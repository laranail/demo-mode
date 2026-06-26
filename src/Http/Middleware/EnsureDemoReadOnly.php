<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Simtabi\Laranail\Demo\Mode\DemoMode;
use Simtabi\Laranail\Demo\Mode\Events\DemoActionBlocked;
use Simtabi\Laranail\Demo\Mode\Support\BlockResponder;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks mutating HTTP verbs while demo mode is active, honouring the
 * route/name/path allowlist. Alias: `demo`, `demo.readonly`.
 */
final readonly class EnsureDemoReadOnly
{
    public function __construct(
        private DemoMode $demo,
        private BlockResponder $responder,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->demo->isActive() || ! $this->isMutating($request) || $this->isAllowed($request)) {
            return $next($request);
        }

        if ((bool) config('demo-mode.events.enabled', true)) {
            event(new DemoActionBlocked('http', $request->method().' '.$request->path()));
        }

        return $this->responder->respond($request, __('demo-mode::blocked.write'));
    }

    private function isMutating(Request $request): bool
    {
        return in_array(strtoupper($request->method()), (array) config('demo-mode.write.methods', ['POST', 'PUT', 'PATCH', 'DELETE']), true);
    }

    private function isAllowed(Request $request): bool
    {
        $allow = (array) config('demo-mode.write.allow', []);

        $paths = (array) ($allow['paths'] ?? []);
        if ($paths !== [] && $request->is(...$paths)) {
            return true;
        }

        $route = $request->route();

        if (! $route instanceof Route) {
            return false;
        }

        $name = $route->getName();

        if ($name !== null && in_array($name, (array) ($allow['names'] ?? []), true)) {
            return true;
        }

        return in_array($route->uri(), (array) ($allow['routes'] ?? []), true);
    }
}
