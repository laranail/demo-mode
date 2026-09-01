<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Policies;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Str;
use Simtabi\Laranail\Demo\Mode\Contracts\DemoPolicy;
use Simtabi\Laranail\Demo\Mode\Contracts\LicenseGateway;

/**
 * Maps license state to a demo decision. Abstains (returns null) when no license
 * source is present, so license-driven triggers degrade gracefully.
 *
 * Modes (config `demo-mode.license.mode`):
 *   trial            — demo unless the license is usable.
 *   unlicensed       — demo only when there is no activated license at all.
 *   entitlement:NAME — demo unless the license is entitled to NAME.
 *   callback         — defer to the `demo-mode.license.resolver` binding.
 */
final readonly class LicenseDemoPolicy implements DemoPolicy
{
    public function __construct(
        private Container $app,
        private LicenseGateway $license,
    ) {}

    public function decide(): ?bool
    {
        if (! $this->license->isPresent()) {
            return null;
        }

        $mode = (string) config('demo-mode.license.mode', 'trial');

        if (Str::startsWith($mode, 'entitlement:')) {
            return ! $this->license->entitledTo(Str::after($mode, 'entitlement:'));
        }

        return match ($mode) {
            'unlicensed' => in_array($this->license->status(), ['none', 'unactivated'], true),
            'callback' => $this->viaCallback(),
            default => ! $this->license->isUsable(),
        };
    }

    private function viaCallback(): ?bool
    {
        if (! $this->app->bound('demo-mode.license.resolver')) {
            return ! $this->license->isUsable();
        }

        $resolver = $this->app->make('demo-mode.license.resolver');
        $result = $resolver($this->license);

        return $result === null ? null : (bool) $result;
    }
}
