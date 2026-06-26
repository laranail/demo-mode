<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Contracts;

/**
 * Restores the application's data to its demo baseline.
 *
 * Implementations only handle the DATABASE concern; file/cache/session scope is
 * orchestrated by {@see ResetManager}.
 */
interface ResetStrategy
{
    public function reset(): void;

    public function name(): string;
}
