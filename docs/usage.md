# Usage

Every capability is reachable several ways — pick what fits.

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

[← Docs index](../README.md#documentation)
