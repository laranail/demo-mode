# laranail/demo-mode

[![Latest version on Packagist](https://img.shields.io/packagist/v/laranail/demo-mode.svg)](https://packagist.org/packages/laranail/demo-mode)
[![Tests](https://github.com/laranail/demo-mode/actions/workflows/tests.yml/badge.svg)](https://github.com/laranail/demo-mode/actions/workflows/tests.yml)
[![Static analysis](https://github.com/laranail/demo-mode/actions/workflows/static-analysis.yml/badge.svg)](https://github.com/laranail/demo-mode/actions/workflows/static-analysis.yml)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

> A granular, license-aware demo / sandbox controller for Laravel products — block writes, gate features, reset data periodically, isolate visitors, auto-login, and show a banner, all configurable with sensible defaults.

Requires PHP `^8.4 || ^8.5` on Laravel `^13`. Complements [`laranail/license-verifier`](https://opensource.simtabi.com/documentation/laranail/license-verifier/) (a trial app can drop into demo mode automatically) but works standalone with no hard dependency.

## Install

```bash
composer require laranail/demo-mode
php artisan vendor:publish --tag="demo-mode-config"
```

The service provider and the `Demo` facade are auto-discovered.

## Quick start

Turn demo mode on (or drive it from the license):

```dotenv
DEMO_MODE_ENABLED=true        # manual toggle
# DEMO_MODE_TRIGGER=license   # or: demo when the license is not usable
```

Block writes on your routes and models, and gate a feature:

```php
Route::middleware('demo.readonly')->group(/* writes blocked in demo */);
Route::post('export', ...)->middleware('demo.feature:export');
```

```php
use Simtabi\Laranail\Demo\Mode\Concerns\PreventsDemoWrites;

class Setting extends Model
{
    use PreventsDemoWrites; // all writes blocked in demo
}
```

```blade
@demo <span class="badge">Demo</span> @enddemo
```

See [Getting started](docs/getting-started.md) for every surface — facade, middleware,
controllers, models, Blade, and events.

## What it controls

| Concern | Mechanism | Docs |
|---|---|---|
| Activation | `manual` / `license` / `both` / `environment` / `schedule` triggers, plus bypass by role/ability/id/IP/gate | [Configuration](docs/configuration.md) · [License integration](docs/license-integration.md) |
| Write protection | layered: HTTP middleware + Eloquent guard + strict/read-only connection | [Write guards](docs/tools/guards.md) |
| Feature gating | named features, entitlement-aware (paying customers keep full functionality) | [Getting started](docs/getting-started.md) |
| Reset & restore | pluggable strategies (`migrate-fresh-seed`, snapshot, SQL dump, callback), scheduled or on demand | [Reset & restore](docs/tools/reset-strategies.md) |
| Visitor isolation | `shared` / `transactional` / `scoped` / `tenant` sandboxes | [Per-visitor sandbox](docs/tools/sandbox.md) |
| Demo UX & safety | banner, demo accounts / auto-login, side effects to safe sinks (mail → log) | [Configuration](docs/configuration.md) · [Security](docs/security.md) |

## <a name="documentation"></a>Documentation

Full documentation is at **[opensource.simtabi.com/documentation/laranail/demo-mode](https://opensource.simtabi.com/documentation/laranail/demo-mode/)** — getting started, activation triggers, write protection, per-model/route/feature gating, reset strategies, per-visitor sandboxing, middleware, the Artisan commands, and configuration.

### Guides

- [Installation](docs/installation.md) — requirements, publishing, optional dependencies, scheduling.
- [Getting started](docs/getting-started.md) — facade, middleware, controllers, models, Blade, events.
- [Configuration](docs/configuration.md) — every section of `config/demo-mode.php`.
- [Architecture](docs/architecture.md) — components, activation/request/reset flows, prior art.
- [License integration](docs/license-integration.md) — driving demo mode from the license state.
- [Security & threat model](docs/security.md) — what the guards do and do not protect against.
- [Release](docs/release.md) — versioning and how releases are cut.

### Reference

- [Commands](docs/tools/commands.md) — `laranail::demo-mode.*` and the `demo:*` aliases.
- [Write guards](docs/tools/guards.md) — the layered write-protection model and strategies.
- [Reset & restore](docs/tools/reset-strategies.md) — strategies, scope, safety, triggers.
- [Per-visitor sandbox](docs/tools/sandbox.md) — shared, transactional, scoped, and tenant isolation.

## Stability

Semver. The package is on the `0.x` line — minor versions may contain breaking changes until 1.0;
pin to a patch range and read the [CHANGELOG](CHANGELOG.md) and [UPGRADE guide](UPGRADE.md) before
upgrading.

## Local development

```bash
composer install
composer test    # pest
composer lint    # pint + phpstan + rector --dry-run
```

## Sister packages

The laranail licensing family:

| Package | What it is |
|---|---|
| [`laranail/license-kit`](https://opensource.simtabi.com/documentation/laranail/license-kit/) | Server-side licensing — activation keys, polymorphic assignment, expirations/renewals, seat control. |
| [`laranail/license-verifier`](https://opensource.simtabi.com/documentation/laranail/license-verifier/) | Headless, provider-agnostic verification client — PASETO/Ed25519 offline verification, fingerprinting, seats, grace periods. |
| [`laranail/license-verifier-ui`](https://opensource.simtabi.com/documentation/laranail/license-verifier-ui/) | UI engine for the verifier — scaffolds owned, themeable preset packages (Blade, Livewire, Filament, Vue). |
| [`laranail/product-updater`](https://opensource.simtabi.com/documentation/laranail/product-updater/) | License-gated self-update engine — checks a source, verifies archives, applies releases safely. |

## Community

Questions, bug reports, and ideas go to [GitHub Issues](https://github.com/laranail/demo-mode/issues).

## Contributing & security

Issues and PRs are welcome — see [CONTRIBUTING.md](CONTRIBUTING.md). Report vulnerabilities per
[SECURITY.md](SECURITY.md) (opensource@simtabi.com); participation follows the [Code of Conduct](CODE_OF_CONDUCT.md).

## License

MIT © Simtabi LLC. See [LICENSE](LICENSE).
