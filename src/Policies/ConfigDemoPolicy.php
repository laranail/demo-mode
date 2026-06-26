<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Policies;

use Simtabi\Laranail\Demo\Mode\Contracts\DemoPolicy;

/**
 * The plain configuration toggle (`demo-mode.enabled`). Used as the final
 * fallback in the resolution chain.
 */
final class ConfigDemoPolicy implements DemoPolicy
{
    public function decide(): bool
    {
        return (bool) config('demo-mode.enabled', false);
    }
}
