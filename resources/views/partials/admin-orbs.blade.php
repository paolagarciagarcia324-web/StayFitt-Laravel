@php
    $items = array_values($items ?? []);
    $gid = $gid ?? 'orbs';
@endphp
<div class="ad-orbs" data-chart-tip-root>
    <svg class="ad-orbs-svg" viewBox="0 0 120 120" aria-hidden="true">
        @foreach ($items as $i => $item)
            @php
                $pct = max(0, min(100, (float) ($item['pct'] ?? 0)));
                $r = 48 - ($i * 13);
                $c = round(2 * M_PI * $r, 2);
                $off = round($c * (1 - $pct / 100), 2);
                $stroke = $item['color'] ?? ($i === 0 ? '#ff2d78' : ($i === 1 ? '#2ef5e6' : '#a78bfa'));
                $tip = $item['tip'] ?? (($item['label'] ?? '').': '.($item['value'] ?? $pct.'%'));
            @endphp
            <circle class="ad-orbs-track" cx="60" cy="60" r="{{ $r }}" />
            <circle
                class="ad-orbs-val is-tip"
                cx="60" cy="60" r="{{ $r }}"
                stroke="{{ $stroke }}"
                data-tip="{{ $tip }}"
                data-tip-color="{{ $stroke }}"
                style="--ring: {{ $c }}; --off: {{ $off }}; --delay: {{ 180 + $i * 160 }}ms"
            />
        @endforeach
    </svg>
    <ul class="ad-orbs-legend">
        @foreach ($items as $item)
            @php $stroke = $item['color'] ?? '#2ef5e6'; @endphp
            <li
                class="is-tip"
                data-tip="{{ $item['tip'] ?? (($item['label'] ?? '').': '.($item['value'] ?? '')) }}"
                data-tip-color="{{ $stroke }}"
            >
                <i style="background: {{ $stroke }}"></i>
                <span>{{ $item['label'] ?? '' }}</span>
                <strong>{{ $item['value'] ?? '' }}</strong>
            </li>
        @endforeach
    </ul>
</div>
