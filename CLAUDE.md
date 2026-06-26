# CLAUDE.md

Guidance for Claude Code when working in `laranail/demo-mode`.

## What this package is

A **comprehensive, granular, license-aware demo/sandbox controller** for Laravel products. It turns any
app into a controllable public demo or trial: read-only & write guards, per-model/route/feature gating,
reset/restore, per-visitor sandboxing, demo accounts and a banner. It **complements** the licensing
ecosystem — it consumes `laranail/license-verifier` through a soft `LicenseGateway` adapter and never
re-implements licensing. It is fully reusable standalone (no hard dependency on the verifier).

- Namespace: `Simtabi\Laranail\Demo\Mode\`. Composer slug / config key: `demo-mode`. Env prefix: `DEMO_MODE_*`.
- Provider: `…\Providers\DemoModeServiceProvider` (extends `laranail/package-tools`).
- Facade: `…\Facades\Demo` (alias `Demo`, also `DemoMode`). Orchestrator: `…\DemoMode`.

## Architecture

- **Activation** (`State/DemoState` + `Policies/*` + `State/BypassResolver`): `trigger` =
  manual|license|both|environment|schedule. `isActive()` resolves bypass → runtime override → license
  policy → config, memoised per request on the `DemoMode` singleton (bypass stays fresh).
- **Guards** (`Guards/*`): layered write protection — HTTP middleware, Eloquent wildcard listener
  (`EloquentWriteGuard` + `PreventsDemoWrites`), opt-in `WriteBlockingConnection` for mass ops, attribute
  guard, `ConsoleGuard`, side-effect guard. `Demo::withoutGuards()` suspends them (used during reset/seed).
- **Reset** (`Reset/*`): `ResetManager` + `ResetStrategy` drivers, safety gate, `ResetLock`, scope toggles,
  demo-state re-establishment. Strategies resolve from `demo-mode.reset.strategy.{name}` bindings.
- **Sandbox** (`Sandbox/*` + `Concerns/BelongsToDemoSandbox`): shared|transactional|scoped|tenant.
- **CLI/TUI**: commands under `src/Commands/*` use `laranail/console` and are namespaced
  `laranail::demo-mode.*` (with `demo:*` aliases).

## Conventions

- Follow laranail (`/opensource/laranail/CLAUDE.md`) and Simtabi (`/opensource/CLAUDE.md`) conventions.
- `declare(strict_types=1);` everywhere; `final` classes; explicit types; early returns; DRY.
- The verifier is a **soft** dependency: anything touching it binds only behind
  `class_exists(Simtabi\Laranail\Licence\Verifier\LicenseManager)` and goes through `Contracts\LicenseGateway`.
- Check sibling files / `laranail/package-tools` + `laranail/console` APIs before adding new patterns.

## Commands

```bash
composer test      # Pest (Unit + Feature)
composer lint      # pint --test + phpstan + rector --dry-run
composer format    # pint
composer analyse   # phpstan
```

## Testing

- Pest via Orchestra Testbench; base `Tests\TestCase` (in-memory SQLite).
- License-driven paths are tested with an in-memory `LicenseGateway` fake (no verifier dependency).
- Reset is tested with a spy `ResetStrategy` — never really run `migrate:fresh` against the test DB.

## Do not

- Do not add a hard dependency on `laranail/license-verifier` (keep it `suggest` + soft binding).
- Do not re-implement licensing — consume it via `LicenseGateway`.
- Do not block the package's own writes/commands — they run under `withoutGuards()`.
