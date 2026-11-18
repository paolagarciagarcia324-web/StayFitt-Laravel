@extends('layouts.admin')

@section('title', 'Roles')
@section('subtitle', 'Quién puede entrar a cada panel. La asignación se hace en Usuarios.')

@section('content')
    <div class="ad-role-grid">
        @foreach ($roles as $rol)
            @php
                $n = (int) $rol->usuarios_count;
                $pct = min(100, $n * 12);
                $tone = str_contains(strtoupper((string) $rol->codigoDisplay()), 'ADMIN') ? 'var(--pink)' : 'var(--cyan)';
            @endphp
            <article class="ad-role-card">
                <span class="ad-chip {{ ($rol->estado ?? 'ACTIVO') === 'ACTIVO' ? 'is-ok' : 'is-off' }}">{{ $rol->estado ?? 'ACTIVO' }}</span>
                @include('partials.admin-ring', [
                    'pct' => $pct,
                    'count' => $n,
                    'sub' => 'personas',
                    'gid' => 'rol-'.$rol->id_rol,
                    'size' => 120,
                ])
                <h3>{{ $rol->nombreDisplay() }}</h3>
                <p class="ad-user-mail">{{ $rol->codigoDisplay() }}</p>
            </article>
        @endforeach
    </div>
@endsection
