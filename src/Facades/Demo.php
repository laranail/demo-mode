<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Facades;

use Closure;
use Illuminate\Support\Facades\Facade;
use Simtabi\Laranail\Demo\Mode\Contracts\LicenseGateway;
use Simtabi\Laranail\Demo\Mode\DemoMode;

/**
 * @method static bool isActive()
 * @method static bool isReadOnly()
 * @method static string reason()
 * @method static DemoMode enable()
 * @method static DemoMode disable()
 * @method static DemoMode clearOverride()
 * @method static bool bypasses()
 * @method static bool guardsSuspended()
 * @method static mixed withoutGuards(Closure $callback)
 * @method static bool allows(string $feature)
 * @method static bool denies(string $feature)
 * @method static DemoMode protect(string ...$features)
 * @method static DemoMode permit(string ...$features)
 * @method static void guard(string $feature)
 * @method static void reset(?string $strategy = null)
 * @method static void snapshot(?string $name = null)
 * @method static LicenseGateway license()
 * @method static DemoMode configure(array $overrides)
 * @method static DemoMode flushState()
 *
 * @see DemoMode
 */
final class Demo extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return DemoMode::class;
    }
}
