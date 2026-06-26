# Reset & restore

`Demo::reset()` (or `php artisan demo:reset`) restores the demo to its baseline.

## Strategies (`reset.strategy`)

| Strategy | How |
|---|---|
| `migrate-fresh-seed` *(default)* | `migrate:fresh` then optional `reset.seeder` (no extra deps) |
| `snapshot` | load a `spatie/laravel-db-snapshots` snapshot (`reset.snapshot_name`) |
| `sql-dump` | replay a SQL file (`reset.sql_dump_path`) |
| `backup-restore` | run your `spatie/laravel-backup` restore via `reset.callback` |
| `callback` | run `reset.callback` |

Register a custom strategy by binding `demo-mode.reset.strategy.{name}` to a
`ResetStrategy`.

## Scope

Each concern is toggleable under `reset.scope`: `database`, `files`, `cache`,
`sessions`, `queue`, `logs`. File reset restores a baseline directory and/or purges
configured disks (`reset.files`).

## Safety

A reset refuses to run unless:

- `reset.enabled` is true, **and**
- the environment is in `reset.allowed_environments` (default `local, demo, staging`) —
  **never `production`** without `reset.allow_production`.

Because `migrate:fresh` drops the package's own `demo_state` table, reset
**re-establishes the demo override afterward** so demo mode does not silently switch off.
A `ResetLock` (+ `reset.min_interval`) prevents concurrent/over-eager resets.

## Triggers

- **Scheduled** — set `reset.schedule` (and run the host scheduler).
- **On demand** — enable `reset.on_demand`; `POST {prefix}/reset` (CSRF + throttled +
  optional Gate) runs a reset. Wire a "Reset demo" button to it.

## Snapshot the baseline

```bash
php artisan demo:snapshot   # requires spatie/laravel-db-snapshots
```

[← Docs index](../README.md#documentation)
