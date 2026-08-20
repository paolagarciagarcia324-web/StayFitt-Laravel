<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'FigueFit' }}</title>
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
                <span class="lg-logo" aria-label="FigueFit">
                    <img src="{{ asset('images/welcome/Logo.png') }}" alt="FigueFit">
                </span>

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
            {{ $slot }}
        </section>
    </main>
</body>
</html>
