<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Reset;

use Illuminate\Contracts\Console\Kernel as Artisan;
use Simtabi\Laranail\Demo\Mode\Contracts\ResetStrategy;

/**
 * Default, dependency-free strategy: migrate:fresh then (optionally) seed.
 */
final readonly class MigrateFreshSeedStrategy implements ResetStrategy
{
    public function __construct(private Artisan $artisan) {}

    public function reset(): void
    {
        $options = ['--force' => true] + (array) config('demo-mode.reset.migrate_fresh_options', []);

        $this->artisan->call('migrate:fresh', $options);

        $seeder = config('demo-mode.reset.seeder');

        if (is_string($seeder) && $seeder !== '') {
            $this->artisan->call('db:seed', ['--class' => $seeder, '--force' => true]);
        }
    }

    public function name(): string
    {
        return 'migrate-fresh-seed';
    }
}
