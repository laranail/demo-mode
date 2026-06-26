<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Sandbox;

use Illuminate\Support\Str;

/**
 * Holds the current per-visitor sandbox id for the `scoped` strategy
 * (request-scoped singleton).
 */
final class SandboxContext
{
    private ?string $id = null;

    public function id(): ?string
    {
        return $this->id;
    }

    public function set(?string $id): void
    {
        $this->id = $id;
    }

    public function ensure(): string
    {
        return $this->id ??= (string) Str::uuid();
    }

    public function clear(): void
    {
        $this->id = null;
    }
}
