<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Simtabi\Laranail\Demo\Mode\Contracts\ResetStrategy;
use Simtabi\Laranail\Demo\Mode\Events\DemoReset;
use Simtabi\Laranail\Demo\Mode\Facades\Demo;
use Simtabi\Laranail\Demo\Mode\Tests\Fixtures\Gadget;

beforeEach(function (): void {
    config()->set('demo-mode.trigger', 'manual');
});

it('injects the banner into HTML responses while demo is active', function (): void {
    Route::middleware(['web', 'demo.banner'])->get('/page', fn (): string => '<html><body>Hi</body></html>');

    Demo::enable();

    $response = $this->get('/page');
    $response->assertOk();
    expect($response->getContent())->toContain('demo-mode-banner');
});

it('does not inject the banner when demo is inactive', function (): void {
    Route::middleware(['web', 'demo.banner'])->get('/page', fn (): string => '<html><body>Hi</body></html>');

    expect($this->get('/page')->getContent())->not->toContain('demo-mode-banner');
});

it('rolls back writes under the ephemeral strategy', function (): void {
    config()->set('demo-mode.write.strategy', 'ephemeral');
    Schema::create('gadgets', fn ($t) => tap($t->id(), fn () => $t->string('name')->nullable()));

    Route::middleware(['web', 'demo.ephemeral'])->post('/make', function (): string {
        Gadget::create(['name' => 'temp']);

        // The write is visible within the request...
        return (string) Gadget::query()->count();
    });

    Demo::enable();

    expect($this->post('/make')->getContent())->toBe('1');

    // ...but rolled back afterwards.
    expect(Gadget::query()->count())->toBe(0);
});

it('runs an on-demand reset via the route', function (): void {
    config()->set('demo-mode.reset.enabled', true);
    config()->set('demo-mode.reset.on_demand.enabled', true);
    config()->set('demo-mode.reset.strategy', 'spy');
    config()->set('demo-mode.reset.allowed_environments', ['testing']);
    config()->set('demo-mode.reset.scope', ['database' => true]);
    app()->instance('demo-mode.reset.strategy.spy', new class implements ResetStrategy
    {
        public function reset(): void {}

        public function name(): string
        {
            return 'spy';
        }
    });

    Event::fake([DemoReset::class]);
    Demo::enable();

    $this->postJson('/demo/reset')->assertOk();
    Event::assertDispatched(DemoReset::class);
});
