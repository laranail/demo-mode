<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Simtabi\Laranail\Demo\Mode\DemoMode;
use Simtabi\Laranail\Demo\Mode\Exceptions\DemoModeException;
use Symfony\Component\HttpFoundation\Response;

/**
 * The on-demand "Reset demo" endpoint (POST {prefix}/reset).
 */
final class DemoController extends Controller
{
    public function reset(Request $request, DemoMode $demo): Response
    {
        if (! (bool) config('demo-mode.reset.on_demand.enabled', false)) {
            abort(404);
        }

        $gate = config('demo-mode.reset.on_demand.gate');

        if (is_string($gate) && $gate !== '' && Gate::denies($gate)) {
            abort(403);
        }

        try {
            $demo->reset();
        } catch (DemoModeException $e) {
            return $request->expectsJson()
                ? response()->json(['message' => $e->getMessage()], 422)
                : back()->with('demo_error', $e->getMessage());
        }

        return $request->expectsJson()
            ? response()->json(['message' => __('demo-mode::reset.complete')])
            : back()->with('demo_status', __('demo-mode::reset.complete'));
    }
}
