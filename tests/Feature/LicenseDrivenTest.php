<?php

declare(strict_types=1);

use Simtabi\Laranail\Demo\Mode\Contracts\LicenseGateway;
use Simtabi\Laranail\Demo\Mode\DemoMode;
use Simtabi\Laranail\Demo\Mode\Facades\Demo;

/**
 * In-memory license gateway so the license-driven path is testable without the
 * verifier package installed (proving the soft-dependency design).
 */
function fakeLicense(bool $present, bool $usable, string $status = 'valid', array $entitlements = []): void
{
    app()->instance(LicenseGateway::class, new readonly class($present, $usable, $status, $entitlements) implements LicenseGateway
    {
        public function __construct(
            private bool $present,
            private bool $usable,
            private string $status,
            private array $entitlements,
        ) {}

        public function isPresent(): bool
        {
            return $this->present;
        }

        public function isUsable(): bool
        {
            return $this->usable;
        }

        public function status(): string
        {
            return $this->status;
        }

        public function entitledTo(string $feature): bool
        {
            return in_array($feature, $this->entitlements, true);
        }

        public function expiresInDays(): int
        {
            return 5;
        }
    });

    // Rebuild DemoMode + its collaborators against the swapped gateway.
    app()->forgetInstance(DemoMode::class);
}

it('enters demo when the trial license is not usable (trigger=license)', function (): void {
    config()->set('demo-mode.trigger', 'license');
    config()->set('demo-mode.license.mode', 'trial');
    fakeLicense(present: true, usable: false, status: 'unactivated');

    expect(Demo::isActive())->toBeTrue()
        ->and(Demo::reason())->toBe('license');
});

it('stays full when the license is usable (trigger=license)', function (): void {
    config()->set('demo-mode.trigger', 'license');
    config()->set('demo-mode.license.mode', 'trial');
    fakeLicense(present: true, usable: true, status: 'valid');

    expect(Demo::isActive())->toBeFalse();
});

it('degrades to manual when no license source is present', function (): void {
    config()->set('demo-mode.trigger', 'license');
    config()->set('demo-mode.enabled', true);
    fakeLicense(present: false, usable: false, status: 'none');

    // license policy abstains → falls back to config(enabled).
    expect(Demo::isActive())->toBeTrue();
});

it('lets a runtime override win under trigger=both', function (): void {
    config()->set('demo-mode.trigger', 'both');
    config()->set('demo-mode.state.store', 'config');
    fakeLicense(present: true, usable: true, status: 'valid'); // license says full

    Demo::enable(); // override says demo

    expect(Demo::isActive())->toBeTrue()
        ->and(Demo::reason())->toBe('override');
});

it('unlocks an entitled feature even while in demo', function (): void {
    config()->set('demo-mode.trigger', 'license');
    config()->set('demo-mode.license.mode', 'trial');
    config()->set('demo-mode.features', ['export' => false]);
    fakeLicense(present: true, usable: false, status: 'grace', entitlements: ['export']);

    expect(Demo::isActive())->toBeTrue()
        ->and(Demo::allows('export'))->toBeTrue()   // entitlement unlocks it
        ->and(Demo::allows('settings'))->toBeTrue() // unregistered → allowed
        ->and(Demo::denies('reports'))->toBeFalse();
});
