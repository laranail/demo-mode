# Commands

Six Artisan commands manage demo mode at the console — canonically named
`laranail::demo-mode.*`, each with a short `demo:*` alias.

| Command | Alias | Purpose |
|---------|-------|---------|
| `laranail::demo-mode.enable` | `demo:enable` | Enable demo mode (runtime override). |
| `laranail::demo-mode.disable` | `demo:disable` | Disable demo mode (runtime override). |
| `laranail::demo-mode.status` | `demo:status` | Show the current demo-mode status. |
| `laranail::demo-mode.reset` | `demo:reset` | Reset the demo to its baseline (data, files, cache). |
| `laranail::demo-mode.snapshot` | `demo:snapshot` | Capture the current database as the demo baseline snapshot. |
| `laranail::demo-mode.doctor` | `demo:doctor` | Diagnose the demo-mode configuration and environment. |

```bash
php artisan demo:enable
php artisan demo:status
php artisan demo:reset --force
```

## Options & arguments

| Command | Option / argument | Effect |
|---------|-------------------|--------|
| `laranail::demo-mode.reset` | `--strategy=` | Override the configured reset strategy for this run. |
| `laranail::demo-mode.reset` | `--force` | Skip the confirmation prompt. |
| `laranail::demo-mode.snapshot` | `name?` | Snapshot name (requires `spatie/laravel-db-snapshots`). |
| `laranail::demo-mode.doctor` | `--json` | Emit the diagnosis as JSON. |

`enable` / `disable` write the runtime override to the configured state store
(`state.store`) — they take effect with the `manual` and `both` triggers. `reset` honours
the safety rules in [Reset & restore](reset-strategies.md); its runs suspend the guards
internally via `Demo::withoutGuards()`. When `console.guard` is on, the package's own
commands are always exempt from console blocking (see [Write guards](guards.md)).

---

[← Docs index](../../README.md#documentation)
