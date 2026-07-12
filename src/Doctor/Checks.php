<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Doctor;

use Simtabi\Laranail\Package\Tools\Services\Doctor\DoctorCheck;

/**
 * The canonical demo-mode health checks — one list reused by the service
 * provider (unified doctor) and the doctor command. All are package-specific
 * (StateStore reachability, reset strategy/environment, and the trigger-aware
 * verifier presence check).
 */
final class Checks
{
    /**
     * @return list<DoctorCheck|class-string<DoctorCheck>>
     */
    public static function all(): array
    {
        return [
            StateStoreCheck::class,
            ResetStrategyCheck::class,
            ResetEnvironmentCheck::class,
            VerifierPresentCheck::class,
        ];
    }
}
