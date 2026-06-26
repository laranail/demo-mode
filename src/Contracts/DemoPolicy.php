<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Contracts;

/**
 * A pluggable source of demo-state opinion.
 */
interface DemoPolicy
{
    /**
     * Decide whether demo mode should be active.
     *
     * @return bool|null true/false for a definitive decision, null to abstain
     *                   (let the next source in the resolution chain decide).
     */
    public function decide(): ?bool;
}
