@php
    $user = $user ?? null;
    $foto = $user?->avatarUrl();
    $iniciales = $user?->iniciales() ?? 'FF';
    $size = $size ?? 'md';
    $sizeClass = $size === 'sm' ? 'is-sm' : '';
@endphp
@if ($foto)
    <img
        class="ad-user-avatar {{ $sizeClass }}"
        src="{{ $foto }}"
        alt="{{ $user?->nombres }}"
        onerror="this.outerHTML='<span class=&quot;ad-user-avatar is-initials {{ $sizeClass }}&quot;>{{ e($iniciales) }}</span>'"
    >
@else
    <span class="ad-user-avatar is-initials {{ $sizeClass }}">{{ $iniciales }}</span>
@endif
