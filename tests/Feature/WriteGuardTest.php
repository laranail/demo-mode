<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Simtabi\Laranail\Demo\Mode\DemoMode;
use Simtabi\Laranail\Demo\Mode\Exceptions\DemoModeException;
use Simtabi\Laranail\Demo\Mode\Facades\Demo;
use Simtabi\Laranail\Demo\Mode\Tests\Fixtures\Gadget;
use Simtabi\Laranail\Demo\Mode\Tests\Fixtures\Widget;

beforeEach(function (): void {
    config()->set('demo-mode.trigger', 'manual');

    Schema::create('widgets', function ($table): void {
        $table->id();
        $table->string('name')->nullable();
    });
    Schema::create('gadgets', function ($table): void {
        $table->id();
        $table->string('name')->nullable();
        $table->string('email')->nullable();
    });
});

it('blocks mutating HTTP verbs and allows GET via demo.readonly', function (): void {
    Route::middleware('demo.readonly')->get('/r', fn (): string => 'ok');
    Route::middleware('demo.readonly')->post('/r', fn (): string => 'saved');

    Demo::enable();

    $this->get('/r')->assertOk();
    $this->postJson('/r')->assertStatus(423)->assertJson(['demo' => true]);
});

it('allows writes when demo is inactive', function (): void {
    Route::middleware('demo.readonly')->post('/r', fn (): string => 'saved');

    $this->postJson('/r')->assertOk();
});

it('honors the path allowlist', function (): void {
    config()->set('demo-mode.write.allow.paths', ['demo/*']);
    Route::middleware('demo.readonly')->post('/demo/reset', fn (): string => 'reset');

    Demo::enable();

    $this->postJson('/demo/reset')->assertOk();
});

it('blocks a route whose feature is disabled via demo.feature', function (): void {
    config()->set('demo-mode.features', ['export' => false]);
    Route::middleware('demo.feature:export')->get('/export', fn (): string => 'exported');

    Demo::enable();

    $this->getJson('/export')->assertStatus(423);
});

it('blocks trait-protected model writes per $demoAllowed', function (): void {
    Demo::enable();

    // create is in $demoAllowed → permitted
    $widget = Widget::create(['name' => 'a']);
    expect($widget->exists)->toBeTrue();

    // update/delete are NOT allowed → blocked
    expect(fn () => $widget->update(['name' => 'b']))->toThrow(DemoModeException::class);
    expect(fn () => $widget->delete())->toThrow(DemoModeException::class);
});

it('blocks config-protected model writes (per operation)', function (): void {
    config()->set('demo-mode.write.protected_models', [Gadget::class => ['delete' => true]]);
    Demo::enable();

    $gadget = Gadget::create(['name' => 'g']);   // create not blocked
    $gadget->update(['name' => 'g2']);            // update not blocked

    expect($gadget->fresh()->name)->toBe('g2')
        ->and(fn () => $gadget->delete())->toThrow(DemoModeException::class);
});

it('blocks changes to protected attributes', function (): void {
    config()->set('demo-mode.write.protected_attributes', [Gadget::class => ['email']]);
    Demo::enable();

    $gadget = Gadget::create(['name' => 'g', 'email' => 'a@b.com']);

    // Changing a protected attribute is blocked.
    expect(fn () => $gadget->update(['email' => 'evil@b.com']))->toThrow(DemoModeException::class);

    // A fresh instance changing only a non-protected attribute is allowed.
    $fresh = Gadget::query()->findOrFail($gadget->id);
    expect(fn () => $fresh->update(['name' => 'ok']))->not->toThrow(DemoModeException::class)
        ->and($fresh->fresh()->name)->toBe('ok');
});

it('lets writes through inside withoutGuards()', function (): void {
    Demo::enable();
    $widget = Widget::create(['name' => 'a']);

    app(DemoMode::class)->withoutGuards(function () use ($widget): void {
        $widget->update(['name' => 'b']);
    });

    expect($widget->fresh()->name)->toBe('b');
});
