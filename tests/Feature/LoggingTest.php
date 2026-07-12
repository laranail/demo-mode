<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Simtabi\Laranail\Demo\Mode\Contracts\ResetStrategy;
use Simtabi\Laranail\Demo\Mode\Facades\Demo;

beforeEach(function (): void {
    config()->set('demo-mode.trigger', 'manual');
});

it('records a blocked HTTP attempt when logging.blocked is on', function (): void {
    config()->set('demo-mode.logging.blocked', true);
    Route::middleware(['web', 'demo.readonly'])->post('/x', fn (): string => 'ok');

    Demo::enable();
    $this->postJson('/x')->assertStatus(423);

    $row = DB::table('demo_blocked_logs')->first();
    expect(DB::table('demo_blocked_logs')->count())->toBe(1)
        ->and($row->type)->toBe('http')
        ->and($row->target)->toContain('POST');
});

it('does not record when logging.blocked is off', function (): void {
    config()->set('demo-mode.logging.blocked', false);
    Route::middleware(['web', 'demo.readonly'])->post('/x', fn (): string => 'ok');

    Demo::enable();
    $this->postJson('/x')->assertStatus(423);

    expect(DB::table('demo_blocked_logs')->count())->toBe(0);
});

it('records a blocked attempt even with the strict connection guard active', function (): void {
    // Proves the audit insert is exempt (runs under withoutGuards).
    config()->set('demo-mode.logging.blocked', true);
    config()->set('demo-mode.write.strict_connection', true);
    Route::middleware(['web', 'demo.readonly'])->post('/x', fn (): string => 'ok');

    Demo::enable();
    $this->postJson('/x')->assertStatus(423);

    expect(DB::table('demo_blocked_logs')->count())->toBe(1);
});

it('records a completed reset when logging.reset is on', function (): void {
    config()->set('demo-mode.logging.reset', true);
    config()->set('demo-mode.reset.enabled', true);
    config()->set('demo-mode.reset.strategy', 'spy');
    config()->set('demo-mode.reset.allowed_environments', ['testing']);
    config()->set('demo-mode.reset.scope', ['database' => true]);
    config()->set('demo-mode.reset.min_interval', 0);
    app()->instance('demo-mode.reset.strategy.spy', new class implements ResetStrategy
    {
        public function reset(): void {}

        public function name(): string
        {
            return 'spy';
        }
    });

    Demo::enable();
    Demo::reset();

    $row = DB::table('demo_reset_logs')->first();
    expect(DB::table('demo_reset_logs')->count())->toBe(1)
        ->and($row->strategy)->toBe('spy');
});
