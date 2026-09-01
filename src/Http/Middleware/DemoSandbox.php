<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Simtabi\Laranail\Demo\Mode\DemoMode;
use Simtabi\Laranail\Demo\Mode\Events\DemoSandboxCreated;
use Simtabi\Laranail\Demo\Mode\Sandbox\SandboxContext;
use Symfony\Component\HttpFoundation\Response;

/**
 * Establishes the per-visitor sandbox id for the `scoped` strategy and persists
 * it in the session + a cookie. Alias: `demo.sandbox`.
 */
final readonly class DemoSandbox
{
    public function __construct(
        private DemoMode $demo,
        private SandboxContext $context,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (config('demo-mode.sandbox.strategy') !== 'scoped' || ! $this->demo->isActive()) {
            return $next($request);
        }

        $cookie = (string) config('demo-mode.sandbox.cookie', 'demo_sandbox');
        $id = $request->cookie($cookie) ?: $request->session()->get('demo_sandbox_id');

        if (! is_string($id) || $id === '') {
            $id = (string) Str::uuid();
            $request->session()->put('demo_sandbox_id', $id);

            if ((bool) config('demo-mode.events.enabled', true)) {
                event(new DemoSandboxCreated($id));
            }
        }

        $this->context->set($id);

        $response = $next($request);

        $ttlMinutes = (int) ceil(((int) config('demo-mode.sandbox.ttl', 3600)) / 60);

        return $response->cookie($cookie, $id, $ttlMinutes);
    }
}
