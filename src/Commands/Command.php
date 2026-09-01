<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Commands;

use Simtabi\Laranail\Console\Tools\Commands\Command as ConsoleCommand;
use Simtabi\Laranail\Console\Tools\Commands\Concerns\SupportsNamespacedNames;
use Simtabi\Laranail\Demo\Mode\DemoMode;

/**
 * Base command for laranail/demo-mode. Enables the `laranail::demo-mode.*`
 * naming shape and exposes the orchestrator.
 */
abstract class Command extends ConsoleCommand
{
    use SupportsNamespacedNames;

    protected function demo(): DemoMode
    {
        return $this->laravel->make(DemoMode::class);
    }
}
