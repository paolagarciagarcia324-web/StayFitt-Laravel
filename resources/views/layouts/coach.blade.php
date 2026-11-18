<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Coach') | FigueFit</title>
    @include('partials.favicon')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ @filemtime(public_path('css/admin.css')) ?: time() }}">
    <link rel="stylesheet" href="{{ asset('css/admin-ds.css') }}?v={{ @filemtime(public_path('css/admin-ds.css')) ?: time() }}">
    <link rel="stylesheet" href="{{ asset('css/coach-ds.css') }}?v={{ @filemtime(public_path('css/coach-ds.css')) ?: time() }}">
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
<body class="ad-body is-coach">
    <input class="ad-nav-check" type="checkbox" id="ad-nav-toggle" checked>

    @php
        $navUser = $coachNavUser ?? auth()->user();
        $navCoach = $coachNavCoach ?? $navUser?->coach;
    @endphp

    <aside class="ad-sidebar">
        <div class="ad-brand">
            <div class="ad-brand-mark">
                <img src="{{ asset('images/admin/figuefit-logo-circle.png') }}" alt="FigueFit">
            </div>
            <div class="ad-brand-copy">
                <strong>FigueFit</strong>
                <small>Coach Pro</small>
            </div>
        </div>

        @if ($navUser)
            <div class="ch-side-user">
                <div class="ch-userchip">
                    <div class="ch-avatar">
                        @if ($navCoach?->foto_url)
                            <img src="{{ $navCoach->foto_url }}" alt="">
                        @elseif ($navUser->avatarUrl())
                            <img src="{{ $navUser->avatarUrl() }}" alt="">
                        @else
                            {{ $navUser->iniciales() }}
                        @endif
                    </div>
                    <div>
                        <strong>{{ $navUser->nombres }}</strong>
                        <small>{{ $navCoach?->especialidad ?? 'Entrenador' }} · {{ $coachNavClientes ?? 0 }} clientes</small>
                    </div>
                </div>
            </div>
        @endif

        <nav class="ad-nav">
            <a href="{{ route('coach.dashboard') }}" class="ad-nav-link {{ request()->routeIs('coach.dashboard') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-pink">@include('partials.admin-icon', ['icon' => 'dashboard'])</span>
                Dashboard
            </a>
            <a href="{{ route('coach.clientes.index') }}" class="ad-nav-link {{ request()->routeIs('coach.clientes.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-cyan">@include('partials.admin-icon', ['icon' => 'clientes'])</span>
                Mis clientes
            </a>
            <a href="{{ route('coach.entrenamiento.index') }}" class="ad-nav-link {{ request()->routeIs('coach.entrenamiento.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-pink">@include('partials.admin-icon', ['icon' => 'actividad'])</span>
                Entrenamiento
            </a>
            <a href="{{ route('coach.videos.index') }}" class="ad-nav-link {{ request()->routeIs('coach.videos.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-cyan">@include('partials.admin-icon', ['icon' => 'comunidad'])</span>
                Biblioteca video
            </a>
            <a href="{{ route('coach.agenda.index') }}" class="ad-nav-link {{ request()->routeIs('coach.agenda.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-pink">@include('partials.admin-icon', ['icon' => 'horarios'])</span>
                Agenda
            </a>
            <a href="{{ route('coach.reportes.progreso') }}" class="ad-nav-link {{ request()->routeIs('coach.reportes.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-cyan">@include('partials.admin-icon', ['icon' => 'estadisticas'])</span>
                Progreso
            </a>
            <a href="{{ route('coach.comunicacion.index') }}" class="ad-nav-link {{ request()->routeIs('coach.comunicacion.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-pink">@include('partials.admin-icon', ['icon' => 'comunidad'])</span>
                Comunicación
                @if (($coachNavMensajes ?? 0) > 0)
                    <em class="ch-nav-badge">{{ $coachNavMensajes }}</em>
                @endif
            </a>
            <a href="{{ route('coach.propuestas.index') }}" class="ad-nav-link {{ request()->routeIs('coach.propuestas.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-cyan">@include('partials.admin-icon', ['icon' => 'planes'])</span>
                Propuestas
            </a>
            <a href="{{ route('coach.perfil.show') }}" class="ad-nav-link {{ request()->routeIs('coach.perfil.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-pink">@include('partials.admin-icon', ['icon' => 'personas'])</span>
                Mi perfil
            </a>
            @if (auth()->user()?->tieneRol('ASISTENTE_TECNICO') || auth()->user()?->tieneRol('ADMIN'))
                <a href="{{ route('staff.dashboard') }}" class="ad-nav-link">
                    <span class="ad-nav-orb is-pink">@include('partials.admin-icon', ['icon' => 'personas'])</span>
                    Panel staff
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
            <div class="ad-header-actions">
                @include('partials.admin-header-tools')
            </div>
        </header>

        <div class="ad-main">
            @if (session('success'))
                <div class="ad-alert">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="ad-alert ad-alert-error">{{ session('error') }}</div>
            @endif
            @if (isset($errors) && $errors->any())
                <div class="ad-alert ad-alert-error">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <main class="ad-content">
                @yield('content')
            </main>
        </div>
    </div>
    <script>
        (function () {
            var root = document.documentElement;
            var moon = document.querySelector('[data-theme-icon="moon"]');
            var sun = document.querySelector('[data-theme-icon="sun"]');
            function sync() {
                var light = root.classList.contains('ad-theme-light');
                if (moon) moon.hidden = light;
                if (sun) sun.hidden = !light;
            }
            document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    root.classList.toggle('ad-theme-light');
                    try {
                        localStorage.setItem('ff-admin-theme', root.classList.contains('ad-theme-light') ? 'light' : 'dark');
                    } catch (e) {}
                    sync();
                });
            });
            sync();
        })();
    </script>
    @stack('scripts')
</body>
</html>
