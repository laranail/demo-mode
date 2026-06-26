<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Simtabi\Laranail\Demo\Mode\DemoMode;
use Simtabi\Laranail\Demo\Mode\Support\DemoBanner;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

/**
 * Injects the demo banner into HTML responses. Alias: `demo.banner`.
 *
 * Only touches non-streamed, non-redirect HTML responses and recomputes
 * Content-Length so it stays correct.
 */
final readonly class InjectDemoBanner
{
    public function __construct(
        private DemoMode $demo,
        private DemoBanner $banner,
    ) {}

    public function handle(Request $request, Closure $next): BaseResponse
    {
        $response = $next($request);

        if (! $this->shouldInject($response)) {
            return $response;
        }

        $content = (string) $response->getContent();
        $html = view((string) config('demo-mode.banner.view', 'demo-mode::banner'), [
            'banner' => $this->banner->data(),
        ])->render();

        $content = str_contains($content, '</body>')
            ? str_replace('</body>', $html.'</body>', $content)
            : $content.$html;

        $response->setContent($content);
        $response->headers->remove('Content-Length');

        return $response;
    }

    private function shouldInject(BaseResponse $response): bool
    {
        if (! $this->demo->isActive() || ! (bool) config('demo-mode.banner.enabled', true)) {
            return false;
        }

        if (! $response instanceof Response || $response->isRedirection()) {
            return false;
        }

        $type = (string) $response->headers->get('Content-Type', 'text/html');

        return str_contains($type, 'text/html');
    }
}
