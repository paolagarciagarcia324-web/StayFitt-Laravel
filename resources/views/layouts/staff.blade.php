<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Staff') | FigueFit</title>
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
    <input class="ad-nav-check" type="checkbox" id="ad-nav-toggle" checked>

    <aside class="ad-sidebar">
        <div class="ad-brand">
            <div class="ad-brand-mark">
                <img src="{{ asset('images/admin/figuefit-logo-circle.png') }}" alt="FigueFit">
            </div>
            <div class="ad-brand-copy">
                <strong>FigueFit</strong>
                <small>Asistente</small>
            </div>
        </div>

        <nav class="ad-nav">
            <a href="{{ route('staff.dashboard') }}" class="ad-nav-link {{ request()->routeIs('staff.dashboard') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-pink">@include('partials.admin-icon', ['icon' => 'dashboard'])</span>
                Dashboard
            </a>
            <a href="{{ route('coach.propuestas.index') }}" class="ad-nav-link {{ request()->routeIs('coach.propuestas.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-cyan">@include('partials.admin-icon', ['icon' => 'planes'])</span>
                Propuestas de plan
            </a>
            <a href="{{ route('staff.novedades.index') }}" class="ad-nav-link {{ request()->routeIs('staff.novedades.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-pink">@include('partials.admin-icon', ['icon' => 'eventos'])</span>
                Novedades welcome
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
                <div class="ad-top-actions">@yield('actions')</div>
            </header>

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
