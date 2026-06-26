<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Simtabi\Laranail\Demo\Mode\DemoMode;
use Simtabi\Laranail\Demo\Mode\Facades\Demo;

it('is inactive by default', function (): void {
    expect(Demo::isActive())->toBeFalse();
});

it('toggles via enable()/disable() under the manual trigger', function (): void {
    config()->set('demo-mode.trigger', 'manual');

    Demo::enable();
    expect(Demo::isActive())->toBeTrue()
        ->and(Demo::isReadOnly())->toBeTrue();

    Demo::disable();
    expect(Demo::isActive())->toBeFalse();
});

it('honors the config enabled flag under the manual trigger', function (): void {
    config()->set('demo-mode.trigger', 'manual');
    config()->set('demo-mode.enabled', true);
    app(DemoMode::class)->flushState();

    expect(Demo::isActive())->toBeTrue()
        ->and(Demo::reason())->toBe('config');
});

it('is never active for a bypassed IP', function (): void {
    config()->set('demo-mode.trigger', 'manual');
    config()->set('demo-mode.enabled', true);
    config()->set('demo-mode.bypass.ips', ['10.0.0.1']);
    app(DemoMode::class)->flushState();

    // Request from a non-allowlisted IP → demo active.
    $this->app->instance('request', Request::create('/', 'GET', server: ['REMOTE_ADDR' => '203.0.113.5']));
    expect(Demo::bypasses())->toBeFalse()
        ->and(Demo::isActive())->toBeTrue();

    // Request from the allowlisted IP → bypassed, never in demo.
    $this->app->instance('request', Request::create('/', 'GET', server: ['REMOTE_ADDR' => '10.0.0.1']));
    expect(Demo::bypasses())->toBeTrue()
        ->and(Demo::isActive())->toBeFalse();
});

it('suspends guards within withoutGuards()', function (): void {
    config()->set('demo-mode.trigger', 'manual');
    Demo::enable();

    $seen = Demo::withoutGuards(fn (): bool => Demo::isActive());

    expect($seen)->toBeFalse()
        ->and(Demo::isActive())->toBeTrue();
});
