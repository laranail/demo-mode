<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Simtabi\Laranail\Demo\Mode\Exceptions\DemoModeException;
use Simtabi\Laranail\Demo\Mode\Facades\Demo;
use Simtabi\Laranail\Demo\Mode\Tests\Fixtures\Gadget;

beforeEach(function (): void {
    config()->set('demo-mode.trigger', 'manual');
    config()->set('demo-mode.write.strict_connection', true);

    Schema::create('gadgets', function ($table): void {
        $table->id();
        $table->string('name')->nullable();
    });
});

it('blocks a mass update()/delete() that Eloquent observers miss', function (): void {
    Gadget::insert([['name' => 'a'], ['name' => 'b']]);

    Demo::enable();

    expect(fn () => Gadget::query()->where('name', 'a')->update(['name' => 'z']))
        ->toThrow(DemoModeException::class)
        ->and(fn () => Gadget::query()->where('name', 'b')->delete())
        ->toThrow(DemoModeException::class);

    // Reads still work.
    expect(Gadget::query()->count())->toBe(2);
});

it('allows writes once demo is disabled', function (): void {
    Demo::enable();
    Demo::disable();

    Gadget::insert(['name' => 'ok']);

    expect(Gadget::query()->count())->toBe(1);
});
