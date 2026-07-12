<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode;

use Closure;
use Illuminate\Contracts\Container\Container;
use Simtabi\Laranail\Demo\Mode\Contracts\LicenseGateway;
use Simtabi\Laranail\Demo\Mode\Contracts\StateStore;
use Simtabi\Laranail\Demo\Mode\Events\DemoModeDisabled;
use Simtabi\Laranail\Demo\Mode\Events\DemoModeEnabled;
use Simtabi\Laranail\Demo\Mode\Exceptions\DemoModeException;
use Simtabi\Laranail\Demo\Mode\Features\DemoRule;
use Simtabi\Laranail\Demo\Mode\Features\DemoRuleRegistry;
use Simtabi\Laranail\Demo\Mode\Features\FeatureRegistry;
use Simtabi\Laranail\Demo\Mode\Reset\ResetManager;
use Simtabi\Laranail\Demo\Mode\State\BypassResolver;
use Simtabi\Laranail\Demo\Mode\State\DemoState;

/**
 * Central entry point for demo mode — the target of the {@see Demo} facade.
 *
 * The base decision is memoised per request (it is consulted by every middleware
 * and every guarded model write); bypass is evaluated fresh on each call so it
 * stays correct after authentication resolves.
 */
final class DemoMode
{
    private ?bool $baseMemo = null;

    private int $suspended = 0;

    public function __construct(
        private readonly Container $app,
        private readonly DemoState $state,
        private readonly FeatureRegistry $features,
        private readonly BypassResolver $bypass,
        private readonly StateStore $store,
        private readonly LicenseGateway $license,
    ) {}

    /*
    |--------------------------------------------------------------------------
    | State
    |--------------------------------------------------------------------------
    */

    public function isActive(): bool
    {
        if ($this->suspended > 0 || $this->bypass->bypasses()) {
            return false;
        }

        return $this->baseMemo ??= $this->state->resolve();
    }

    public function isReadOnly(): bool
    {
        return $this->isActive() && config('demo-mode.write.strategy', 'block') !== 'allow';
    }

    public function reason(): string
    {
        if (! $this->isActive()) {
            return $this->bypass->bypasses() ? 'bypass' : 'inactive';
        }

        return $this->state->reason();
    }

    public function enable(): self
    {
        // Suspend guards so the state write is exempt from the strict connection guard.
        $this->withoutGuards(fn () => $this->store->set(true));
        $this->flushState();
        $this->dispatch(new DemoModeEnabled('manual'));

        return $this;
    }

    public function disable(): self
    {
        $this->withoutGuards(fn () => $this->store->set(false));
        $this->flushState();
        $this->dispatch(new DemoModeDisabled('manual'));

        return $this;
    }

    /**
     * Clear the persisted override (revert to config/license-driven state).
     */
    public function clearOverride(): self
    {
        $this->withoutGuards(fn () => $this->store->forget());
        $this->flushState();

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | Bypass / guard suspension
    |--------------------------------------------------------------------------
    */

    public function bypasses(): bool
    {
        return $this->bypass->bypasses();
    }

    public function guardsSuspended(): bool
    {
        return $this->suspended > 0;
    }

    /**
     * Run a callback with demo guards suspended (used internally during
     * reset/seed and exposed for admin operations).
     *
     * @template T
     *
     * @param  Closure(): T  $callback
     * @return T
     */
    public function withoutGuards(Closure $callback): mixed
    {
        $this->suspended++;

        try {
            return $callback();
        } finally {
            $this->suspended--;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Feature gating
    |--------------------------------------------------------------------------
    */

    public function allows(string $feature): bool
    {
        if (! $this->isActive()) {
            return true;
        }

        return $this->features->allows($feature);
    }

    public function denies(string $feature): bool
    {
        return ! $this->allows($feature);
    }

    public function protect(string ...$features): self
    {
        $this->features->protect(...$features);

        return $this;
    }

    /**
     * Begin a fluent per-model write rule.
     *
     * @param  class-string  $class
     */
    public function rule(string $class): DemoRule
    {
        return $this->app->make(DemoRuleRegistry::class)->for($class);
    }

    public function permit(string ...$features): self
    {
        $this->features->permit(...$features);

        return $this;
    }

    /**
     * Abort with a DemoModeException when the feature is denied in demo.
     */
    public function guard(string $feature): void
    {
        if ($this->denies($feature)) {
            throw DemoModeException::actionBlocked($feature);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Reset / sandbox / license (resolved lazily)
    |--------------------------------------------------------------------------
    */

    public function reset(?string $strategy = null): void
    {
        $this->app->make(ResetManager::class)->reset($strategy);
    }

    public function snapshot(?string $name = null): void
    {
        $this->app->make(ResetManager::class)->snapshot($name);
    }

    public function license(): LicenseGateway
    {
        return $this->license;
    }

    /*
    |--------------------------------------------------------------------------
    | Runtime reconfiguration
    |--------------------------------------------------------------------------
    */

    /**
     * Apply dotted config overrides and re-resolve state.
     *
     * @param  array<string, mixed>  $overrides
     */
    public function configure(array $overrides): self
    {
        foreach ($overrides as $key => $value) {
            config()->set('demo-mode.'.$key, $value);
        }

        return $this->flushState();
    }

    public function flushState(): self
    {
        $this->baseMemo = null;

        return $this;
    }

    private function dispatch(object $event): void
    {
        if ((bool) config('demo-mode.events.enabled', true)) {
            event($event);
        }
    }
}
