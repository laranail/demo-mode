@php
    /** @var array<string, mixed> $banner */
@endphp
<div
    id="demo-mode-banner"
    style="position:fixed;left:0;right:0;{{ ($banner['position'] ?? 'top') === 'bottom' ? 'bottom:0' : 'top:0' }};z-index:2147483647;
           background:#1f2937;color:#fff;font:14px/1.4 system-ui,sans-serif;padding:8px 16px;
           display:flex;align-items:center;gap:12px;justify-content:center;box-shadow:0 1px 4px rgba(0,0,0,.25)"
    role="status"
>
    <span>{{ $banner['message'] }}</span>

    @if (! is_null($banner['reset_in'] ?? null))
        <span style="opacity:.8">· {{ __('demo-mode::banner.reset_in', ['time' => gmdate('i:s', (int) $banner['reset_in'])]) }}</span>
    @endif

    @if (! empty($banner['cta']['label']) && ! empty($banner['cta']['url']))
        <a href="{{ $banner['cta']['url'] }}" style="color:#93c5fd;font-weight:600">{{ $banner['cta']['label'] }}</a>
    @endif

    @if ($banner['dismissible'] ?? true)
        <button type="button" onclick="document.getElementById('demo-mode-banner').remove()"
                style="margin-left:8px;background:transparent;border:0;color:#fff;cursor:pointer;font-size:16px"
                aria-label="{{ __('demo-mode::banner.dismiss') }}">×</button>
    @endif
</div>
