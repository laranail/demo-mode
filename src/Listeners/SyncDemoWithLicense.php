<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Listeners;

use Simtabi\Laranail\Demo\Mode\DemoMode;

/**
 * Keeps the runtime override in step with license lifecycle events from
 * laranail/license-verifier (registered only when that package is present and
 * demo-mode.license.sync_events is on).
 */
final readonly class SyncDemoWithLicense
{
    public function __construct(private DemoMode $demo) {}

    public function activated(): void
    {
        if ($this->enabled()) {
            $this->demo->disable();
        }
    }

    public function deactivated(): void
    {
        if ($this->enabled()) {
            $this->demo->enable();
        }
    }

    private function enabled(): bool
    {
        return (bool) config('demo-mode.license.sync_events', true);
    }
}
