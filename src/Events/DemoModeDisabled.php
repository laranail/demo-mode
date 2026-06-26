<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Events;

use Illuminate\Foundation\Events\Dispatchable;

final readonly class DemoModeDisabled
{
    use Dispatchable;

    public function __construct(public string $reason = 'manual') {}
}
