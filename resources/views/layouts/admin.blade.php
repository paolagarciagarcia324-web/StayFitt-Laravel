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

        <nav class="ad-nav" data-admin-nav>
            @php
                $pend = (int) ($navSolicitudesPendientes ?? 0);
                $opOpen = request()->routeIs('admin.solicitudes.*', 'admin.pagos.*', 'admin.horarios.*', 'admin.eventos.*', 'admin.calendario.*');
                $perOpen = request()->routeIs('admin.usuarios.*', 'admin.clientes.*', 'admin.instituciones.*', 'admin.coaches.*');
                $cliOpen = request()->routeIs('admin.clientes.*');
                $comOpen = request()->routeIs('admin.planes.*', 'admin.cupones.*', 'admin.tienda.*', 'admin.pasarelas.*');
                $comuOpen = request()->routeIs('admin.whatsapp.*', 'admin.comunidad.*');
                $anOpen = request()->routeIs('admin.estadisticas.*', 'admin.reportes.*');
            @endphp

            <a href="{{ route('admin.dashboard') }}" class="ad-nav-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-pink">@include('partials.admin-icon', ['icon' => 'dashboard'])</span>
                Dashboard
            </a>

            <details class="ad-nav-group {{ $opOpen ? 'is-route-active' : '' }}" data-nav-group="operacion" @if ($opOpen) open @endif>
                <summary>
                    <span class="ad-nav-orb is-cyan">@include('partials.admin-icon', ['icon' => 'pagos'])</span>
                    <span class="ad-nav-group-title">Operación</span>
                    @if ($pend > 0)
                        <span class="ad-nav-badge">{{ $pend }}</span>
                    @endif
                    <span class="ad-nav-chevron" aria-hidden="true"></span>
                </summary>
                <div class="ad-nav-children">
                    <a href="{{ route('admin.solicitudes.index') }}" class="ad-nav-link {{ request()->routeIs('admin.solicitudes.*') ? 'is-active' : '' }}">
                        <span class="ad-nav-dot"></span>
                        Solicitudes
                        @if ($pend > 0)
                            <span class="ad-nav-badge">{{ $pend }}</span>
                        @endif
                    </a>
                    <a href="{{ route('admin.pagos.index') }}" class="ad-nav-link {{ request()->routeIs('admin.pagos.*') ? 'is-active' : '' }}">
                        <span class="ad-nav-dot"></span>
                        Pagos
                    </a>
                    <a href="{{ route('admin.horarios.index') }}" class="ad-nav-link {{ request()->routeIs('admin.horarios.*') ? 'is-active' : '' }}">
                        <span class="ad-nav-dot"></span>
                        Horarios
                    </a>
                    <a href="{{ route('admin.eventos.index') }}" class="ad-nav-link {{ request()->routeIs('admin.eventos.*') ? 'is-active' : '' }}">
                        <span class="ad-nav-dot"></span>
                        Eventos
                    </a>
                    <a href="{{ route('admin.calendario.index') }}" class="ad-nav-link {{ request()->routeIs('admin.calendario.*') ? 'is-active' : '' }}">
                        <span class="ad-nav-dot"></span>
                        Calendario
                    </a>
                </div>
            </details>

            <details class="ad-nav-group {{ $perOpen ? 'is-route-active' : '' }}" data-nav-group="personas" @if ($perOpen) open @endif>
                <summary>
                    <span class="ad-nav-orb is-pink">@include('partials.admin-icon', ['icon' => 'usuarios'])</span>
                    <span class="ad-nav-group-title">Personas</span>
                    <span class="ad-nav-chevron" aria-hidden="true"></span>
                </summary>
                <div class="ad-nav-children">
                    <a href="{{ route('admin.usuarios.index') }}" class="ad-nav-link {{ request()->routeIs('admin.usuarios.*') ? 'is-active' : '' }}">
                        <span class="ad-nav-dot"></span>
                        Usuarios
                    </a>

                    <details class="ad-nav-sub {{ $cliOpen ? 'is-route-active' : '' }}" data-nav-group="clientes" @if ($cliOpen) open @endif>
                        <summary>
                            <span class="ad-nav-dot is-accent"></span>
                            <span class="ad-nav-group-title">Clientes</span>
                            <span class="ad-nav-chevron" aria-hidden="true"></span>
                        </summary>
                        <div class="ad-nav-children">
                            <a href="{{ route('admin.clientes.virtuales') }}" class="ad-nav-link {{ request()->routeIs('admin.clientes.virtuales') ? 'is-active' : '' }}">
                                Virtuales
                            </a>
                            <a href="{{ route('admin.clientes.presenciales') }}" class="ad-nav-link {{ request()->routeIs('admin.clientes.presenciales') ? 'is-active' : '' }}">
                                Presenciales
                            </a>
                        </div>
                    </details>

                    <a href="{{ route('admin.instituciones.index') }}" class="ad-nav-link {{ request()->routeIs('admin.instituciones.*') ? 'is-active' : '' }}">
                        <span class="ad-nav-dot"></span>
                        Instituciones
                    </a>
                    <a href="{{ route('admin.coaches.index') }}" class="ad-nav-link {{ request()->routeIs('admin.coaches.*') ? 'is-active' : '' }}">
                        <span class="ad-nav-dot"></span>
                        Coaches
                    </a>
                </div>
            </details>

            <details class="ad-nav-group {{ $comOpen ? 'is-route-active' : '' }}" data-nav-group="comercial" @if ($comOpen) open @endif>
                <summary>
                    <span class="ad-nav-orb is-cyan">@include('partials.admin-icon', ['icon' => 'planes'])</span>
                    <span class="ad-nav-group-title">Comercial</span>
                    <span class="ad-nav-chevron" aria-hidden="true"></span>
                </summary>
                <div class="ad-nav-children">
                    <a href="{{ route('admin.planes.index') }}" class="ad-nav-link {{ request()->routeIs('admin.planes.*') ? 'is-active' : '' }}">
                        <span class="ad-nav-dot"></span>
                        Planes
                    </a>
                    <a href="{{ route('admin.cupones.index') }}" class="ad-nav-link {{ request()->routeIs('admin.cupones.*') ? 'is-active' : '' }}">
                        <span class="ad-nav-dot"></span>
                        Cupones
                    </a>
                    <a href="{{ route('admin.tienda.index') }}" class="ad-nav-link {{ request()->routeIs('admin.tienda.*') ? 'is-active' : '' }}">
                        <span class="ad-nav-dot"></span>
                        Tienda
                    </a>
                    <a href="{{ route('admin.pasarelas.index') }}" class="ad-nav-link {{ request()->routeIs('admin.pasarelas.*') ? 'is-active' : '' }}">
                        <span class="ad-nav-dot"></span>
                        Pasarelas
                    </a>
                </div>
            </details>

            <details class="ad-nav-group {{ $comuOpen ? 'is-route-active' : '' }}" data-nav-group="comunicacion" @if ($comuOpen) open @endif>
                <summary>
                    <span class="ad-nav-orb is-pink">@include('partials.admin-icon', ['icon' => 'comunidad'])</span>
                    <span class="ad-nav-group-title">Comunicación</span>
                    <span class="ad-nav-chevron" aria-hidden="true"></span>
                </summary>
                <div class="ad-nav-children">
                    <a href="{{ route('admin.whatsapp.index') }}" class="ad-nav-link {{ request()->routeIs('admin.whatsapp.*') ? 'is-active' : '' }}">
                        <span class="ad-nav-dot"></span>
                        WhatsApp
                    </a>
                    <a href="{{ route('admin.comunidad.index') }}" class="ad-nav-link {{ request()->routeIs('admin.comunidad.*') ? 'is-active' : '' }}">
                        <span class="ad-nav-dot"></span>
                        Comunidad
                    </a>
                </div>
            </details>

            <details class="ad-nav-group {{ $anOpen ? 'is-route-active' : '' }}" data-nav-group="analisis" @if ($anOpen) open @endif>
                <summary>
                    <span class="ad-nav-orb is-cyan">@include('partials.admin-icon', ['icon' => 'estadisticas'])</span>
                    <span class="ad-nav-group-title">Análisis</span>
                    <span class="ad-nav-chevron" aria-hidden="true"></span>
                </summary>
                <div class="ad-nav-children">
                    <a href="{{ route('admin.estadisticas.index') }}" class="ad-nav-link {{ request()->routeIs('admin.estadisticas.*') ? 'is-active' : '' }}">
                        <span class="ad-nav-dot"></span>
                        Estadísticas
                    </a>
                    <a href="{{ route('admin.reportes.index') }}" class="ad-nav-link {{ request()->routeIs('admin.reportes.*') ? 'is-active' : '' }}">
                        <span class="ad-nav-dot"></span>
                        Reportes
                    </a>
                </div>
            </details>
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

            // Nav accordion: persist open groups; route-active always wins
            (function () {
                var nav = document.querySelector('[data-admin-nav]');
                if (!nav) return;
                var KEY = 'ff-admin-nav';
                var stored = {};
                try { stored = JSON.parse(localStorage.getItem(KEY) || '{}') || {}; } catch (e) { stored = {}; }

                nav.querySelectorAll('[data-nav-group]').forEach(function (el) {
                    var id = el.getAttribute('data-nav-group');
                    if (el.classList.contains('is-route-active')) {
                        el.open = true;
                        return;
                    }
                    if (Object.prototype.hasOwnProperty.call(stored, id)) {
                        el.open = !!stored[id];
                    }
                });

                nav.addEventListener('toggle', function (event) {
                    var el = event.target;
                    if (!el || !el.getAttribute || !el.getAttribute('data-nav-group')) return;
                    stored[el.getAttribute('data-nav-group')] = el.open;
                    try { localStorage.setItem(KEY, JSON.stringify(stored)); } catch (e) {}
                }, true);
            })();
        })();
    </script>
</body>
</html>
