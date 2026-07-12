<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Doctor;

use Simtabi\Laranail\Package\Tools\Services\Doctor\DoctorCheck;
use Simtabi\Laranail\Package\Tools\Services\Doctor\DoctorResult;

/**
 * The configured reset strategy must be one the package can resolve.
 */
final class ResetStrategyCheck implements DoctorCheck
{
    private const array KNOWN = ['migrate-fresh-seed', 'snapshot', 'sql-dump', 'backup-restore', 'callback'];

    public function name(): string
    {
        return 'demo-mode:reset-strategy';
    }

    public function description(): string
    {
        return 'The configured reset strategy is resolvable';
    }

    public function run(): DoctorResult
    {
        $strategy = (string) config('demo-mode.reset.strategy', 'migrate-fresh-seed');

        return in_array($strategy, self::KNOWN, true)
            ? DoctorResult::pass("Reset strategy \"{$strategy}\" is known.")
            : DoctorResult::fail("Unknown reset strategy \"{$strategy}\".", ['known' => self::KNOWN]);
    }
}
