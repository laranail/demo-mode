<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\License;

use Simtabi\Laranail\Demo\Mode\Contracts\LicenseGateway;

/**
 * Default gateway used when laranail/license-verifier is not installed. It
 * reports "no license source", so license-driven triggers degrade to manual.
 */
final class NullLicenseGateway implements LicenseGateway
{
    public function isPresent(): bool
    {
        return false;
    }

    public function isUsable(): bool
    {
        return false;
    }

    public function status(): string
    {
        return 'none';
    }

    public function entitledTo(string $feature): bool
    {
        return false;
    }

    public function expiresInDays(): ?int
    {
        return null;
    }
}
