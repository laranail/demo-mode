<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Providers;

use Composer\InstalledVersions;
use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Eloquent\Model;
use Override;
use Simtabi\Laranail\Demo\Mode\Blade\DemoDirectives;
use Simtabi\Laranail\Demo\Mode\Commands\DisableCommand;
use Simtabi\Laranail\Demo\Mode\Commands\DoctorCommand;
use Simtabi\Laranail\Demo\Mode\Commands\EnableCommand;
use Simtabi\Laranail\Demo\Mode\Commands\ResetCommand;
use Simtabi\Laranail\Demo\Mode\Commands\SnapshotCommand;
use Simtabi\Laranail\Demo\Mode\Commands\StatusCommand;
use Simtabi\Laranail\Demo\Mode\Contracts\LicenseGateway;
use Simtabi\Laranail\Demo\Mode\Contracts\StateStore;
use Simtabi\Laranail\Demo\Mode\DemoMode;
use Simtabi\Laranail\Demo\Mode\Doctor\Checks;
use Simtabi\Laranail\Demo\Mode\Events\DemoActionBlocked;
use Simtabi\Laranail\Demo\Mode\Events\DemoReset;
use Simtabi\Laranail\Demo\Mode\Features\DemoRuleRegistry;
use Simtabi\Laranail\Demo\Mode\Guards\ConsoleGuard;
use Simtabi\Laranail\Demo\Mode\Guards\EloquentWriteGuard;
use Simtabi\Laranail\Demo\Mode\Guards\WriteBlockingConnection;
use Simtabi\Laranail\Demo\Mode\Http\Middleware\BlockDemoFeature;
use Simtabi\Laranail\Demo\Mode\Http\Middleware\BypassDemo;
use Simtabi\Laranail\Demo\Mode\Http\Middleware\DemoAutoLogin;
use Simtabi\Laranail\Demo\Mode\Http\Middleware\DemoSandbox;
use Simtabi\Laranail\Demo\Mode\Http\Middleware\EnsureDemoMode;
use Simtabi\Laranail\Demo\Mode\Http\Middleware\EnsureDemoReadOnly;
use Simtabi\Laranail\Demo\Mode\Http\Middleware\EphemeralWrites;
use Simtabi\Laranail\Demo\Mode\Http\Middleware\GuardDemoAction;
use Simtabi\Laranail\Demo\Mode\Http\Middleware\GuardSideEffects;
use Simtabi\Laranail\Demo\Mode\Http\Middleware\InjectDemoBanner;
use Simtabi\Laranail\Demo\Mode\License\NullLicenseGateway;
use Simtabi\Laranail\Demo\Mode\License\VerifierLicenseGateway;
use Simtabi\Laranail\Demo\Mode\Listeners\LogBlockedAttempt;
use Simtabi\Laranail\Demo\Mode\Listeners\LogDemoReset;
use Simtabi\Laranail\Demo\Mode\Listeners\SyncDemoWithLicense;
use Simtabi\Laranail\Demo\Mode\Sandbox\SandboxContext;
use Simtabi\Laranail\Demo\Mode\State\CacheStateStore;
use Simtabi\Laranail\Demo\Mode\State\ConfigStateStore;
use Simtabi\Laranail\Demo\Mode\State\DatabaseStateStore;
use Simtabi\Laranail\Package\Tools\Package;
use Simtabi\Laranail\Package\Tools\Providers\PackageServiceProvider;
use Simtabi\Laranail\Package\Tools\Support\Definitions\AboutSectionDefinition;

