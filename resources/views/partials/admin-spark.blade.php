@php
    $values = array_values($values ?? [2, 3, 2, 5, 4, 6, 7]);
    if (count($values) < 2) {
        $values = [2, 3, 2, 5, 4, 6, 7];
    }
    $color = $color ?? '#ff2d78';
    $w = 88;
    $h = 40;
    $max = max($values);
    $min = min($values);
    $range = max(1, $max - $min);
    $last = count($values) - 1;
    $points = [];
    foreach ($values as $i => $v) {
        $x = ($i / $last) * $w;
        $y = $h - (($v - $min) / $range) * ($h - 8) - 4;
        $points[] = round($x, 1).','.round($y, 1);
    }
    $d = 'M'.implode(' L', $points);
@endphp
<svg class="ad-spark" viewBox="0 0 {{ $w }} {{ $h }}" preserveAspectRatio="none" aria-hidden="true">
    <path d="{{ $d }}" fill="none" stroke="{{ $color }}" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
