<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Simtabi\Laranail\Demo\Mode\Exceptions\DemoModeException;
use Simtabi\Laranail\Demo\Mode\Facades\Demo;
use Simtabi\Laranail\Demo\Mode\Features\DemoRule;
use Simtabi\Laranail\Demo\Mode\Tests\Fixtures\Gadget;

beforeEach(function (): void {
    config()->set('demo-mode.trigger', 'manual');

    Schema::create('gadgets', function ($table): void {
        $table->id();
        $table->string('name')->nullable();
        $table->string('email')->nullable();
    });
});

it('blocks denied operations and allows the rest', function (): void {
    DemoRule::for(Gadget::class)->deny('delete');
    Demo::enable();

    $gadget = Gadget::create(['name' => 'a']);          // create allowed
    $gadget->update(['name' => 'b']);                    // update allowed

    expect($gadget->fresh()->name)->toBe('b')
        ->and(fn () => $gadget->delete())->toThrow(DemoModeException::class);
});

it('blocks everything except an explicit allow under block()', function (): void {
    DemoRule::for(Gadget::class)->block()->allow('create');
    Demo::enable();

    $gadget = Gadget::create(['name' => 'a']);          // allowed

    expect($gadget->exists)->toBeTrue()
        ->and(fn () => $gadget->update(['name' => 'b']))->toThrow(DemoModeException::class);
});

it('lets a rule allow() override a config block (allow wins)', function (): void {
    config()->set('demo-mode.write.protected_models', [Gadget::class => ['delete' => true]]);
    DemoRule::for(Gadget::class)->allow('delete');
    Demo::enable();

    $gadget = Gadget::create(['name' => 'a']);

    expect(fn () => $gadget->delete())->not->toThrow(DemoModeException::class)
        ->and(Gadget::query()->count())->toBe(0);
});

it('adds a rule deny on top of config (additive)', function (): void {
    config()->set('demo-mode.write.protected_models', [Gadget::class => ['delete' => true]]);
    Demo::rule(Gadget::class)->deny('update'); // via the facade shortcut
    Demo::enable();

    $gadget = Gadget::create(['name' => 'a']);

    expect(fn () => $gadget->update(['name' => 'b']))->toThrow(DemoModeException::class)  // rule
        ->and(fn () => Gadget::query()->whereKey($gadget->id)->first()->delete())->toThrow(DemoModeException::class); // config
});

it('keeps protected attributes blocked even when the op is allowed', function (): void {
    DemoRule::for(Gadget::class)->allow('update')->protectAttributes('email');
    Demo::enable();

    $gadget = Gadget::create(['name' => 'a', 'email' => 'a@b.com']);

    // Non-protected attribute update is allowed (op allowed)...
    $gadget->update(['name' => 'b']);
    expect($gadget->fresh()->name)->toBe('b');

    // ...but a protected-attribute change still throws (allow un-blocks the op, not the attribute).
    $fresh = Gadget::query()->findOrFail($gadget->id);
    expect(fn () => $fresh->update(['email' => 'evil@b.com']))->toThrow(DemoModeException::class);
});
