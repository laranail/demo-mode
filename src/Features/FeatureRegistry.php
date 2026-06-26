<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Features;

use Simtabi\Laranail\Demo\Mode\Contracts\LicenseGateway;

/**
 * Tracks which named features are enabled/disabled in demo. Gating is opt-in:
 * a feature that is not registered is allowed by default. A feature explicitly
 * disabled (false) can still be unlocked by a matching license entitlement.
 */
final class FeatureRegistry
{
    /**
     * Runtime overrides layered on top of the `demo-mode.features` config.
     *
     * @var array<string, bool>
     */
    private array $overrides = [];

    public function __construct(private readonly LicenseGateway $license) {}

    public function protect(string ...$features): void
    {
        foreach ($features as $feature) {
            $this->overrides[$feature] = false;
        }
    }

    public function permit(string ...$features): void
    {
        foreach ($features as $feature) {
            $this->overrides[$feature] = true;
        }
    }

    /**
     * Whether a feature is allowed (assuming demo is active). Unregistered
     * features default to allowed; explicitly-disabled features may be unlocked
     * by a license entitlement of the same name.
     */
    public function allows(string $feature): bool
    {
        $explicit = $this->configured($feature);

        if ($explicit === null) {
            return true;
        }

        if ($explicit) {
            return true;
        }

        return $this->license->isPresent() && $this->license->entitledTo($feature);
    }

    private function configured(string $feature): ?bool
    {
        if (array_key_exists($feature, $this->overrides)) {
            return $this->overrides[$feature];
        }

        $features = (array) config('demo-mode.features', []);

        return array_key_exists($feature, $features) ? (bool) $features[$feature] : null;
    }
}
