<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Demo Mode configuration
|--------------------------------------------------------------------------
|
| Every behaviour here has a sensible default and may be overridden at
| runtime (config()->set(...), Demo::configure([...]) or the Demo facade).
| Env vars use the DEMO_MODE_* prefix.
|
*/

$env = static fn (string $key, mixed $default = null): mixed => env('DEMO_MODE_'.$key, $default);

return [

    /*
    |--------------------------------------------------------------------------
    | Activation trigger
    |--------------------------------------------------------------------------
    | How demo mode is turned on:
    |   manual      — only the explicit toggle (`enabled` below + Demo::enable()/disable()).
    |   license     — only the license policy (requires laranail/license-verifier).
    |   both        — runtime override > license policy > `enabled` (default).
    |   environment — active only in the listed `environments`.
    |   schedule    — active only within the configured `windows`.
    */
    'trigger' => $env('TRIGGER', 'both'),

    'enabled' => (bool) $env('ENABLED', false),

    'environments' => ['demo'],

    // Time windows for the `schedule` trigger (CRON-less): [['from' => 'H:i', 'to' => 'H:i'], ...].
    'windows' => [],

    /*
    |--------------------------------------------------------------------------
    | Runtime state store
    |--------------------------------------------------------------------------
    | Where the Demo::enable()/disable() override is persisted.
    |   config   — in-memory for the request only (no persistence).
    |   cache    — shared across requests via the cache store.
    |   database — persisted in the demo_state table.
    */
    'state' => [
        'store' => $env('STATE_STORE', 'cache'),
        'cache_store' => $env('STATE_CACHE_STORE', null),
        'key' => 'demo-mode:state',
    ],

    /*
    |--------------------------------------------------------------------------
    | License-driven activation
    |--------------------------------------------------------------------------
    | Used when trigger is `license` or `both` AND laranail/license-verifier is
    | installed. mode:
    |   trial            — usable license => full app; otherwise demo.
    |   unlicensed       — demo only when there is no usable license at all.
    |   entitlement:NAME — demo unless the license is entitled to NAME.
    |   callback         — resolve via the `license.resolver` binding.
    */
    'license' => [
        'mode' => $env('LICENSE_MODE', 'trial'),
        // Auto-disable demo when a LicenseActivated event fires (and re-enable on deactivation).
        'sync_events' => (bool) $env('LICENSE_SYNC_EVENTS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Bypass — who is NEVER in demo (admins, the owner, office IPs, ...)
    |--------------------------------------------------------------------------
    | Role/ability/id checks require an authenticated request; ip/gate work
    | pre-auth. `gate` is the name of a Gate ability returning true to bypass.
    */
    'bypass' => [
        'roles' => [],
        'abilities' => [],
        'ids' => [],
        'ips' => [],
        'gate' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Write protection
    |--------------------------------------------------------------------------
    | strategy:
    |   block               — deny mutating requests / model writes (default).
    |   ephemeral           — wrap each request in a transaction, roll back on terminate.
    |   readonly-connection — point the default connection at `readonly_connection`.
    |   allow               — do not block writes (gate features only).
    */
    'write' => [
        'strategy' => $env('WRITE_STRATEGY', 'block'),

        // HTTP layer: methods treated as mutating.
        'methods' => ['POST', 'PUT', 'PATCH', 'DELETE'],

        // Routes/paths/names always allowed even in demo (e.g. login, the reset button).
        'allow' => [
            'routes' => [],
            'names' => ['login', 'logout'],
            'paths' => ['demo/*'],
        ],

        // Eloquent layer: models guarded against writes. Either a bare class name
        // (all ops blocked) or class => ['create' => true, 'update' => false, ...].
        'protected_models' => [],

        // Per-model attributes that may never change in demo (anti privilege-escalation).
        'protected_attributes' => [
            // App\Models\User::class => ['email', 'password'],
        ],

        // Opt-in airtight blocking that also stops mass update()/delete()
        // (Eloquent observers do NOT fire on those).
        'strict_connection' => (bool) $env('WRITE_STRICT', false),

        // Connection name to swap in for the `readonly-connection` strategy.
        'readonly_connection' => $env('READONLY_CONNECTION', null),

        // Response for a blocked write: 'auto' (JSON for API, redirect for web),
        // 'json', or 'redirect'.
        'response' => 'auto',
        'status' => 423,
    ],

    /*
    |--------------------------------------------------------------------------
    | Named feature gating
    |--------------------------------------------------------------------------
    | feature => bool (allowed in demo). An entitlement-linked feature is also
    | allowed when the license is entitled to it.
    */
    'features' => [
        // 'export' => false,
        // 'settings' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Protected console commands (blocked while demo is active)
    |--------------------------------------------------------------------------
    */
    'console' => [
        'guard' => (bool) $env('GUARD_CONSOLE', false),
        'protected' => [
            // 'migrate:fresh', 'db:wipe',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Side-effect guards (only applied while demo is active for the request)
    |--------------------------------------------------------------------------
    */
    'side_effects' => [
        'mail' => (bool) $env('GUARD_MAIL', true),          // swap mailer to "log"
        'notifications' => (bool) $env('GUARD_NOTIFICATIONS', false),
        'broadcasting' => (bool) $env('GUARD_BROADCASTING', false),
        'http' => (bool) $env('GUARD_HTTP', false),         // block outgoing HTTP
        'http_allow' => [],                                  // host allowlist when http guard is on
    ],

    /*
    |--------------------------------------------------------------------------
    | Reset / restore
    |--------------------------------------------------------------------------
    */
    'reset' => [
        'enabled' => (bool) $env('RESET_ENABLED', false),
        'strategy' => $env('RESET_STRATEGY', 'migrate-fresh-seed'),

        // Never run a destructive reset outside these environments without --force.
        'allowed_environments' => ['local', 'demo', 'staging'],
        'allow_production' => (bool) $env('RESET_ALLOW_PRODUCTION', false),

        // What to reset (each toggleable).
        'scope' => [
            'database' => true,
            'files' => false,
            'cache' => true,
            'sessions' => true,
            'queue' => false,
            'logs' => false,
        ],

        // migrate-fresh-seed strategy.
        'seeder' => null,                 // e.g. Database\Seeders\DemoSeeder::class
        'migrate_fresh_options' => [],

        // snapshot / sql-dump / backup-restore strategy inputs.
        'snapshot_name' => 'demo-baseline',
        'sql_dump_path' => null,
        'callback' => null,               // callable for the `callback` strategy

        // File reset: disks reset from a baseline directory and/or purged.
        'files' => [
            'baseline_path' => null,
            'disks' => [],
        ],

        // Scheduling (requires the host scheduler/cron).
        'schedule' => null,               // e.g. 'hourly' or a CRON expression
        'min_interval' => 300,            // seconds between resets
        'queue' => false,                 // dispatch the reset as a queued job
        'maintenance' => false,           // php artisan down during the reset

        // On-demand reset endpoint.
        'on_demand' => [
            'enabled' => (bool) $env('RESET_ON_DEMAND', false),
            'gate' => null,               // null = anyone (rate-limited); or a Gate ability
            'throttle' => '3,1',          // max,minutes
        ],

        // Re-seed demo accounts after a reset.
        'reseed_accounts' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Per-visitor sandbox
    |--------------------------------------------------------------------------
    | shared (default) | transactional | scoped (row-level) | tenant (stancl/tenancy).
    */
    'sandbox' => [
        'strategy' => $env('SANDBOX', 'shared'),
        'ttl' => 3600,                    // seconds for scoped/tenant sessions
        'cookie' => 'demo_sandbox',
        'models' => [],                   // models using BelongsToDemoSandbox for `scoped`
    ],

    /*
    |--------------------------------------------------------------------------
    | Demo accounts + auto-login
    |--------------------------------------------------------------------------
    */
    'accounts' => [
        'auto_login' => (bool) $env('AUTO_LOGIN', false),
        'guard' => null,
        'default' => null,                // identifier (email/id) to log in as
        'roles' => [
            // 'admin' => 'demo-admin@example.com',
            // 'user'  => 'demo-user@example.com',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Banner / affordances
    |--------------------------------------------------------------------------
    */
    'banner' => [
        'enabled' => (bool) $env('BANNER', true),
        'view' => 'demo-mode::banner',
        'position' => 'top',              // top | bottom
        'dismissible' => true,
        'countdown' => true,              // show time until next reset
        'message' => null,                // null = use translation
        'cta' => [
            'label' => null,
            'url' => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Observability
    |--------------------------------------------------------------------------
    */
    'logging' => [
        'blocked' => (bool) $env('LOG_BLOCKED', false),   // record blocked attempts
        'channel' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Events
    |--------------------------------------------------------------------------
    */
    'events' => [
        'enabled' => (bool) $env('EVENTS_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Middleware groups the package should auto-attach its banner/guards to.
    |--------------------------------------------------------------------------
    */
    'middleware_groups' => [],

    /*
    |--------------------------------------------------------------------------
    | Route registration (the reset endpoint)
    |--------------------------------------------------------------------------
    */
    'routes' => [
        'enabled' => true,
        'prefix' => 'demo',
        'middleware' => ['web'],
    ],
];
