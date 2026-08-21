@php
    $src = $src ?? '';
    $alt = $alt ?? 'media';
    $size = $size ?? 'md'; // sm|md|lg
@endphp
@if ($src)
    <a class="ad-media-thumb is-{{ $size }}" href="{{ $src }}" target="_blank" rel="noopener" title="Ver imagen">
        <img src="{{ $src }}" alt="{{ $alt }}" loading="lazy">
    </a>
@else
    <span class="ad-media-thumb is-{{ $size }} is-empty" title="Sin imagen">
        @include('partials.admin-icon', ['icon' => $icon ?? 'tienda'])
    </span>
@endif
