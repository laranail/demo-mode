# License integration

demo-mode complements [`laranail/license-verifier`](https://opensource.simtabi.com/license-verifier/)
without depending on it. All access goes through a small `Contracts\LicenseGateway`:

- when the verifier is installed, the provider binds `VerifierLicenseGateway` (delegating
  to its `LicenseManager`);
- otherwise it binds `NullLicenseGateway`, and license-driven triggers degrade to manual.

So the package is **fully usable standalone**, and license-aware the moment the verifier
is present.

## Driving demo from the license

```php
'trigger' => 'both',   // or 'license'
'license' => [
    'mode' => 'trial', // Valid license => full app; otherwise demo
    'sync_events' => true,
],
```

`license.mode`:

| Mode | Demo is active when… |
|---|---|
| `trial` *(default)* | the license is **not** usable (unactivated/grace/expired/revoked) |
| `unlicensed` | there is no activated license at all |
| `entitlement:NAME` | the license is **not** entitled to `NAME` |
| `callback` | your `demo-mode.license.resolver` binding says so |

## Entitlements unlock features

A feature disabled in demo (`features.export = false`) is still allowed when the license
is entitled to it — so paying customers keep full functionality even if the app is
otherwise demoing.

## Event sync

With `license.sync_events` on, a `LicenseActivated` event turns demo off and
`LicenseDeactivated` turns it back on (handy for `manual`/`both` triggers). The listener
is registered only when the verifier's event classes exist.

[← Docs index](../README.md#documentation)
