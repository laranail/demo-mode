<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Simtabi\Laranail\Demo\Mode\Contracts\ResetStrategy;
use Simtabi\Laranail\Demo\Mode\DemoMode;
use Simtabi\Laranail\Demo\Mode\Events\DemoReset;
use Simtabi\Laranail\Demo\Mode\Exceptions\DemoModeException;
use Simtabi\Laranail\Demo\Mode\Facades\Demo;

/**
 * Spy strategy so the reset pipeline is exercised without really running
 * migrate:fresh against the Testbench database.
 */
function spyStrategy(): object
{
    $spy = new class implements ResetStrategy
    {
        public int $calls = 0;

        public function reset(): void
        {
            $this->calls++;
        }

        public function name(): string
        {
            return 'spy';
        }
    };

    app()->instance('demo-mode.reset.strategy.spy', $spy);

    return $spy;
}

beforeEach(function (): void {
    config()->set('demo-mode.trigger', 'manual');
    config()->set('demo-mode.reset.enabled', true);
    config()->set('demo-mode.reset.strategy', 'spy');
    config()->set('demo-mode.reset.allowed_environments', ['testing']);
    config()->set('demo-mode.reset.scope', ['database' => true, 'cache' => false, 'sessions' => false]);
    config()->set('demo-mode.reset.min_interval', 0);
});

it('runs the strategy under suspended guards and fires DemoReset', function (): void {
    Event::fake([DemoReset::class]);
    $spy = spyStrategy();

    Demo::reset();

    expect($spy->calls)->toBe(1);
    Event::assertDispatched(DemoReset::class, fn (DemoReset $e): bool => $e->strategy === 'spy' && in_array('database', $e->scope, true));
});

it('refuses to reset when disabled', function (): void {
    config()->set('demo-mode.reset.enabled', false);
    spyStrategy();

    expect(fn () => Demo::reset())->toThrow(DemoModeException::class);
});

it('refuses to reset in a disallowed environment', function (): void {
    config()->set('demo-mode.reset.allowed_environments', ['production']);
    config()->set('demo-mode.reset.allow_production', false);
    spyStrategy();

    expect(fn () => Demo::reset())->toThrow(DemoModeException::class);
});

it('re-establishes the demo override after a reset', function (): void {
    config()->set('demo-mode.state.store', 'cache');
    app(DemoMode::class)->flushState();
    spyStrategy();

    Demo::enable();
    expect(Demo::isActive())->toBeTrue();

    Demo::reset();

    expect(Demo::isActive())->toBeTrue(); // not silently disabled
});

it('exposes status + toggles via the CLI', function (): void {
    $this->artisan('laranail::demo-mode.enable')->assertSuccessful();
    $this->artisan('demo:status')->assertSuccessful();
    $this->artisan('laranail::demo-mode.disable')->assertSuccessful();
});
