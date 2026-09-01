<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Guards;

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Contracts\Container\Container;
use Simtabi\Laranail\Demo\Mode\DemoMode;
use Simtabi\Laranail\Demo\Mode\Exceptions\DemoModeException;

/**
 * Blocks configured destructive console commands while demo mode is active.
 * Always exempts the package's own commands and anything inside withoutGuards().
 */
final class ConsoleGuard
{
    public static function install(Container $app): void
    {
        $app['events']->listen(CommandStarting::class, static function (CommandStarting $event) use ($app): void {
            if (! (bool) config('demo-mode.console.guard', false)) {
                return;
            }

            $command = $event->command;

            if (str_starts_with($command, 'laranail::demo-mode.') || str_starts_with($command, 'demo:')) {
                return;
            }

            $demo = $app->make(DemoMode::class);

            if (! $demo->isActive() || $demo->guardsSuspended()) {
                return;
            }

            if (in_array($command, (array) config('demo-mode.console.protected', []), true)) {
                throw DemoModeException::actionBlocked('command:'.$command);
            }
        });
    }
}
