<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\State;

use Illuminate\Contracts\Foundation\Application;
use Simtabi\Laranail\Demo\Mode\Contracts\StateStore;
use Simtabi\Laranail\Demo\Mode\Policies\LicenseDemoPolicy;

/**
 * Resolves the BASE demo decision (ignoring bypass, which is evaluated fresh per
 * call by {@see DemoMode}).
 *
 * Resolution by trigger:
 *   manual      — override ?? config(enabled)
 *   license     — license decision ?? config(enabled)            (no manual override)
 *   both        — override ?? license decision ?? config(enabled) (default)
 *   environment — app environment is in config(environments)
 *   schedule    — current time falls in a configured window
 */
final readonly class DemoState
{
    public function __construct(
        private Application $app,
        private StateStore $store,
        private LicenseDemoPolicy $license,
    ) {}

    public function resolve(): bool
    {
        return match ((string) config('demo-mode.trigger', 'both')) {
            'manual' => $this->store->get() ?? $this->configEnabled(),
            'license' => $this->license->decide() ?? $this->configEnabled(),
            'environment' => $this->inEnvironment(),
            'schedule' => $this->inWindow(),
            default => $this->store->get() ?? $this->license->decide() ?? $this->configEnabled(),
        };
    }

    public function reason(): string
    {
        $trigger = (string) config('demo-mode.trigger', 'both');

        if (in_array($trigger, ['manual', 'license', 'both'], true) && $this->store->get() !== null && $trigger !== 'license') {
            return 'override';
        }

        return match ($trigger) {
            'manual' => 'config',
            'environment' => 'environment',
            'schedule' => 'schedule',
            default => $this->license->decide() !== null ? 'license' : 'config',
        };
    }

    private function configEnabled(): bool
    {
        return (bool) config('demo-mode.enabled', false);
    }

    private function inEnvironment(): bool
    {
        return in_array($this->app->environment(), (array) config('demo-mode.environments', []), true);
    }

    private function inWindow(): bool
    {
        $now = now()->format('H:i');

        foreach ((array) config('demo-mode.windows', []) as $window) {
            $from = (string) ($window['from'] ?? '00:00');
            $to = (string) ($window['to'] ?? '23:59');

            if ($now >= $from && $now <= $to) {
                return true;
            }
        }

        return false;
    }
}
