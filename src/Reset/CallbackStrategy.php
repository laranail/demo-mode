<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Reset;

use Simtabi\Laranail\Demo\Mode\Contracts\ResetStrategy;
use Simtabi\Laranail\Demo\Mode\Exceptions\DemoModeException;

/**
 * Runs the author-supplied `demo-mode.reset.callback` callable.
 */
final class CallbackStrategy implements ResetStrategy
{
    public function reset(): void
    {
        $callback = config('demo-mode.reset.callback');

        if (! is_callable($callback)) {
            throw DemoModeException::resetRefused('no reset.callback is configured');
        }

        $callback();
    }

    public function name(): string
    {
        return 'callback';
    }
}
