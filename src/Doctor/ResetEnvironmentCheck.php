<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Doctor;

use Simtabi\Laranail\Package\Tools\Services\Doctor\DoctorCheck;
use Simtabi\Laranail\Package\Tools\Services\Doctor\DoctorResult;

/**
 * Reset must be enabled and the current environment whitelisted for it.
 */
final class ResetEnvironmentCheck implements DoctorCheck
{
    public function name(): string
    {
        return 'demo-mode:reset-environment';
    }

    public function description(): string
    {
        return 'Reset is enabled and allowed in the current environment';
    }

    public function run(): DoctorResult
    {
        if (! (bool) config('demo-mode.reset.enabled', false)) {
            return DoctorResult::skip('Reset is disabled.');
        }

        $env = (string) app()->environment();
        /** @var list<string> $allowed */
        $allowed = (array) config('demo-mode.reset.allowed_environments', []);

        return in_array($env, $allowed, true)
            ? DoctorResult::pass("Reset is allowed in \"{$env}\".")
            : DoctorResult::warn("Reset is not allowed in \"{$env}\".", ['allowed' => $allowed]);
    }
}
