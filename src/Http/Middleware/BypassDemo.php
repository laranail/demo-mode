<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Simtabi\Laranail\Demo\Mode\DemoMode;
use Symfony\Component\HttpFoundation\Response;

/**
 * Suspends demo guards for the wrapped route(s). Alias: `demo.bypass`.
 */
final readonly class BypassDemo
{
    public function __construct(private DemoMode $demo) {}

    public function handle(Request $request, Closure $next): Response
    {
        return $this->demo->withoutGuards(static fn (): Response => $next($request));
    }
}
