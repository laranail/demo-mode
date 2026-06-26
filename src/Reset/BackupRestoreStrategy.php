<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Reset;

use Simtabi\Laranail\Demo\Mode\Contracts\ResetStrategy;
use Simtabi\Laranail\Demo\Mode\Exceptions\DemoModeException;

/**
 * Restores from a spatie/laravel-backup archive. Because restore policy is
 * app-specific, this delegates to a `demo-mode.reset.callback` (which should run
 * the project's restore routine); it exists so `backup-restore` is a first-class
 * strategy name. Throws if the dependency is absent and no callback is set.
 */
final class BackupRestoreStrategy implements ResetStrategy
{
    public function reset(): void
    {
        if (! class_exists('Spatie\Backup\BackupServiceProvider')) {
            throw DemoModeException::resetRefused('spatie/laravel-backup is not installed');
        }

        $callback = config('demo-mode.reset.callback');

        if (! is_callable($callback)) {
            throw DemoModeException::resetRefused('set reset.callback to your spatie/laravel-backup restore routine');
        }

        $callback();
    }

    public function name(): string
    {
        return 'backup-restore';
    }
}
