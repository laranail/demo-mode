<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\State;

use Simtabi\Laranail\Demo\Mode\Contracts\StateStore;

/**
 * Request-scoped override with no persistence — the toggle lives only for the
 * current process. Suitable when demo state is driven entirely by config or the
 * license and you only need transient overrides in tests/tinker.
 */
final class ConfigStateStore implements StateStore
{
    private ?bool $override = null;

    public function get(): ?bool
    {
        return $this->override;
    }

    public function set(bool $active): void
    {
        $this->override = $active;
    }

    public function forget(): void
    {
        $this->override = null;
    }
}
