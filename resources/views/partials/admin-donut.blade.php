@php
    $segments = array_values($segments ?? []);
    $size = (int) ($size ?? 150);
    $gid = $gid ?? 'donut';
    $cx = 54;
    $cy = 54;
    $r = 40;
    $total = 0.0;
    foreach ($segments as $seg) {
        $total += max(0, (float) ($seg['pct'] ?? 0));
    }
    if ($total <= 0) {
        $total = 1;
    }
    $angle = -90.0; // start top
    $paths = [];
    foreach ($segments as $i => $seg) {
        $pct = max(0, (float) ($seg['pct'] ?? 0));
        $sweep = ($pct / $total) * 360.0;
        if ($sweep <= 0) {
            continue;
        }
        $start = $angle;
        $end = $angle + $sweep;
        $large = $sweep > 180 ? 1 : 0;
        $a1 = deg2rad($start);
        $a2 = deg2rad($end);
        $x1 = $cx + $r * cos($a1);
        $y1 = $cy + $r * sin($a1);
        $x2 = $cx + $r * cos($a2);
        $y2 = $cy + $r * sin($a2);
        // Donut via thick stroke arc path (open arc)
        $d = sprintf('M %.3f %.3f A %.3f %.3f 0 %d 1 %.3f %.3f', $x1, $y1, $r, $r, $large, $x2, $y2);
        $paths[] = [
            'd' => $d,
            'color' => $seg['color'] ?? '#ff2d78',
            'tip' => $seg['tip'] ?? (($seg['label'] ?? 'Serie').': '.round($pct).'%'),
            'label' => $seg['label'] ?? '',
            'pct' => $pct,
            'delay' => 120 + $i * 140,
        ];
        $angle = $end;
    }
@endphp
<div class="ad-donut-wrap" style="--sz: {{ $size }}px" data-chart-tip-root>
    <svg class="ad-donut" viewBox="0 0 108 108" aria-hidden="true">
        <circle class="ad-donut-track" cx="54" cy="54" r="40"/>
        @foreach ($paths as $p)
            <path
                class="ad-donut-seg is-tip"
                d="{{ $p['d'] }}"
                stroke="{{ $p['color'] }}"
                data-tip="{{ $p['tip'] }}"
                data-tip-color="{{ $p['color'] }}"
                style="--delay: {{ $p['delay'] }}ms"
            />
        @endforeach
    </svg>
    <div class="ad-donut-label">
        <strong @if (isset($count)) data-count="{{ $count }}" data-prefix="{{ $prefix ?? '' }}" data-suffix="{{ $suffix ?? '' }}" @endif>{{ $label ?? '' }}</strong>
        @if (! empty($sub))
            <small>{{ $sub }}</small>
        @endif
    </div>
</div>
