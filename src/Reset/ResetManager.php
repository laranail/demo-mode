<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Reset;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Console\Kernel as Artisan;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Connection;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Support\Facades\File;
use Simtabi\Laranail\Demo\Mode\Contracts\ResetStrategy;
use Simtabi\Laranail\Demo\Mode\Contracts\StateStore;
use Simtabi\Laranail\Demo\Mode\DemoMode;
use Simtabi\Laranail\Demo\Mode\Events\DemoReset;
use Simtabi\Laranail\Demo\Mode\Events\DemoResetting;
use Simtabi\Laranail\Demo\Mode\Events\DemoSnapshotCreated;
use Simtabi\Laranail\Demo\Mode\Exceptions\DemoModeException;
use Throwable;

/**
 * Orchestrates a demo reset: safety gate → lock → (database via strategy, files,
 * cache, sessions, queue, logs) under suspended guards → re-establish demo state
 * → events. Strategies are resolved from container bindings
 * (`demo-mode.reset.strategy.{name}`, overridable in tests) or the built-ins.
 */
final readonly class ResetManager
{
    public function __construct(
        private Application $app,
        private DemoMode $demo,
        private StateStore $store,
        private ResetLock $lock,
        private FileResetter $files,
        private ConnectionResolverInterface $db,
        private CacheRepository $cache,
    ) {}

    public function reset(?string $strategyName = null): void
    {
        $this->assertSafe();

        $strategy = $this->strategy($strategyName ?? (string) config('demo-mode.reset.strategy', 'migrate-fresh-seed'));
        $override = $this->store->get();

        $ran = $this->lock->run(function () use ($strategy, $override): void {
            $this->dispatch(new DemoResetting($strategy->name()));

            $scopes = $this->demo->withoutGuards(fn (): array => $this->runScopes($strategy));

            $this->reestablishState($override);

            $this->dispatch(new DemoReset($strategy->name(), $scopes));
        });

        if (! $ran) {
            throw DemoModeException::resetRefused('a reset is already running or the minimum interval has not elapsed');
        }
    }

    public function snapshot(?string $name = null): void
    {
        if (! class_exists('Spatie\DbSnapshots\DbSnapshotsServiceProvider')) {
            throw DemoModeException::resetRefused('snapshots require spatie/laravel-db-snapshots');
        }

        $snapshotName = $name ?? (string) config('demo-mode.reset.snapshot_name', 'demo-baseline');

        $this->app->make(Artisan::class)->call('snapshot:create', [
            'name' => $snapshotName,
        ]);

        $this->dispatch(new DemoSnapshotCreated($snapshotName));
    }

    private function assertSafe(): void
    {
        if (! (bool) config('demo-mode.reset.enabled', false)) {
            throw DemoModeException::resetRefused('reset is disabled (set demo-mode.reset.enabled)');
        }

        $env = $this->app->environment();
        $allowed = (array) config('demo-mode.reset.allowed_environments', []);

        if (in_array($env, $allowed, true)) {
            return;
        }

        if ($env === 'production' && (bool) config('demo-mode.reset.allow_production', false)) {
            return;
        }

        throw DemoModeException::resetRefused("environment [{$env}] is not allowed to reset");
    }

    private function strategy(string $name): ResetStrategy
    {
        $binding = 'demo-mode.reset.strategy.'.$name;

        if ($this->app->bound($binding)) {
            return $this->app->make($binding);
        }

        return match ($name) {
            'migrate-fresh-seed' => $this->app->make(MigrateFreshSeedStrategy::class),
            'snapshot' => $this->app->make(SnapshotStrategy::class),
            'sql-dump' => $this->app->make(SqlDumpStrategy::class),
            'backup-restore' => $this->app->make(BackupRestoreStrategy::class),
            'callback' => $this->app->make(CallbackStrategy::class),
            default => throw DemoModeException::resetRefused("unknown reset strategy [{$name}]"),
        };
    }

    /**
     * @return list<string>
     */
    private function runScopes(ResetStrategy $strategy): array
    {
        $scope = (array) config('demo-mode.reset.scope', []);
        $done = [];

        if ($scope['database'] ?? true) {
            $strategy->reset();
            $done[] = 'database';
        }

        if ($scope['files'] ?? false) {
            $this->files->reset();
            $done[] = 'files';
        }

        if ($scope['cache'] ?? true) {
            $this->cache->clear();
            $done[] = 'cache';
        }

        if (($scope['sessions'] ?? true) && $this->truncate('session', 'sessions')) {
            $done[] = 'sessions';
        }

        if (($scope['queue'] ?? false) && $this->truncate('queue', 'jobs')) {
            $done[] = 'queue';
        }

        if ($scope['logs'] ?? false) {
            $this->clearLogs();
            $done[] = 'logs';
        }

        return $done;
    }

    private function truncate(string $driverConfig, string $table): bool
    {
        if (config($driverConfig.'.driver') !== 'database') {
            return false;
        }

        try {
            $connection = $this->db->connection();

            if ($connection instanceof Connection && $connection->getSchemaBuilder()->hasTable($table)) {
                $connection->table($table)->truncate();

                return true;
            }
        } catch (Throwable) {
            // Best effort.
        }

        return false;
    }

    private function clearLogs(): void
    {
        foreach (File::glob(storage_path('logs/*.log')) as $log) {
            File::put($log, '');
        }
    }

    /**
     * migrate:fresh drops the package's own demo_state table — re-apply the
     * pre-reset override so demo mode does not silently switch off.
     */
    private function reestablishState(?bool $override): void
    {
        if ($override !== null) {
            $this->store->set($override);
        }

        $this->demo->flushState();
    }

    private function dispatch(object $event): void
    {
        if ((bool) config('demo-mode.events.enabled', true)) {
            event($event);
        }
    }
}
