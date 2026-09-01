<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Reset;

use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\Repository;
use Throwable;

/**
 * Guards against concurrent or over-eager resets using an atomic cache lock
 * plus a minimum-interval check.
 */
final readonly class ResetLock
{
    public function __construct(private CacheFactory $cache) {}

    /**
     * Run the callback while holding the reset lock; returns false when the lock
     * is already held or the min-interval has not elapsed.
     */
    public function run(callable $callback): bool
    {
        if (! $this->intervalElapsed()) {
            return false;
        }

        $store = $this->store()->getStore();

        // Stores without atomic locks (rare) simply run without one.
        if (! $store instanceof LockProvider) {
            $callback();
            $this->stampLastReset();

            return true;
        }

        $lock = $store->lock('demo-mode:reset', 600);

        if (! $lock->get()) {
            return false;
        }

        try {
            $callback();
            $this->stampLastReset();
        } finally {
            $lock->release();
        }

        return true;
    }

    private function intervalElapsed(): bool
    {
        $min = (int) config('demo-mode.reset.min_interval', 0);

        if ($min <= 0) {
            return true;
        }

        $last = $this->store()->get('demo-mode:reset:last');

        return $last === null || (time() - (int) $last) >= $min;
    }

    private function stampLastReset(): void
    {
        try {
            $this->store()->forever('demo-mode:reset:last', time());
        } catch (Throwable) {
            // Best effort.
        }
    }

    private function store(): Repository
    {
        return $this->cache->store(config('demo-mode.state.cache_store'));
    }
}
