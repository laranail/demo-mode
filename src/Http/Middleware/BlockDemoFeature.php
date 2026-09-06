<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Simtabi\Laranail\Demo\Mode\DemoMode;
use Symfony\Component\HttpFoundation\Response;
use Simtabi\Laranail\Demo\Mode\Support\BlockResponder;
use Simtabi\Laranail\Demo\Mode\Events\DemoActionBlocked;

/**
 * Blocks a route when the named demo feature is disabled. Alias: `demo.feature`.
 *
 *   Route::post(...)->middleware('laranail-demo-mode.feature:export');
 */
final readonly class BlockDemoFeature
{
    public function __construct(
        private DemoMode $demo,
        private BlockResponder $responder,
    ) {}

    public function handle(Request $request, Closure $next, string $feature): Response
    {
        if ($this->demo->allows($feature)) {
            return $next($request);
        }

        if ((bool) config('demo-mode.events.enabled', true)) {
            event(new DemoActionBlocked('feature', $feature));
        }

        return $this->responder->respond($request, __('laranail-demo-mode::blocked.feature', ['feature' => $feature]));
    }
}
