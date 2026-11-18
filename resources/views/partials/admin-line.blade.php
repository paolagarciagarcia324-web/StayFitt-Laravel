@php
    $vals = array_values($vals ?? [0]);
    $tips = array_values($tips ?? []);
    $labels = array_values($labels ?? []);
    $max = max(1, ...array_map('floatval', $vals ?: [1]));
    $n = max(1, count($vals) - 1);
    $pts = [];
    $area = ['0,100'];
    $dots = [];
    foreach ($vals as $i => $v) {
        $x = round(($i / $n) * 100, 2);
        $y = round(100 - (((float) $v / $max) * 86) - 6, 2);
        $pts[] = $x.','.$y;
        $area[] = $x.','.$y;
        $dots[] = ['x' => $x, 'y' => $y, 'v' => $v, 'i' => $i];
    }
    $area[] = '100,100';
    $gid = $gid ?? 'ln';
@endphp
<svg class="ad-line" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true" data-chart-tip-root>
    <defs>
        <linearGradient id="{{ $gid }}-fill" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#2ef5e6" stop-opacity="0.35"/>
            <stop offset="100%" stop-color="#ff2d78" stop-opacity="0"/>
        </linearGradient>
        <linearGradient id="{{ $gid }}-stroke" x1="0" y1="0" x2="1" y2="0">
            <stop offset="0%" stop-color="#ff2d78"/>
            <stop offset="100%" stop-color="#2ef5e6"/>
        </linearGradient>
    </defs>
    <polygon class="ad-line-fill" fill="url(#{{ $gid }}-fill)" points="{{ implode(' ', $area) }}"/>
    <polyline class="ad-line-stroke" points="{{ implode(' ', $pts) }}" stroke="url(#{{ $gid }}-stroke)"/>
    @foreach ($dots as $d)
        @php
            $label = $labels[$d['i']] ?? ('Día '.($d['i'] + 1));
            $tip = $tips[$d['i']] ?? ($label.': $'.number_format((float) $d['v'], 0, ',', '.'));
            $bandW = $n > 0 ? (100 / count($vals)) : 100;
            $bandX = max(0, $d['x'] - ($bandW / 2));
        @endphp
        <rect
            class="ad-line-hit is-tip"
            x="{{ $bandX }}" y="0" width="{{ $bandW }}" height="100"
            data-tip="{{ $tip }}"
            data-tip-color="#2ef5e6"
        />
        <circle
            class="ad-line-dot is-tip"
            cx="{{ $d['x'] }}" cy="{{ $d['y'] }}" r="2.2"
            data-tip="{{ $tip }}"
            data-tip-color="#ff2d78"
        />
    @endforeach
</svg>
