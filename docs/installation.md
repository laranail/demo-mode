# Installation

Install `laranail/demo-mode`, publish what you need, and wire the optional integrations.

```bash
composer require laranail/demo-mode
```

The service provider is auto-discovered. Publish what you need:

```bash
php artisan vendor:publish --tag="demo-mode-config"      # config/demo-mode.php
php artisan vendor:publish --tag="demo-mode-views"       # banner / blocked views
php artisan vendor:publish --tag="demo-mode-migrations"  # only if state.store = database
php artisan migrate                                       # only if you published migrations
```

## Requirements

- PHP `^8.4 || ^8.5`
- Laravel `^13`

## Optional dependencies

| Package | Enables |
|---|---|
| `laranail/license-verifier` | license-aware activation (`trigger = license|both`) |
| `spatie/laravel-db-snapshots` | the `snapshot` reset strategy |
| `spatie/laravel-backup` | the `backup-restore` reset strategy |
| `stancl/tenancy` | the `tenant` sandbox integration |

None are required — the package degrades gracefully when they are absent.

## Scheduling resets

If you use scheduled resets (`demo-mode.reset.schedule`), make sure the Laravel
scheduler runs on the host:

```cron
* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
```

## Next steps

- [Getting started](getting-started.md) — the facade, middleware, model, and Blade surfaces.
- [Configuration](configuration.md) — every section of `config/demo-mode.php`.

---

[← Docs index](../README.md#documentation)
