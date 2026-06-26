# Write guards

Demo-mode blocks writes in layers, because no single layer is sufficient.

| Layer | Catches | Misses |
|---|---|---|
| **HTTP** (`demo.readonly`) | mutating requests (POST/PUT/PATCH/DELETE) | non-HTTP writes (jobs, console, tinker) |
| **Eloquent** (`PreventsDemoWrites` / config models) | `save()`/`create()`/`update()`/`delete()` per model & operation | **mass** `Model::where()->update()/->delete()` |
| **Strict connection** (`write.strict_connection`) | every write statement, incl. mass ops | — |

> Eloquent model events do **not** fire on mass `update()`/`delete()`. If you need
> airtight blocking, enable `write.strict_connection` (or use a read-only DB user).

## Strategies (`write.strategy`)

- `block` *(default)* — deny writes (HTTP → 423/redirect; model → `DemoModeException`).
- `ephemeral` — wrap each request (`demo.ephemeral`) in a transaction that is always
  rolled back: writes appear to work but vanish on reload. **Caveat:** only database
  writes roll back; files, mail, queue and non-DB session writes do not — compose with
  `demo.safe`.
- `readonly-connection` — point the default connection at a read-only DB user
  (`write.readonly_connection`). **Caveat:** requires non-`database` session/cache/queue
  drivers.
- `allow` — do not block writes (feature-gate only).

## Allowlist & bypass

```php
'write' => ['allow' => [
    'names'  => ['login', 'logout'],
    'paths'  => ['demo/*'],
    'routes' => [],
]],
```

Suspend all guards for a block of work — used internally during reset/seed:

```php
Demo::withoutGuards(fn () => DB::table('users')->truncate());
```

Bypassed actors (config `bypass.*`) are never subject to any guard.

## Side effects & console

- `demo.safe` middleware routes mail → `log` and broadcasting → `null` while demo is
  active (per `side_effects.*`), per-request so bypassed users still send real mail.
- `console.guard` blocks the artisan commands listed in `console.protected` while demo
  is active (the package's own commands and `withoutGuards()` are always exempt).

[← Docs index](../README.md#documentation)
