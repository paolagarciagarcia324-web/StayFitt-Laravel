<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') | FigueFit</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body class="ad-body">
    <aside class="ad-sidebar">
        <a href="{{ route('admin.dashboard') }}" class="ad-brand">
            <img src="{{ asset('images/welcome/Logo.png') }}" alt="FigueFit">
            <span>Admin</span>
        </a>

        <nav class="ad-nav">
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">Dashboard</a>
            <a href="{{ route('admin.solicitudes.index') }}" class="{{ request()->routeIs('admin.solicitudes.*') ? 'is-active' : '' }}">Solicitudes</a>
            <a href="{{ route('admin.pagos.index') }}" class="{{ request()->routeIs('admin.pagos.*') ? 'is-active' : '' }}">Pagos</a>
            <a href="{{ route('admin.planes.index') }}" class="{{ request()->routeIs('admin.planes.*') ? 'is-active' : '' }}">Planes</a>
            <a href="{{ route('admin.usuarios.index') }}" class="{{ request()->routeIs('admin.usuarios.*') ? 'is-active' : '' }}">Usuarios</a>
            <a href="{{ route('admin.clientes.index') }}" class="{{ request()->routeIs('admin.clientes.*') ? 'is-active' : '' }}">Clientes</a>
            <a href="{{ route('admin.coaches.index') }}" class="{{ request()->routeIs('admin.coaches.*') ? 'is-active' : '' }}">Coaches</a>
            <a href="{{ route('admin.instituciones.index') }}" class="{{ request()->routeIs('admin.instituciones.*') ? 'is-active' : '' }}">Instituciones</a>
        </nav>

        <div class="ad-sidebar-foot">
            <p>{{ auth()->user()?->nombres }}</p>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Cerrar sesión</button>
            </form>
        </div>
    </aside>

    <div class="ad-main">
        <header class="ad-top">
            <div>
                <h1>@yield('title', 'Dashboard')</h1>
                <p>@yield('subtitle', 'Panel de administración StayFit')</p>
            </div>
            <a class="ad-link" href="{{ route('welcome') }}">Ver sitio</a>
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
</body>
</html>
