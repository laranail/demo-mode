<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Dispatched when a guarded action (HTTP write, model write, feature, console
 * command) is blocked while demo mode is active.
 */
final readonly class DemoActionBlocked
{
    use Dispatchable;

    public function __construct(
        public string $type,
        public string $target,
        public ?string $actor = null,
    ) {}
}
