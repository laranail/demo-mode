<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Support;

use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Simtabi\Laranail\Demo\Mode\Contracts\LicenseGateway;

/**
 * Computes the data shown in the demo banner (message, reset countdown, license
 * trial expiry, CTA).
 */
final readonly class DemoBanner
{
    public function __construct(
        private CacheFactory $cache,
        private LicenseGateway $license,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function data(): array
    {
        return [
            'message' => config('demo-mode.banner.message') ?? __('demo-mode::banner.message'),
            'position' => (string) config('demo-mode.banner.position', 'top'),
            'dismissible' => (bool) config('demo-mode.banner.dismissible', true),
            'reset_in' => $this->secondsUntilReset(),
            'expires_in_days' => $this->license->isPresent() ? $this->license->expiresInDays() : null,
            'cta' => (array) config('demo-mode.banner.cta', []),
        ];
    }

    public function secondsUntilReset(): ?int
    {
        if (! (bool) config('demo-mode.banner.countdown', true)) {
            return null;
        }

        $interval = (int) config('demo-mode.reset.min_interval', 0);

        if ($interval <= 0) {
            return null;
        }

        $last = (int) $this->cache->store(config('demo-mode.state.cache_store'))->get('demo-mode:reset:last', 0);

        $remaining = ($last + $interval) - time();

        return max($remaining, 0);
    }
}
