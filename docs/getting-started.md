# Getting started

Every capability is reachable several ways — facade, middleware, controllers, models, Blade, events — pick what fits.

Start with [Installation](installation.md), then activate demo mode (`DEMO_MODE_ENABLED=true`, or
a license-driven trigger — see [Configuration](configuration.md) and
[License integration](license-integration.md)).

## Facade

```php
use Simtabi\Laranail\Demo\Mode\Facades\Demo;

Demo::isActive(); Demo::isReadOnly(); Demo::reason();
Demo::enable(); Demo::disable(); Demo::clearOverride();
Demo::allows('export'); Demo::denies('export'); Demo::guard('export');
Demo::protect('export', 'settings'); Demo::permit('export');
Demo::bypasses(); Demo::withoutGuards(fn () => /* admin write */);
Demo::reset(); Demo::snapshot(); Demo::configure(['write.strategy' => 'ephemeral']);
Demo::license(); // the LicenseGateway
```

## Middleware

```php
Route::middleware('demo.readonly')->group(/* writes blocked in demo */);
Route::post('export', ...)->middleware('demo.feature:export');
Route::post('publish', ...)->middleware('demo.guard:publish');
Route::middleware('demo.banner')->group(/* inject the banner */);
Route::middleware('demo.ephemeral')->group(/* writes roll back */);
Route::middleware('demo.sandbox')->group(/* per-visitor scoped data */);
Route::middleware('demo.autologin')->group(/* log in as a demo user */);
Route::middleware('demo.safe')->group(/* mail -> log, etc. */);
Route::middleware('demo.bypass')->group(/* guards suspended here */);
Route::middleware('demo.only')->group(/* 404 unless in demo */);
```

Attach globally by listing groups in `demo-mode.middleware_groups`, or add them
to your `web` group.

## Controllers

```php
use Simtabi\Laranail\Demo\Mode\Concerns\HandlesDemoMode;

class SettingsController extends Controller
{
    use HandlesDemoMode; // or extend DemoAwareController

    public function update()
    {
        $this->guardDemo('settings'); // throws DemoModeException in demo
        // ...
    }
}
```

## Models

```php
use Simtabi\Laranail\Demo\Mode\Concerns\PreventsDemoWrites;

class Setting extends Model
{
    use PreventsDemoWrites;            // all writes blocked in demo
    public array $demoAllowed = [];    // ...except these operations
}
```

Or protect models (and attributes) from config without touching them:

```php
'write' => [
    'protected_models' => [
        App\Models\Setting::class,                 // all ops blocked
        App\Models\User::class => ['delete' => true],
    ],
    'protected_attributes' => [
        App\Models\User::class => ['email', 'password'], // anti privilege-escalation
    ],
],
```

Or define rules fluently at runtime (e.g. in `AppServiceProvider::boot`) with `DemoRule`:

```php
use Simtabi\Laranail\Demo\Mode\Features\DemoRule;

DemoRule::for(User::class)
    ->block()                                  // block all operations…
    ->allow('create')                          // …except create
    ->protectAttributes('email', 'password');  // and never let these change

// Demo::rule(...) is the facade shortcut for DemoRule::for(...).
```

Rules are **additive** with the trait/config (a write is blocked if any source blocks it),
except an explicit `allow($op)` which always wins. `allow()` un-blocks the *operation* only —
protected attributes stay protected. `DemoRule` governs the Eloquent layer (not `demo.readonly`).

## Audit logging

Turn on `logging.blocked` to record blocked attempts to `demo_blocked_logs`, and
`logging.reset` to record resets to `demo_reset_logs` (publish + run the migrations).
Set `logging.channel` to also mirror blocked attempts to a log channel. Blocked logging
relies on the `DemoActionBlocked` event, so it needs `events.enabled` (the default).

## Blade

```blade
@demo <span class="badge">Demo</span> @enddemo
@unlessdemo <a href="/checkout">Buy</a> @endunlessdemo
@demoAllows('export') <button>Export</button> @enddemoAllows
@demoReadonly <p>Saving is disabled.</p> @enddemoReadonly
```

## Events

`DemoModeEnabled` · `DemoModeDisabled` · `DemoActionBlocked` · `DemoResetting` ·
`DemoReset` · `DemoSandboxCreated`.

## Next steps

- [Configuration](configuration.md) — every section of `config/demo-mode.php`.
- [Write guards](tools/guards.md) — the layered write-protection model.
- [Reset & restore](tools/reset-strategies.md) and [Per-visitor sandbox](tools/sandbox.md).
- [Commands](tools/commands.md) — the `laranail::demo-mode.*` Artisan commands.

---

[← Docs index](../README.md#documentation)
