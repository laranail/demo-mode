<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Contracts;

/**
 * Persists the runtime demo override toggled by Demo::enable()/disable().
 *
 * Implementations must be resilient: a read that cannot be satisfied (e.g. the
 * backing table is missing mid-migration) returns null rather than throwing.
 */
interface StateStore
{
    /**
     * The stored override, or null when no explicit override is set.
     */
    public function get(): ?bool;

    public function set(bool $active): void;

    public function forget(): void;
}
