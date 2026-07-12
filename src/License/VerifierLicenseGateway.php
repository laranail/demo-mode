<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\License;

use Simtabi\Laranail\Demo\Mode\Contracts\LicenseGateway;
use Throwable;

/**
 * Adapts laranail/license-verifier's LicenseManager to {@see LicenseGateway}.
 *
 * Bound by the service provider ONLY when the verifier is installed, so this
 * class is never instantiated otherwise. The manager is typed loosely (object)
 * to avoid a hard compile-time dependency on the verifier package.
 */
final readonly class VerifierLicenseGateway implements LicenseGateway
{
    public function __construct(private object $manager) {}

    public function isPresent(): bool
    {
        return true;
    }

    public function isUsable(): bool
    {
        try {
            return (bool) $this->manager->verify()->isUsable();
        } catch (Throwable) {
            return false;
        }
    }

    public function status(): string
    {
        try {
            $status = $this->manager->verify()->status;

            return is_object($status) && property_exists($status, 'value')
                ? (string) $status->value
                : (string) $status;
        } catch (Throwable) {
            return 'unreachable';
        }
    }

    public function entitledTo(string $feature): bool
    {
        try {
            return (bool) $this->manager->entitledTo($feature);
        } catch (Throwable) {
            return false;
        }
    }

    public function expiresInDays(): ?int
    {
        try {
            $expiresAt = $this->manager->licenseInfo()->expiresAt;

            if (! is_string($expiresAt) || $expiresAt === '') {
                return null;
            }

            return (int) ceil((strtotime($expiresAt) - time()) / 86400);
        } catch (Throwable) {
            return null;
        }
    }
}
