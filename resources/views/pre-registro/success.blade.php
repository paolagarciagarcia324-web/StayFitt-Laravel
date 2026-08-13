<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud enviada | FigueFit</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
</head>
<body class="ff-body ff-pre-page">
    <main class="ff-pre-main ff-pre-success">
        <div class="ff-pre-form-card ff-success-card">
            <p class="ff-kicker">Solicitud registrada</p>
            <h1>¡Gracias, {{ $solicitud->nombres }}!</h1>
            <p class="ff-body">Tu pre-registro fue guardado en la base de datos y está en revisión.</p>

            <div class="ff-success-box">
                <p><strong>Código de solicitud:</strong> {{ $solicitud->codigo_solicitud }}</p>
                <p><strong>Plan:</strong> {{ $solicitud->plan->nombre }}</p>
                <p><strong>Estado solicitud:</strong> {{ $solicitud->estado_solicitud }}</p>
                <p><strong>Estado pago:</strong> {{ $solicitud->pago?->estado_pago ?? 'PENDIENTE' }}</p>
                <p><strong>Monto:</strong> ${{ number_format((float) $solicitud->pago?->monto, 0, ',', '.') }} {{ $solicitud->pago?->moneda }}</p>
            </div>

            <p class="ff-pre-note">Cuando el administrador valide tu pago, se creará tu cuenta de cliente. Si es rechazada, se liberará el cupo del plan.</p>

            <a class="ff-cta" href="{{ route('welcome') }}">VOLVER AL INICIO →</a>
        </div>
    </main>
</body>
</html>
