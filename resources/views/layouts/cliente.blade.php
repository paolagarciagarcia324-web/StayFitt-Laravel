<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Mi espacio') | FigueFit</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-ds.css') }}">
    <link rel="stylesheet" href="{{ asset('css/client-ds.css') }}">
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
<body class="ad-body is-cliente">
    <input class="ad-nav-check" type="checkbox" id="ad-nav-toggle" checked>

    @php
        $navCodigos = collect($clienteNavCodigos ?? []);
        $tiene = fn (string $c) => $navCodigos->contains($c);
        $navUser = $clienteNavUser ?? auth()->user();
        $esPresencial = strtoupper((string) ($clienteNavCliente->modalidad ?? '')) === 'PRESENCIAL';
        $navItems = [
            ['always' => true, 'route' => 'cliente.dashboard', 'match' => 'cliente.dashboard', 'label' => 'Inicio', 'icon' => 'dashboard', 'orb' => 'is-pink'],
            ['always' => true, 'route' => 'cliente.plan.index', 'match' => 'cliente.plan.*', 'label' => 'Mi membresía', 'icon' => 'planes', 'orb' => 'is-cyan'],
            ['mod' => 'ENTRENAMIENTO', 'route' => 'cliente.entrenamiento.index', 'match' => 'cliente.entrenamiento.*', 'label' => 'Entrenar', 'icon' => 'actividad', 'orb' => 'is-pink'],
            ['mod' => 'NUTRICION', 'route' => 'cliente.nutricion.index', 'match' => 'cliente.nutricion.*', 'label' => 'Nutrición', 'icon' => 'planes', 'orb' => 'is-cyan'],
            ['mod' => 'CONTENIDO_VIRTUAL', 'route' => 'cliente.contenido.index', 'match' => 'cliente.contenido.*', 'label' => 'Videos', 'icon' => 'comunidad', 'orb' => 'is-pink'],
            ['mod' => 'PROGRESO', 'route' => 'cliente.progreso.index', 'match' => 'cliente.progreso.*', 'label' => 'Progreso', 'icon' => 'estadisticas', 'orb' => 'is-cyan'],
            ['mod' => 'AGENDA', 'route' => 'cliente.agenda.index', 'match' => 'cliente.agenda.*', 'label' => 'Agenda', 'icon' => 'horarios', 'orb' => 'is-pink'],
            ['mod' => 'COMUNICACION', 'route' => 'cliente.comunicacion.index', 'match' => 'cliente.comunicacion.*', 'label' => 'Comunidad', 'icon' => 'comunidad', 'orb' => 'is-cyan'],
            ['mod' => 'TIENDA', 'route' => 'cliente.tienda.index', 'match' => 'cliente.tienda.*', 'label' => 'Tienda', 'icon' => 'tienda', 'orb' => 'is-pink'],
            ['always' => true, 'route' => 'cliente.comercial.planes', 'match' => 'cliente.comercial.*', 'label' => 'Planes', 'icon' => 'pagos', 'orb' => 'is-cyan'],
            ['always' => true, 'route' => 'cliente.analisis.index', 'match' => 'cliente.analisis.*', 'label' => 'Análisis', 'icon' => 'dashboard', 'orb' => 'is-pink'],
            $esPresencial
                ? ['always' => true, 'route' => 'cliente.presencial.perfil', 'match' => 'cliente.presencial.*', 'label' => 'Ficha presencial', 'icon' => 'clientes', 'orb' => 'is-cyan']
                : ['always' => true, 'route' => 'cliente.perfil.edit', 'match' => 'cliente.perfil.*', 'label' => 'Perfil', 'icon' => 'clientes', 'orb' => 'is-cyan'],
        ];
    @endphp

    <aside class="ad-sidebar">
        <div class="ad-brand">
            <div class="ad-brand-mark">
                <img src="{{ asset('images/admin/figuefit-logo-circle.png') }}" alt="FigueFit">
            </div>
            <div class="ad-brand-copy">
                <strong>FigueFit</strong>
                <small>Member</small>
            </div>
        </div>

        @if ($navUser)
            <div class="cv-side-user">
                <div class="cv-userchip" style="border:0;background:transparent;padding:0">
                    <div class="cv-avatar">
                        @if ($navUser->avatarUrl())
                            <img src="{{ $navUser->avatarUrl() }}" alt="">
                        @else
                            {{ $navUser->iniciales() }}
                        @endif
                    </div>
                    <div>
                        <strong>{{ $navUser->nombres }}</strong>
                        <small>{{ $clienteNavPlanNombre ?? 'Sin plan' }}
                            @if ($clienteNavDiasPlan !== null)
                                · {{ $clienteNavDiasPlan }}d
                            @endif
                        </small>
                    </div>
                </div>
            </div>
        @endif

        <nav class="ad-nav">
            @foreach ($navItems as $item)
                @if (!empty($item['always']) || (!empty($item['mod']) && $tiene($item['mod'])))
                    <a href="{{ route($item['route']) }}" class="ad-nav-link {{ request()->routeIs($item['match']) ? 'is-active' : '' }}">
                        <span class="ad-nav-orb {{ $item['orb'] }}">@include('partials.admin-icon', ['icon' => $item['icon']])</span>
                        {{ $item['label'] }}
                    </a>
                @endif
            @endforeach

            <a href="{{ route('cliente.notificaciones.index') }}" class="ad-nav-link {{ request()->routeIs('cliente.notificaciones.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-pink">@include('partials.admin-icon', ['icon' => 'alerta'])</span>
                Avisos
                @if (($clienteNavNoLeidas ?? 0) > 0)
                    <em class="cv-nav-badge">{{ $clienteNavNoLeidas }}</em>
                @endif
            </a>
            <a href="{{ route('cliente.pagos.index') }}" class="ad-nav-link {{ request()->routeIs('cliente.pagos.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-cyan">@include('partials.admin-icon', ['icon' => 'pagos'])</span>
                Pagos
            </a>
            <a href="{{ route('cliente.novedades.index') }}" class="ad-nav-link {{ request()->routeIs('cliente.novedades.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-pink">@include('partials.admin-icon', ['icon' => 'eventos'])</span>
                Novedades
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
            <label class="ad-nav-burger" for="ad-nav-toggle" aria-label="Menú">
                <span></span><span></span><span></span>
            </label>
            <div class="ad-header-actions" style="display:flex;gap:10px;align-items:center">
                <a class="cv-btn cv-btn-ghost cv-btn-sm" href="{{ route('cliente.notificaciones.index') }}">
                    Avisos
                    @if (($clienteNavNoLeidas ?? 0) > 0)
                        ({{ $clienteNavNoLeidas }})
                    @endif
                </a>
                @include('partials.admin-header-tools')
            </div>
        </header>

        <div class="ad-main">
            @if (session('success'))
                <div class="cv-page" style="padding-bottom:0">
                    <div class="cv-alert">{{ session('success') }}</div>
                </div>
            @endif
            @if (session('error'))
                <div class="cv-page" style="padding-bottom:0">
                    <div class="cv-alert is-error">{{ session('error') }}</div>
                </div>
            @endif
            @if (isset($errors) && $errors->any())
                <div class="cv-page" style="padding-bottom:0">
                    <div class="cv-alert is-error">
                        <ul style="margin:0;padding-left:18px">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <main class="cv-page">
                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
