<?php

declare(strict_types=1);

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Route;
use Simtabi\Laranail\Package\Tools\Testing\NamingScope;
use Simtabi\Laranail\Package\Tools\Testing\NameRegistry;
use Simtabi\Laranail\Package\Tools\Testing\AssertsRegisteredNames;
use Simtabi\Laranail\Package\Tools\Support\Routing\BareRouteNameAliases;

uses(AssertsRegisteredNames::class);

/**
 * Route names and view namespaces, read from the live registries.
 *
 * Ownership is judged from `src/` and `resources/`: run from this repository, the package root
 * also holds `vendor/`, and Laravel's own view namespaces would otherwise count as this package's
 * (package-tools v0.1.3 defaults the base path to the root).
 */
function demoModeScope(string $basePath): NamingScope
{
    return NamingScope::for(
        'laranail/demo-mode',
        'Simtabi\\Laranail\\Demo\\Mode\\',
        basePath: dirname(__DIR__, 2) . '/' . $basePath,
        prefixes: [NameRegistry::Route->value => ['laranail-demo-mode']],
    );
}

beforeEach(fn () => BareRouteNameAliases::forgetWarnings());

it('names its reset route laranail-demo-mode.reset', function (): void {
    expect($this->assertRouteNamesScoped(demoModeScope('src'), atLeast: 1))
        ->toContain('laranail-demo-mode.reset');

    expect(Route::has('demo-mode.reset'))->toBeFalse();
});

it('still generates a URL for the deprecated demo-mode.reset name', function (): void {
    $this->assertDeprecatedRouteNamesResolve(['demo-mode.reset' => 'laranail-demo-mode.reset']);
});

it('announces the deprecated route name when it is used', function (): void {
    $notices = [];
    set_error_handler(static function (int $level, string $message) use (&$notices): bool {
        $notices[] = $message;

        return true;
    }, E_USER_DEPRECATED);

    try {
        $url = route('demo-mode.reset');
    } finally {
        restore_error_handler();
    }

    expect($url)->toBe(route('laranail-demo-mode.reset'))
        ->and(implode("\n", $notices))->toContain('demo-mode.reset')
        ->toContain('laranail-demo-mode.reset');
});

it('registers its views under both laranail/demo-mode and laranail-demo-mode', function (): void {
    expect($this->assertViewNamespacesScoped(demoModeScope('resources'), atLeast: 2))
        ->toContain('laranail/demo-mode', 'laranail-demo-mode');

    $hints = View::getFinder()->getHints();

    expect($hints['laranail/demo-mode'])->toBe($hints['laranail-demo-mode']);
});
