<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Iniciar sesión | FigueFit</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body class="lg-body">
    <main class="lg-shell">
        <section class="lg-visual">
            <img class="lg-visual-bg" src="{{ asset('images/welcome/login.png') }}" alt="">

            <div class="lg-visual-content">
                <a href="{{ route('welcome') }}" class="lg-logo">
                    <img src="{{ asset('images/welcome/Logo.png') }}" alt="FigueFit">
                </a>

                <div class="lg-visual-copy">
                    <h1>Tu proceso fitness,<br><span>empieza aquí.</span></h1>
                    <p>Entrena con propósito. Vive tu mejor versión con planes fitness personalizados, seguimiento profesional y bienestar diseñado para ti.</p>
                </div>

                <div class="lg-secure">
                    <span class="lg-secure-icon" aria-hidden="true">✓</span>
                    <div>
                        <strong>Acceso seguro</strong>
                        <small>Ingreso protegido para administradores, coaches, clientes e instituciones.</small>
                    </div>
                </div>
            </div>
        </section>

        <section class="lg-form-panel">
            <h2>Iniciar sesión</h2>
            <p class="lg-subtitle">Ingresa tus credenciales para continuar en FigueFit.</p>

            @if (session('status'))
                <p class="lg-status">{{ session('status') }}</p>
            @endif

            <form method="POST" action="{{ route('login') }}" class="lg-form">
                @csrf

                <label>
                    <span>Correo electrónico</span>
                    <div class="lg-input">
                        <span class="lg-input-icon" aria-hidden="true">✉</span>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="nombre@correo.com" required autofocus autocomplete="username">
                    </div>
                    @error('email')
                        <small class="lg-error">{{ $message }}</small>
                    @enderror
                </label>

                <label>
                    <div class="lg-label-row">
                        <span>Contraseña</span>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
                        @endif
                    </div>
                    <div class="lg-input">
                        <span class="lg-input-icon" aria-hidden="true">🔒</span>
                        <input id="password" type="password" name="password" placeholder="••••••••" required autocomplete="current-password">
                    </div>
                    @error('password')
                        <small class="lg-error">{{ $message }}</small>
                    @enderror
                </label>

                <p class="lg-hint">Si te inscribiste antes de crear contraseña, usa tu número de identificación como contraseña.</p>

                <button type="submit" class="lg-submit">Entrar al sistema →</button>
            </form>

            <a class="lg-back" href="{{ route('welcome') }}">· Volver al inicio</a>
        </section>
    </main>
</body>
</html>
