<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\State;

use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Container\Container;
use Throwable;

/**
 * Decides whether the current actor is exempt from demo restrictions
 * (the product owner, admins, office IPs, ...).
 *
 * Role/ability/id checks require an authenticated request; IP and gate checks
 * work pre-auth.
 */
final readonly class BypassResolver
{
    public function __construct(private Container $app) {}

    public function bypasses(): bool
    {
        if ($this->byIp()) {
            return true;
        }
        if ($this->byGate()) {
            return true;
        }

        return $this->byUser();
    }

    private function config(string $key): mixed
    {
        return config('demo-mode.bypass.'.$key);
    }

    private function byIp(): bool
    {
        $ips = (array) $this->config('ips');

        if ($ips === [] || ! $this->app->bound('request')) {
            return false;
        }

        $ip = $this->app->make('request')->ip();

        return $ip !== null && in_array($ip, $ips, true);
    }

    private function byGate(): bool
    {
        $ability = $this->config('gate');

        if (! is_string($ability) || $ability === '' || ! $this->app->bound(Gate::class)) {
            return false;
        }

        try {
            return $this->app->make(Gate::class)->allows($ability);
        } catch (Throwable) {
            return false;
        }
    }

    private function byUser(): bool
    {
        $user = $this->user();

        if ($user === null) {
            return false;
        }
        if ($this->userMatchesId($user)) {
            return true;
        }
        if ($this->userHasRole($user)) {
            return true;
        }

        return $this->userHasAbility($user);
    }

    private function user(): ?object
    {
        if (! $this->app->bound('request')) {
            return null;
        }

        try {
            return $this->app->make('request')->user();
        } catch (Throwable) {
            return null;
        }
    }

    private function userMatchesId(object $user): bool
    {
        $ids = (array) $this->config('ids');

        return $ids !== [] && method_exists($user, 'getKey') && in_array($user->getKey(), $ids, true);
    }

    private function userHasRole(object $user): bool
    {
        $roles = (array) $this->config('roles');

        if ($roles === [] || ! method_exists($user, 'hasRole')) {
            return false;
        }

        return array_any($roles, fn ($role) => $user->hasRole($role));
    }

    private function userHasAbility(object $user): bool
    {
        $abilities = (array) $this->config('abilities');

        if ($abilities === [] || ! method_exists($user, 'can')) {
            return false;
        }

        return array_any($abilities, fn ($ability) => $user->can($ability));
    }
}
