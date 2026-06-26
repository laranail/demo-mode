<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Support;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Builds the response for a blocked action — JSON for API requests, a
 * redirect-back with a flash message for web requests (configurable).
 */
final class BlockResponder
{
    public function respond(Request $request, string $message): Response
    {
        $status = (int) config('demo-mode.write.status', 423);
        $mode = (string) config('demo-mode.write.response', 'auto');

        $wantsJson = $mode === 'json' || ($mode === 'auto' && $request->expectsJson());

        if ($wantsJson) {
            return response()->json([
                'message' => $message,
                'demo' => true,
            ], $status);
        }

        return back()->with('demo_error', $message)->setStatusCode(302);
    }
}
