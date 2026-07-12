<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Http\Controllers;

use Illuminate\Routing\Controller;
use Simtabi\Laranail\Demo\Mode\Concerns\HandlesDemoMode;

/**
 * Optional base controller exposing the demo helpers from
 * {@see HandlesDemoMode}. Extend it, or simply `use` the trait in your own base.
 */
abstract class DemoAwareController extends Controller
{
    use HandlesDemoMode;
}
