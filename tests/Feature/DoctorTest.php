<?php

declare(strict_types=1);

use Simtabi\Laranail\Demo\Mode\Doctor\ResetStrategyCheck;
use Simtabi\Laranail\Demo\Mode\Doctor\VerifierPresentCheck;
use Simtabi\Laranail\Package\Tools\Services\Doctor\DoctorStatus;

it('fails the reset-strategy check for an unknown strategy', function (): void {
    config()->set('demo-mode.reset.strategy', 'bogus');

    expect((new ResetStrategyCheck)->run()->status)->toBe(DoctorStatus::Fail);
});

it('passes the reset-strategy check for a known strategy', function (): void {
    config()->set('demo-mode.reset.strategy', 'migrate-fresh-seed');

    expect((new ResetStrategyCheck)->run()->status)->toBe(DoctorStatus::Pass);
});

it('skips the verifier check when not license-driven', function (): void {
    config()->set('demo-mode.trigger', 'manual');

    expect((new VerifierPresentCheck)->run()->status)->toBe(DoctorStatus::Skip);
});

it('runs the doctor command', function (): void {
    config()->set('demo-mode.reset.strategy', 'migrate-fresh-seed');

    $this->artisan('laranail::demo-mode.doctor --json')->run();

    expect(true)->toBeTrue();
});