final class DemoModeServiceProvider extends PackageServiceProvider
{
    #[Override]
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laranail/demo-mode')
            ->hasConfigFile('demo-mode')
            ->withoutConfigNamespacing()
            ->hasTranslations('demo-mode')
            ->hasViews()
            ->hasRoute('web')
            ->hasMigrations([
                'create_demo_state_table',
                'create_demo_blocked_logs_table',
                'create_demo_reset_logs_table',
            ])
            ->hasCommands(
                StatusCommand::class,
                EnableCommand::class,
                DisableCommand::class,
                ResetCommand::class,
                SnapshotCommand::class,
                DoctorCommand::class,
            )
            ->hasDoctorChecks(Checks::all())
            ->hasAboutSection(
                AboutSectionDefinition::make('Demo Mode')
                    ->field('Version', fn (): string => (string) InstalledVersions::getPrettyVersion('laranail/demo-mode'))
                    ->field('Enabled', fn (): bool => (bool) config('demo-mode.enabled', false))
            )
            ->registerRouteMiddleware('demo', EnsureDemoReadOnly::class)
            ->registerRouteMiddleware('demo.readonly', EnsureDemoReadOnly::class)
            ->registerRouteMiddleware('demo.only', EnsureDemoMode::class)
            ->registerRouteMiddleware('demo.feature', BlockDemoFeature::class)
            ->registerRouteMiddleware('demo.guard', GuardDemoAction::class)
            ->registerRouteMiddleware('demo.banner', InjectDemoBanner::class)
            ->registerRouteMiddleware('demo.ephemeral', EphemeralWrites::class)
            ->registerRouteMiddleware('demo.safe', GuardSideEffects::class)
            ->registerRouteMiddleware('demo.autologin', DemoAutoLogin::class)
            ->registerRouteMiddleware('demo.sandbox', DemoSandbox::class)
            ->registerRouteMiddleware('demo.bypass', BypassDemo::class);
    }

    #[Override]
    public function packageRegistered(): void
    {
        $this->registerStateStore();
        $this->registerLicenseGateway();

        $this->app->singleton(SandboxContext::class);
        $this->app->singleton(DemoRuleRegistry::class);
        $this->app->singleton(DemoMode::class);
    }

    #[Override]
    public function packageBooted(): void
    {
        DemoDirectives::register();
        $this->bootModelGuards();
        WriteBlockingConnection::install($this->app);
        ConsoleGuard::install($this->app);
        $this->registerLogListeners();
        $this->registerLicenseSync();
    }

    /**
     * Audit listeners for blocked attempts and completed resets (each self-checks
     * its config toggle).
     */
    private function registerLogListeners(): void
    {
        $events = $this->app['events'];

        $events->listen(DemoActionBlocked::class, [LogBlockedAttempt::class, 'handle']);
        $events->listen(DemoReset::class, [LogDemoReset::class, 'handle']);
    }

    /**
     * Hook demo state to license lifecycle events when the verifier is installed.
     */
    private function registerLicenseSync(): void
    {
        $events = $this->app['events'];
        $listener = SyncDemoWithLicense::class;

        $map = [
            'Simtabi\Laranail\Licence\Verifier\Events\LicenseActivated' => 'activated',
            'Simtabi\Laranail\Licence\Verifier\Events\LicenseDeactivated' => 'deactivated',
        ];

        foreach ($map as $event => $method) {
            if (class_exists($event)) {
                $events->listen($event, [$listener, $method]);
            }
        }
    }

    /**
     * A single set of wildcard Eloquent listeners enforces both trait- and
     * config-based protection dynamically (config is read live by the guard),
     * so protection works regardless of boot-time config ordering.
     */
    private function bootModelGuards(): void
    {
        $events = $this->app['events'];

        $operations = [
            'creating' => 'create',
            'updating' => 'update',
            'deleting' => 'delete',
            'restoring' => 'restore',
        ];

        foreach ($operations as $event => $operation) {
            $events->listen("eloquent.{$event}: *", function (string $name, array $payload) use ($operation): void {
                $model = $payload[0] ?? null;

                if ($model instanceof Model) {
                    $this->app->make(EloquentWriteGuard::class)->guard($model, $operation);
                }
            });
        }
    }

    private function registerStateStore(): void
    {
        $this->app->singleton(StateStore::class, static fn (Container $app): StateStore => match ((string) config('demo-mode.state.store', 'cache')) {
            'database' => new DatabaseStateStore($app->make(ConnectionResolverInterface::class)),
            'config' => new ConfigStateStore,
            default => new CacheStateStore($app->make(CacheFactory::class)),
        });
    }

    /**
     * Bind the license adapter to the verifier's LicenseManager when it is
     * installed; otherwise fall back to the null gateway (soft dependency).
     */
    private function registerLicenseGateway(): void
    {
        $this->app->singleton(LicenseGateway::class, static function (Container $app): LicenseGateway {
            $manager = 'Simtabi\Laranail\Licence\Verifier\LicenseManager';

            return class_exists($manager)
                ? new VerifierLicenseGateway($app->make($manager))
                : new NullLicenseGateway;
        });
    }
}
