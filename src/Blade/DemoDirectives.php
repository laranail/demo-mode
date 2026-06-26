<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Blade;

use Illuminate\Support\Facades\Blade;
use Simtabi\Laranail\Demo\Mode\DemoMode;

/**
 * Registers the demo blade directives (each used with a leading `@` in Blade):
 *
 *   demo / enddemo                  — rendered only while demo is active
 *   unlessdemo / endunlessdemo      — rendered only when NOT in demo
 *   demoAllows('x') / enddemoAllows — rendered when feature "x" is allowed
 *   demoReadonly / enddemoReadonly  — rendered when the app is read-only
 */
final class DemoDirectives
{
    public static function register(): void
    {
        Blade::if('demo', static fn (): bool => app(DemoMode::class)->isActive());

        Blade::if('demoReadonly', static fn (): bool => app(DemoMode::class)->isReadOnly());

        Blade::if('demoAllows', static fn (string $feature): bool => app(DemoMode::class)->allows($feature));
    }
}
