<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Listeners;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Simtabi\Laranail\Demo\Mode\DemoMode;
use Simtabi\Laranail\Demo\Mode\Events\DemoActionBlocked;
use Throwable;

/**
 * Records blocked attempts to the demo_blocked_logs table (and optionally a log
 * channel) when demo-mode.logging.blocked is on.
 *
 * Best-effort: the insert runs under withoutGuards() (so the write guards don't
 * block the audit insert) and is wrapped in try/catch (so a missing table never
 * breaks the request). Under the `ephemeral` write strategy the row shares the
 * request transaction and is rolled back with it.
 */
final readonly class LogBlockedAttempt
{
    public function __construct(private DemoMode $demo) {}

    public function handle(DemoActionBlocked $event): void
    {
        if (! (bool) config('demo-mode.logging.blocked', false)) {
            return;
        }

        $row = [
            'type' => $event->type,
            'target' => $event->target,
            'actor' => $this->actor(),
            'ip' => $this->ip(),
            'created_at' => now(),
        ];

        $channel = config('demo-mode.logging.channel');

        if (is_string($channel) && $channel !== '') {
            Log::channel($channel)->warning('demo-mode blocked attempt', $row);
        }

        try {
            $this->demo->withoutGuards(static fn () => DB::table('demo_blocked_logs')->insert($row));
        } catch (Throwable) {
            // Table absent or write failed — auditing is best-effort.
        }
    }

    private function actor(): ?string
    {
        try {
            $id = Auth::id();

            return $id === null ? null : (string) $id;
        } catch (Throwable) {
            return null;
        }
    }

    private function ip(): ?string
    {
        try {
            return app()->bound('request') ? app('request')->ip() : null;
        } catch (Throwable) {
            return null;
        }
    }
}
