# Configuration

Publish `config/demo-mode.php`:

```bash
php artisan vendor:publish --tag="demo-mode-config"
```

Every key has a default and may be overridden at runtime with `config()->set(...)`
or `Demo::configure([...])`. Env vars use the `DEMO_MODE_*` prefix.

## Key sections

| Key | Purpose |
|---|---|
| `trigger`, `enabled`, `environments`, `windows` | how demo mode is activated |
| `state.store` | where the runtime override lives (`config`/`cache`/`database`) |
| `license.mode`, `license.sync_events` | license-driven activation mapping |
| `bypass.{roles,abilities,ids,ips,gate}` | who is never in demo |
| `write.strategy`, `write.methods`, `write.allow` | HTTP write protection |
| `write.protected_models`, `write.protected_attributes` | Eloquent protection (per op/attr) |
| `write.strict_connection`, `write.readonly_connection` | airtight / read-only DB blocking |
| `features` | named feature gating (entitlement-aware) |
| `console.{guard,protected}` | blocked artisan commands |
| `side_effects.{mail,notifications,broadcasting,http}` | request-scoped side-effect guards |
| `reset.*` | strategy, scope, safety, schedule, on-demand endpoint |
| `sandbox.{strategy,ttl,cookie,models}` | per-visitor isolation |
| `accounts.{auto_login,roles,default}` | demo accounts |
| `banner.*` | banner content, position, countdown, CTA |
| `logging.blocked` | record blocked attempts |
| `routes.*`, `middleware_groups` | route + auto-attach wiring |

See the published file for inline documentation of every option.

## Runtime reconfiguration

```php
Demo::configure([
    'write.strategy' => 'ephemeral',
    'features.export' => false,
]);
```

[← Docs index](../README.md#documentation)
