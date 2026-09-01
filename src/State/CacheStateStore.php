<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\State;

use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Cache\Repository;
use Simtabi\Laranail\Demo\Mode\Contracts\StateStore;
use Throwable;

/**
 * Persists the override in the cache so it is shared across requests/processes.
 */
final readonly class CacheStateStore implements StateStore
{
    public function __construct(private CacheFactory $cache) {}

    public function get(): ?bool
    {
        try {
            $value = $this->store()->get($this->key());

            return $value === null ? null : (bool) $value;
        } catch (Throwable) {
            return null;
        }
    }

    public function set(bool $active): void
    {
        try {
            $this->store()->forever($this->key(), $active);
        } catch (Throwable) {
            // Best effort — a missing cache store should never break the app.
        }
    }

    public function forget(): void
    {
        try {
            $this->store()->forget($this->key());
        } catch (Throwable) {
            // Best effort.
        }
    }

    private function store(): Repository
    {
        return $this->cache->store(config('demo-mode.state.cache_store'));
    }

    private function key(): string
    {
        return (string) config('demo-mode.state.key', 'demo-mode:state');
    }
}
