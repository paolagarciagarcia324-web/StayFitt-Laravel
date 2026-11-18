<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Asistente') | FigueFit</title>
    @include('partials.favicon')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ @filemtime(public_path('css/admin.css')) ?: time() }}">
    <link rel="stylesheet" href="{{ asset('css/admin-ds.css') }}?v={{ @filemtime(public_path('css/admin-ds.css')) ?: time() }}">
    <link rel="stylesheet" href="{{ asset('css/staff-ds.css') }}?v={{ @filemtime(public_path('css/staff-ds.css')) ?: time() }}">
    <script>
        (function () {
            try {
                if (localStorage.getItem('ff-admin-theme') === 'light') {
                    document.documentElement.classList.add('ad-theme-light');
                }
            } catch (e) {}
        })();
    </script>
</head>
<body class="ad-body is-staff">
    <input class="ad-nav-check" type="checkbox" id="ad-nav-toggle" checked>

    @php
        $navUser = $staffNavUser ?? auth()->user();
        $navCoach = $staffNavCoach ?? null;
    @endphp

    <aside class="ad-sidebar">
        <div class="ad-brand">
            <div class="ad-brand-mark">
                <img src="{{ asset('images/admin/figuefit-logo-circle.png') }}" alt="FigueFit">
            </div>
            <div class="ad-brand-copy">
                <strong>FigueFit</strong>
                <small>Ops Staff</small>
            </div>
        </div>

        @if ($navUser)
            <div class="st-side-user">
                <div class="st-userchip">
                    <div class="st-avatar">
                        @if ($navUser->avatarUrl())
                            <img src="{{ $navUser->avatarUrl() }}" alt="">
                        @else
                            {{ $navUser->iniciales() }}
                        @endif
                    </div>
                    <div>
                        <strong>{{ $navUser->nombres }}</strong>
                        <small>{{ $staffNavEspecialidad ?? 'Apoyo operativo' }}</small>
                    </div>
                </div>
            </div>
        @endif

        @if (($staffNavCoaches ?? collect())->isNotEmpty())
            <form class="st-coach-select" method="POST" action="{{ route('staff.coach-context.update') }}">
                @csrf
                <label>Coach de apoyo</label>
                <select name="id_coach" onchange="this.form.submit()">
                    @foreach ($staffNavCoaches as $c)
                        <option value="{{ $c->id_coach }}" @selected($navCoach && (int) $navCoach->id_coach === (int) $c->id_coach)>
                            {{ trim($c->usuario?->nombres.' '.$c->usuario?->apellidos) ?: 'Coach #'.$c->id_coach }}
                        </option>
                    @endforeach
                </select>
            </form>
        @endif

        <nav class="ad-nav">
            <a href="{{ route('staff.dashboard') }}" class="ad-nav-link {{ request()->routeIs('staff.dashboard') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-pink">@include('partials.admin-icon', ['icon' => 'dashboard'])</span>
                Dashboard
            </a>
            <a href="{{ route('staff.clientes.index') }}" class="ad-nav-link {{ request()->routeIs('staff.clientes.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-cyan">@include('partials.admin-icon', ['icon' => 'clientes'])</span>
                Clientes apoyo
            </a>
            <a href="{{ route('staff.rutinas.index') }}" class="ad-nav-link {{ request()->routeIs('staff.rutinas.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-pink">@include('partials.admin-icon', ['icon' => 'actividad'])</span>
                Rutinas
            </a>
            <a href="{{ route('staff.propuestas.index') }}" class="ad-nav-link {{ request()->routeIs('staff.propuestas.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-cyan">@include('partials.admin-icon', ['icon' => 'planes'])</span>
                Propuestas
            </a>
            <a href="{{ route('staff.videos.index') }}" class="ad-nav-link {{ request()->routeIs('staff.videos.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-pink">@include('partials.admin-icon', ['icon' => 'comunidad'])</span>
                Biblioteca video
            </a>
            <a href="{{ route('staff.comunicacion.index') }}" class="ad-nav-link {{ request()->routeIs('staff.comunicacion.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-cyan">@include('partials.admin-icon', ['icon' => 'comunidad'])</span>
                Comunicación
            </a>
            <a href="{{ route('staff.novedades.index') }}" class="ad-nav-link {{ request()->routeIs('staff.novedades.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-pink">@include('partials.admin-icon', ['icon' => 'eventos'])</span>
                Novedades
            </a>
            <a href="{{ route('staff.perfil.show') }}" class="ad-nav-link {{ request()->routeIs('staff.perfil.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-cyan">@include('partials.admin-icon', ['icon' => 'personas'])</span>
                Mi perfil
            </a>
            <a href="{{ route('coach.propuestas.create') }}" class="ad-nav-link">
                <span class="ad-nav-orb is-pink">+</span>
                Nueva propuesta
            </a>
            @if (auth()->user()?->tieneRol('ADMIN'))
                <a href="{{ route('admin.dashboard') }}" class="ad-nav-link">
                    <span class="ad-nav-orb is-cyan">@include('partials.admin-icon', ['icon' => 'dashboard'])</span>
                    Admin completo
                </a>
            @endif
        </nav>

        <div class="ad-sidebar-foot">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Cerrar sesión</button>
            </form>
        </div>
    </aside>

    <div class="ad-wrap">
        <header class="ad-header">
            <label class="ad-nav-burger" for="ad-nav-toggle" aria-label="Menú">
                <span></span><span></span><span></span>
            </label>
            <div class="ad-header-actions">@include('partials.admin-header-tools')</div>
        </header>
        <div class="ad-main">
            @if (session('success'))<div class="ad-alert">{{ session('success') }}</div>@endif
            @if (session('error'))<div class="ad-alert ad-alert-error">{{ session('error') }}</div>@endif
            @if (isset($errors) && $errors->any())
                <div class="ad-alert ad-alert-error"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            <main class="ad-content">@yield('content')</main>
        </div>
    </div>
    <script>
        (function () {
            var root = document.documentElement;
            document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    root.classList.toggle('ad-theme-light');
                    try { localStorage.setItem('ff-admin-theme', root.classList.contains('ad-theme-light') ? 'light' : 'dark'); } catch (e) {}
                });
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>
