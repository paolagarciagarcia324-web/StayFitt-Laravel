@php
    $pct = max(0, min(100, (float) ($pct ?? 0)));
    $size = (int) ($size ?? 148);
    $r = 40;
    $c = round(2 * M_PI * $r, 2);
    $off = round($c * (1 - $pct / 100), 2);
    $gid = $gid ?? ('adg-'.substr(md5(($label ?? '').(string) $pct.($sub ?? '')), 0, 8));
    $tip = $tip ?? (($label ?? ((int) $pct).'%').(!empty($sub) ? ' · '.$sub : ''));
@endphp
<div class="ad-svg-ring-wrap" style="--sz: {{ $size }}px" data-chart-tip-root>
    <svg class="ad-svg-ring" viewBox="0 0 108 108" aria-hidden="true">
        <defs>
            <linearGradient id="{{ $gid }}" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#ff2d78"/>
                <stop offset="100%" stop-color="#2ef5e6"/>
            </linearGradient>
        </defs>
        <circle class="ad-svg-track" cx="54" cy="54" r="{{ $r }}"/>
        <circle
            class="ad-svg-val is-tip"
            cx="54" cy="54" r="{{ $r }}"
            stroke="url(#{{ $gid }})"
            data-tip="{{ $tip }}"
            data-tip-color="#ff2d78"
            style="--ring: {{ $c }}; --off: {{ $off }}"
        />
    </svg>
    <div class="ad-svg-ring-label">
        <strong @if (isset($count)) data-count="{{ $count }}" data-prefix="{{ $prefix ?? '' }}" data-suffix="{{ $suffix ?? '' }}" @endif>{{ $label ?? ((int) $pct).'%' }}</strong>
        @if (! empty($sub))
            <small>{{ $sub }}</small>
        @endif
    </div>
</div>
