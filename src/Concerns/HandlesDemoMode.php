<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Concerns;

use Simtabi\Laranail\Demo\Mode\DemoMode;
use Simtabi\Laranail\Demo\Mode\Exceptions\DemoModeException;

/**
 * Controller convenience trait. Use it (or extend {@see DemoAwareController})
 * to guard actions inline.
 */
trait HandlesDemoMode
{
    protected function demo(): DemoMode
    {
        return app(DemoMode::class);
    }

    protected function demoActive(): bool
    {
        return $this->demo()->isActive();
    }

    protected function demoDenies(string $feature): bool
    {
        return $this->demo()->denies($feature);
    }

    /**
     * Abort with a DemoModeException when the feature is disabled in demo.
     */
    protected function guardDemo(string $feature): void
    {
        $this->demo()->guard($feature);
    }

    /**
     * @throws DemoModeException
     */
    protected function abortIfDemo(string $action = 'this action'): void
    {
        if ($this->demoActive()) {
            throw DemoModeException::actionBlocked($action);
        }
    }
}
