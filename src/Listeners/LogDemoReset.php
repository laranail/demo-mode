<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Listeners;

use Illuminate\Support\Facades\DB;
use Simtabi\Laranail\Demo\Mode\DemoMode;
use Simtabi\Laranail\Demo\Mode\Events\DemoReset;
use Throwable;

/**
 * Records completed resets to the demo_reset_logs table when
 * demo-mode.logging.reset is on. Wrapped in withoutGuards() (demo state is
 * re-established by the time DemoReset fires) and resilient.
 */
final readonly class LogDemoReset
{
    public function __construct(private DemoMode $demo) {}

    public function handle(DemoReset $event): void
    {
        if (! (bool) config('demo-mode.logging.reset', false)) {
            return;
        }

        $row = [
            'strategy' => $event->strategy,
            'scope' => json_encode($event->scope),
            'created_at' => now(),
        ];

        try {
            $this->demo->withoutGuards(static fn () => DB::table('demo_reset_logs')->insert($row));
        } catch (Throwable) {
            // Best effort.
        }
    }
}
