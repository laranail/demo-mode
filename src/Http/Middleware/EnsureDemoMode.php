<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Simtabi\Laranail\Demo\Mode\DemoMode;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts a route to demo mode only (404 otherwise) — useful for demo-only
 * pages (sample-credentials screen, reset button page). Alias: `demo.only`.
 */
final readonly class EnsureDemoMode
{
    public function __construct(private DemoMode $demo) {}

    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($this->demo->isActive(), 404);

        return $next($request);
    }
}
