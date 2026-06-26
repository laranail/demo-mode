# Demo Mode

[![Tests](https://img.shields.io/github/actions/workflow/status/laranail/demo-mode/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/laranail/demo-mode/actions)
[![Packagist](https://img.shields.io/packagist/v/laranail/demo-mode.svg?style=flat-square)](https://packagist.org/packages/laranail/demo-mode)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE)

A comprehensive, **granular**, **license-aware** demo / sandbox controller for Laravel products.

Turn any application into a controllable public demo or trial: block writes, gate features, reset the data
periodically, isolate visitors, auto-login, and show a banner — all configurable with sensible defaults and
overridable at runtime. It **complements** [`laranail/license-verifier`](https://opensource.simtabi.com/license-verifier/)
(a trial/unlicensed app can drop into demo mode automatically) but has **no hard dependency** on it and is
fully usable standalone.

## Why

Existing Laravel demo packages each do one thing — password-gate the demo, or a global read-only flag, or
block a few POST routes. `demo-mode` unifies them and adds what none have: license-aware activation, layered
write protection that also catches mass `update()`/`delete()`, per-model/route/feature granularity, pluggable
reset strategies, per-visitor sandboxing, and guard suspension for admin/reset operations.

## Install

```bash
composer require laranail/demo-mode
php artisan vendor:publish --tag="demo-mode-config"
```

## Quick start

```php
use Simtabi\Laranail\Demo\Mode\Facades\Demo;

// Turn demo mode on (manual trigger), or let the license/env drive it (config).
Demo::enable();

Demo::isActive();              // true
Demo::allows('export');        // gate a named feature
Demo::guard('export');         // throw DemoModeException if denied
Demo::withoutGuards(fn () => $user->update([...])); // run an admin write
Demo::rule(User::class)->block()->allow('create')->protectAttributes('email'); // fluent model rule
Demo::reset();                 // restore the demo baseline
```

Protect routes and models:

```php
// routes/web.php
Route::middleware('demo.readonly')->group(function () {
    Route::resource('posts', PostController::class);   // writes blocked in demo
});
Route::post('export', ExportController::class)->middleware('demo.feature:export');

// app/Models/Comment.php
use Simtabi\Laranail\Demo\Mode\Concerns\PreventsDemoWrites;

class Comment extends Model
{
    use PreventsDemoWrites;
    public array $demoAllowed = ['create']; // visitors may comment, not edit/delete
}
```

Drive it from the license (with the verifier installed):

```php
// config/demo-mode.php
'trigger' => 'both',          // runtime override > license > config
'license' => ['mode' => 'trial'], // usable license => full app; otherwise demo
```

## Activation triggers

| Trigger | Demo is active when… |
|---|---|
| `manual` | the override (`Demo::enable()`) or `enabled` config says so |
| `license` | the license policy says so (falls back to config if the verifier is absent) |
| `both` *(default)* | runtime override → license policy → config (override wins) |
| `environment` | `app()->environment()` is in `environments` |
| `schedule` | the current time is inside a configured window |

**Bypass** (never in demo): office IPs, a Gate ability, or authenticated admins by role/ability/id.

## Middleware

`demo` / `demo.readonly` · `demo.feature:<name>` · `demo.guard:<action>` · `demo.only` · `demo.banner` ·
`demo.ephemeral` · `demo.sandbox` · `demo.autologin` · `demo.safe` · `demo.bypass`.

## Commands

`laranail::demo-mode.status` · `…enable` · `…disable` · `…reset` · `…snapshot` (each also as `demo:*`).

<a name="documentation"></a>

## Documentation

| Page | What it covers |
|------|----------------|
| [installation.md](docs/installation.md) | Install, publish, scheduler wiring |
| [configuration.md](docs/configuration.md) | The full config surface + env vars |
| [usage.md](docs/usage.md) | Middleware, traits, base controller, facade, blade, fluent rules |
| [guards.md](docs/guards.md) | The layered write protection + the mass-op caveat |
| [reset-strategies.md](docs/reset-strategies.md) | Reset/restore strategies, scope, safety, scheduling |
| [sandbox.md](docs/sandbox.md) | Per-visitor sandbox strategies |
| [license-integration.md](docs/license-integration.md) | License-aware activation via the verifier |
| [architecture.md](docs/architecture.md) | Internals, resolution + request + reset flows, prior art |
| [security.md](docs/security.md) | Threat model and hardening notes |

## Credits

- [Imani Manyara](https://github.com/imanimanyara) — Simtabi LLC
- [All Contributors](../../contributors)

## License

MIT. See [LICENSE](LICENSE).
