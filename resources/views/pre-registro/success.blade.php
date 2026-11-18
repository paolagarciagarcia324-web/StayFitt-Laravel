<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago aprobado | FigueFit</title>
    @include('partials.favicon')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
</head>
<body class="ff-body ff-pre-page">
    <main class="ff-pre-main ff-pre-success">
        <div class="ff-pre-form-card ff-success-card">
            <p class="ff-kicker">Pago aprobado</p>
            <h1>Bienvenida a FigueFit, {{ $solicitud->nombres }}</h1>
            <p class="ff-body">Tu plan virtual ya está activo. Entra con tu correo y tu documento como contraseña inicial.</p>

            <div class="ff-success-box">
                <p><strong>Código:</strong> {{ $solicitud->codigo_solicitud }}</p>
                <p><strong>Plan:</strong> {{ $solicitud->plan->nombre }}</p>
                <p><strong>Pago:</strong> {{ $solicitud->pago?->estado_pago }} · {{ $solicitud->pago?->metodo_pago }}</p>
                <p><strong>Correo de acceso:</strong> {{ $solicitud->correo }}</p>
            </div>

            @include('partials.ff-cta', ['href' => route('login'), 'label' => 'INICIAR SESIÓN', 'icon' => 'login'])
        </div>
    </main>
</body>
</html>
