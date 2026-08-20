@php
    $user = $user ?? null;
    $foto = $user?->foto_perfil_url ?: $user?->avatarUrl();
    $iniciales = $user?->iniciales() ?? 'FF';
    $size = $size ?? 'md';
@endphp
@if ($foto)
    <img class="ad-user-avatar {{ $size === 'sm' ? 'is-sm' : '' }}" src="{{ $foto }}" alt="{{ $user?->nombres }}">
@else
    <span class="ad-user-avatar is-initials {{ $size === 'sm' ? 'is-sm' : '' }}">{{ $iniciales }}</span>
@endif
