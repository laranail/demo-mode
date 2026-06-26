<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Simtabi\Laranail\Demo\Mode\Providers\DemoModeServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            DemoModeServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));

        // Deterministic defaults for tests.
        config()->set('demo-mode.trigger', 'manual');
        config()->set('demo-mode.state.store', 'config');
        config()->set('demo-mode.events.enabled', true);

        $migration = include __DIR__.'/../database/migrations/create_demo_state_table.php.stub';
        $migration->up();
    }
}
