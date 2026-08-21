@php
    $rating = max(0, min(5, (float) ($rating ?? 0)));
    $full = (int) floor($rating);
    $half = ($rating - $full) >= 0.5;
@endphp
<span class="ad-ml-stars" title="{{ number_format($rating, 1) }} de 5" aria-label="{{ number_format($rating, 1) }} estrellas">
    @for ($i = 1; $i <= 5; $i++)
        @if ($i <= $full)
            <i class="is-on">★</i>
        @elseif ($half && $i === $full + 1)
            <i class="is-half">★</i>
        @else
            <i>★</i>
        @endif
    @endfor
    <em>{{ number_format($rating, 1) }}</em>
</span>
