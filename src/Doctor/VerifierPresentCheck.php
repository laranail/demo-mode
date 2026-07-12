<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Doctor;

use Simtabi\Laranail\Package\Tools\Services\Doctor\DoctorCheck;
use Simtabi\Laranail\Package\Tools\Services\Doctor\DoctorResult;

/**
 * When demo mode is license-driven, the verifier must be installed.
 */
final class VerifierPresentCheck implements DoctorCheck
{
    public function name(): string
    {
        return 'demo-mode:verifier';
    }

    public function description(): string
    {
        return 'The license verifier is present when demo mode is license-driven';
    }

    public function run(): DoctorResult
    {
        $trigger = (string) config('demo-mode.trigger', 'both');

        if (! in_array($trigger, ['license', 'both'], true)) {
            return DoctorResult::skip("Trigger \"{$trigger}\" is not license-driven.");
        }

        return class_exists('Simtabi\Laranail\Licence\Verifier\LicenseManager')
            ? DoctorResult::pass('laranail/license-verifier is installed.')
            : DoctorResult::warn('Trigger is license-driven but laranail/license-verifier is not installed.');
    }
}
