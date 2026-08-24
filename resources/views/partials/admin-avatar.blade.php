@php
    $user = $user ?? null;
    $foto = $user?->avatarUrl();
    $iniciales = $user?->iniciales() ?? 'FF';
    $size = $size ?? 'md';
    $sizeClass = $size === 'sm' ? 'is-sm' : '';
    $zoomable = (bool) ($zoomable ?? false);
    $nombre = trim(($user?->nombres ?? '').' '.($user?->apellidos ?? '')) ?: 'Perfil';
@endphp
@if ($foto)
    @if ($zoomable)
        <button
            type="button"
            class="ad-avatar-zoom-trigger"
            data-avatar-zoom
            data-avatar-src="{{ $foto }}"
            data-avatar-alt="{{ $nombre }}"
            title="Ampliar foto"
            aria-label="Ampliar foto de {{ $nombre }}"
        >
            <img
                class="ad-user-avatar {{ $sizeClass }}"
                src="{{ $foto }}"
                alt="{{ $nombre }}"
                onerror="this.closest('[data-avatar-zoom]').outerHTML='<span class=&quot;ad-user-avatar is-initials {{ $sizeClass }}&quot;>{{ e($iniciales) }}</span>'"
            >
        </button>
    @else
        <img
            class="ad-user-avatar {{ $sizeClass }}"
            src="{{ $foto }}"
            alt="{{ $nombre }}"
            onerror="this.outerHTML='<span class=&quot;ad-user-avatar is-initials {{ $sizeClass }}&quot;>{{ e($iniciales) }}</span>'"
        >
    @endif
@else
    <span class="ad-user-avatar is-initials {{ $sizeClass }}">{{ $iniciales }}</span>
@endif
