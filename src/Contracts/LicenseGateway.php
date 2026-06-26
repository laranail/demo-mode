<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Contracts;

/**
 * Thin adapter over a license source so demo-mode never hard-depends on
 * laranail/license-verifier. The default binding ({@see NullLicenseGateway})
 * reports "not present"; when the verifier is installed the provider binds the
 * adapter that delegates to its LicenseManager.
 */
interface LicenseGateway
{
    /**
     * Whether a license source is available at all (drives soft-dependency behaviour).
     */
    public function isPresent(): bool;

    /**
     * Whether the current license is usable (valid or within grace).
     */
    public function isUsable(): bool;

    /**
     * Raw status token (e.g. valid|grace|expired|unactivated|revoked|unreachable|none).
     */
    public function status(): string;

    /**
     * Whether the license grants the given feature/entitlement.
     */
    public function entitledTo(string $feature): bool;

    /**
     * Days until expiry, or null when unknown / not applicable.
     */
    public function expiresInDays(): ?int;
}
