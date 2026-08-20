<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') | FigueFit</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
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
<body class="ad-body">
    @php
        $adminUser = auth()->user();
        $adminAvatar = $adminUser?->avatarUrl();
    @endphp
    <input class="ad-nav-check" type="checkbox" id="ad-nav-toggle">

    <aside class="ad-sidebar">
        <div class="ad-brand">
            <div class="ad-brand-avatar">
                @if ($adminAvatar)
                    <img src="{{ $adminAvatar }}" alt="{{ $adminUser?->nombres }}" onerror="this.style.display='none'; this.nextElementSibling.hidden=false;">
                    <span class="ad-user-icon" hidden>
                        @include('partials.admin-icon', ['icon' => 'clientes'])
                    </span>
                @else
                    <span class="ad-user-icon">
                        @include('partials.admin-icon', ['icon' => 'clientes'])
                    </span>
                @endif
            </div>
            <span>
                <small>FigueFit</small>
                <strong>Admin</strong>
            </span>
        </div>

        <nav class="ad-nav">
            <span class="ad-nav-label">Operación</span>
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-pink">@include('partials.admin-icon', ['icon' => 'dashboard'])</span>
                Dashboard
            </a>
            <a href="{{ route('admin.solicitudes.index') }}" class="{{ request()->routeIs('admin.solicitudes.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-cyan">@include('partials.admin-icon', ['icon' => 'pagos'])</span>
                Solicitudes
                @if (($navSolicitudesPendientes ?? 0) > 0)
                    <span class="ad-nav-badge">{{ $navSolicitudesPendientes }}</span>
                @endif
            </a>
            <a href="{{ route('admin.pagos.index') }}" class="{{ request()->routeIs('admin.pagos.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-pink">@include('partials.admin-icon', ['icon' => 'pagos'])</span>
                Pagos
            </a>
            <a href="{{ route('admin.horarios.index') }}" class="{{ request()->routeIs('admin.horarios.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-cyan">@include('partials.admin-icon', ['icon' => 'horarios'])</span>
                Horarios
            </a>
            <a href="{{ route('admin.eventos.index') }}" class="{{ request()->routeIs('admin.eventos.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-pink">@include('partials.admin-icon', ['icon' => 'eventos'])</span>
                Eventos
            </a>
            <a href="{{ route('admin.calendario.index') }}" class="{{ request()->routeIs('admin.calendario.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-cyan">@include('partials.admin-icon', ['icon' => 'calendario'])</span>
                Calendario
            </a>

            <span class="ad-nav-label">Personas</span>
            <a href="{{ route('admin.usuarios.index') }}" class="{{ request()->routeIs('admin.usuarios.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-pink">@include('partials.admin-icon', ['icon' => 'usuarios'])</span>
                Usuarios
            </a>
            <a href="{{ route('admin.clientes.virtuales') }}" class="{{ request()->routeIs('admin.clientes.virtuales') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-cyan">@include('partials.admin-icon', ['icon' => 'clientes-virtual'])</span>
                Clientes virtuales
            </a>
            <a href="{{ route('admin.clientes.presenciales') }}" class="{{ request()->routeIs('admin.clientes.presenciales') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-pink">@include('partials.admin-icon', ['icon' => 'clientes-presencial'])</span>
                Clientes presenciales
            </a>
            <a href="{{ route('admin.instituciones.index') }}" class="{{ request()->routeIs('admin.instituciones.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-cyan">@include('partials.admin-icon', ['icon' => 'instituciones'])</span>
                Instituciones
            </a>
            <a href="{{ route('admin.coaches.index') }}" class="{{ request()->routeIs('admin.coaches.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-pink">@include('partials.admin-icon', ['icon' => 'coaches'])</span>
                Coaches
            </a>

            <span class="ad-nav-label">Comercial</span>
            <a href="{{ route('admin.planes.index') }}" class="{{ request()->routeIs('admin.planes.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-cyan">@include('partials.admin-icon', ['icon' => 'planes'])</span>
                Planes
            </a>
            <a href="{{ route('admin.cupones.index') }}" class="{{ request()->routeIs('admin.cupones.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-pink">@include('partials.admin-icon', ['icon' => 'planes'])</span>
                Cupones
            </a>
            <a href="{{ route('admin.tienda.index') }}" class="{{ request()->routeIs('admin.tienda.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-cyan">@include('partials.admin-icon', ['icon' => 'tienda'])</span>
                Tienda
            </a>
            <a href="{{ route('admin.pasarelas.index') }}" class="{{ request()->routeIs('admin.pasarelas.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-pink">@include('partials.admin-icon', ['icon' => 'pasarelas'])</span>
                Pasarelas
            </a>

            <span class="ad-nav-label">Comunicación</span>
            <a href="{{ route('admin.whatsapp.index') }}" class="{{ request()->routeIs('admin.whatsapp.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-cyan">@include('partials.admin-icon', ['icon' => 'comunidad'])</span>
                WhatsApp
            </a>
            <a href="{{ route('admin.comunidad.index') }}" class="{{ request()->routeIs('admin.comunidad.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-pink">@include('partials.admin-icon', ['icon' => 'comunidad'])</span>
                Comunidad
            </a>

            <span class="ad-nav-label">Análisis</span>
            <a href="{{ route('admin.estadisticas.index') }}" class="{{ request()->routeIs('admin.estadisticas.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-cyan">@include('partials.admin-icon', ['icon' => 'estadisticas'])</span>
                Estadísticas
            </a>
            <a href="{{ route('admin.reportes.index') }}" class="{{ request()->routeIs('admin.reportes.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-pink">@include('partials.admin-icon', ['icon' => 'reportes'])</span>
                Reportes
            </a>
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
            <label class="ad-nav-burger" for="ad-nav-toggle" aria-label="Menú">☰</label>
            <div class="ad-header-actions">
                @include('partials.admin-header-tools')
            </div>
        </header>

        <div class="ad-main">
            <header class="ad-top">
                <div>
                    <h1>@yield('title', 'Dashboard')</h1>
                    @hasSection('subtitle')
                        <p>@yield('subtitle')</p>
                    @endif
                </div>
                <div class="ad-top-actions">
                    @yield('actions')
                </div>
            </header>

            @if (session('success'))
                <div class="ad-alert">{{ session('success') }}</div>
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
            var tools = document.querySelector('[data-tools]');
            var moon = document.querySelector('[data-theme-icon="moon"]');
            var sun = document.querySelector('[data-theme-icon="sun"]');

            function syncThemeIcons() {
                var light = root.classList.contains('ad-theme-light');
                if (moon) moon.hidden = light;
                if (sun) sun.hidden = !light;
            }

            syncThemeIcons();

            document.querySelector('[data-theme-toggle]')?.addEventListener('click', function () {
                var light = root.classList.toggle('ad-theme-light');
                try { localStorage.setItem('ff-admin-theme', light ? 'light' : 'dark'); } catch (e) {}
                syncThemeIcons();
            });

            tools?.addEventListener('click', function (event) {
                var btn = event.target.closest('[data-panel]');
                if (!btn) return;
                var id = btn.getAttribute('data-panel');
                tools.querySelectorAll('[data-box]').forEach(function (box) {
                    var open = box.getAttribute('data-box') === id && box.hasAttribute('hidden');
                    box.toggleAttribute('hidden', !open);
                });
            });

            document.addEventListener('click', function (event) {
                if (!tools || tools.contains(event.target)) return;
                tools.querySelectorAll('[data-box]').forEach(function (box) {
                    box.setAttribute('hidden', '');
                });
            });
        })();
    </script>
</body>
</html>
