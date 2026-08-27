<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Institucional') | FigueFit</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-ds.css') }}">
    <link rel="stylesheet" href="{{ asset('css/institucional-ds.css') }}">
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
<body class="ad-body is-institucional">
    <input class="ad-nav-check" type="checkbox" id="ad-nav-toggle" checked>

    @php
        $navUser = $instNavUser ?? auth()->user();
        $navInst = $instNavInstitucion ?? null;
    @endphp

    <aside class="ad-sidebar">
        <div class="ad-brand">
            <div class="ad-brand-mark">
                <img src="{{ asset('images/admin/figuefit-logo-circle.png') }}" alt="FigueFit">
            </div>
            <div class="ad-brand-copy">
                <strong>FigueFit</strong>
                <small>Portal B2B</small>
            </div>
        </div>

        @if ($navInst)
            <div class="in-side-org">
                <div class="in-orgchip">
                    <div class="in-orglogo">
                        @if ($navInst->logo_url)
                            <img src="{{ $navInst->logo_url }}" alt="">
                        @else
                            {{ mb_substr($navInst->nombre, 0, 2) }}
                        @endif
                    </div>
                    <div>
                        <strong>{{ \Illuminate\Support\Str::limit($navInst->nombre, 28) }}</strong>
                        <small>{{ $navInst->etiquetaTipo() }}</small>
                    </div>
                </div>
            </div>
        @endif

        @if (($instNavInstituciones ?? collect())->isNotEmpty())
            <form class="in-inst-select" method="POST" action="{{ route('institucional.contexto.update') }}">
                @csrf
                <label>Institución (admin)</label>
                <select name="id_institucion" onchange="this.form.submit()">
                    @foreach ($instNavInstituciones as $inst)
                        <option value="{{ $inst->id_institucion }}" @selected($navInst && (int) $navInst->id_institucion === (int) $inst->id_institucion)>
                            {{ $inst->nombre }}
                        </option>
                    @endforeach
                </select>
            </form>
        @endif

        <nav class="ad-nav">
            <a href="{{ route('institucional.dashboard') }}" class="ad-nav-link {{ request()->routeIs('institucional.dashboard') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-cyan">@include('partials.admin-icon', ['icon' => 'dashboard'])</span>
                Dashboard
            </a>
            <a href="{{ route('institucional.perfil.show') }}" class="ad-nav-link {{ request()->routeIs('institucional.perfil.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-pink">@include('partials.admin-icon', ['icon' => 'personas'])</span>
                Perfil institución
            </a>
            <a href="{{ route('institucional.usuarios.index') }}" class="ad-nav-link {{ request()->routeIs('institucional.usuarios.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-cyan">@include('partials.admin-icon', ['icon' => 'clientes'])</span>
                Usuarios vinculados
                @if (($instNavVinculados ?? 0) > 0)
                    <em class="st-nav-badge">{{ $instNavVinculados }}</em>
                @endif
            </a>
            <a href="{{ route('institucional.invitaciones.index') }}" class="ad-nav-link {{ request()->routeIs('institucional.invitaciones.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-pink">@include('partials.admin-icon', ['icon' => 'eventos'])</span>
                Invitaciones
            </a>
            <a href="{{ route('institucional.plan.index') }}" class="ad-nav-link {{ request()->routeIs('institucional.plan.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-cyan">@include('partials.admin-icon', ['icon' => 'planes'])</span>
                Plan convenio
            </a>
            <a href="{{ route('institucional.reportes.index') }}" class="ad-nav-link {{ request()->routeIs('institucional.reportes.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-pink">@include('partials.admin-icon', ['icon' => 'actividad'])</span>
                Reportes
            </a>
            <a href="{{ route('institucional.comunicacion.index') }}" class="ad-nav-link {{ request()->routeIs('institucional.comunicacion.*') ? 'is-active' : '' }}">
                <span class="ad-nav-orb is-cyan">@include('partials.admin-icon', ['icon' => 'comunidad'])</span>
                Comunicación
            </a>
            @if (auth()->user()?->tieneRol('ADMIN'))
                <a href="{{ route('admin.instituciones.index') }}" class="ad-nav-link">
                    <span class="ad-nav-orb is-pink">@include('partials.admin-icon', ['icon' => 'dashboard'])</span>
                    Admin instituciones
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
            @if (session('institucional_ok'))<div class="in-alert-ok">{{ session('institucional_ok') }}</div>@endif
            @if (isset($errors) && $errors->any())
                <div class="ad-alert ad-alert-error"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            <main class="ad-content">@yield('content')</main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
