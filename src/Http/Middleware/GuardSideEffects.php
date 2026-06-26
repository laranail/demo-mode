<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Simtabi\Laranail\Demo\Mode\DemoMode;
use Symfony\Component\HttpFoundation\Response;

/**
 * Request-scoped side-effect guards: while demo is active for THIS request, route
 * real-world side effects to safe sinks (mail → log, broadcasting → null) per the
 * demo-mode.side_effects toggles. Alias: `demo.safe`.
 *
 * Applied per request so bypassed users still trigger real side effects.
 */
final readonly class GuardSideEffects
{
    public function __construct(private DemoMode $demo) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->demo->isActive()) {
            $effects = (array) config('demo-mode.side_effects', []);

            if ($effects['mail'] ?? false) {
                config(['mail.default' => 'log']);
            }

            if ($effects['broadcasting'] ?? false) {
                config(['broadcasting.default' => 'null']);
            }
        }

        return $next($request);
    }
}
