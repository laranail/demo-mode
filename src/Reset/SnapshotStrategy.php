<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Reset;

use Illuminate\Contracts\Console\Kernel as Artisan;
use Simtabi\Laranail\Demo\Mode\Contracts\ResetStrategy;
use Simtabi\Laranail\Demo\Mode\Exceptions\DemoModeException;

/**
 * Restores a named baseline via spatie/laravel-db-snapshots (suggested dep).
 */
final readonly class SnapshotStrategy implements ResetStrategy
{
    public function __construct(private Artisan $artisan) {}

    public function reset(): void
    {
        if (! class_exists('Spatie\DbSnapshots\DbSnapshotsServiceProvider')) {
            throw DemoModeException::resetRefused('spatie/laravel-db-snapshots is not installed');
        }

        $this->artisan->call('snapshot:load', [
            'name' => (string) config('demo-mode.reset.snapshot_name', 'demo-baseline'),
            '--force' => true,
        ]);
    }

    public function name(): string
    {
        return 'snapshot';
    }
}
