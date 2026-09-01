<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Simtabi\Laranail\Demo\Mode\DemoMode;
use Symfony\Component\HttpFoundation\Response;

/**
 * Logs the visitor in as a pre-seeded demo account when demo mode is active and
 * no user is authenticated. Alias: `demo.autologin`. Optionally pick a role via
 * `?demo_role=admin` mapped through demo-mode.accounts.roles.
 */
final readonly class DemoAutoLogin
{
    public function __construct(private DemoMode $demo) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->demo->isActive() || ! (bool) config('demo-mode.accounts.auto_login', false)) {
            return $next($request);
        }

        $guard = Auth::guard(config('demo-mode.accounts.guard'));

        if ($guard instanceof StatefulGuard && ! $guard->check()) {
            $identifier = $this->identifier($request);

            if ($identifier !== null) {
                $guard->loginUsingId($identifier);
            }
        }

        return $next($request);
    }

    private function identifier(Request $request): mixed
    {
        $roles = (array) config('demo-mode.accounts.roles', []);
        $role = (string) $request->query('demo_role', '');

        if ($role !== '' && isset($roles[$role])) {
            return $roles[$role];
        }

        return config('demo-mode.accounts.default');
    }
}
