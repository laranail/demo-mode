<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Simtabi\Laranail\Demo\Mode\DemoMode;
use Simtabi\Laranail\Demo\Mode\Events\DemoActionBlocked;
use Simtabi\Laranail\Demo\Mode\Support\BlockResponder;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks a named action while demo is active unless it is permitted as a
 * feature. Alias: `demo.guard:<action>`.
 */
final readonly class GuardDemoAction
{
    public function __construct(
        private DemoMode $demo,
        private BlockResponder $responder,
    ) {}

    public function handle(Request $request, Closure $next, string $action): Response
    {
        if (! $this->demo->isActive() || $this->demo->allows($action)) {
            return $next($request);
        }

        if ((bool) config('demo-mode.events.enabled', true)) {
            event(new DemoActionBlocked('action', $action));
        }

        return $this->responder->respond($request, __('demo-mode::blocked.message'));
    }
}
