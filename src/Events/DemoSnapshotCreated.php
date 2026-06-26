<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Dispatched after a demo baseline snapshot is captured.
 */
final readonly class DemoSnapshotCreated
{
    use Dispatchable;

    public function __construct(
        public string $name,
    ) {}
}
