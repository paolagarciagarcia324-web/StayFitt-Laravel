<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagar inscripción | FigueFit</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
</head>
<body class="ff-body ff-pre-page">
    <main class="ff-pre-main ff-pre-success">
        <div class="ff-pre-form-card ff-success-card ff-checkout-card">
            <p class="ff-kicker">Simulacro de pasarela</p>
            <h1>Confirma tu pago</h1>
            <p class="ff-body">Al confirmar, simulamos la aprobación de la pasarela. Quedas activa como cliente y recibes el enlace del WhatsApp de tu horario.</p>

            <div class="ff-success-box">
                <p><strong>Sede:</strong> Campoalegre, Huila</p>
                <p><strong>Plan:</strong> {{ $solicitud->plan->nombre }}</p>
                @if (! empty($horario))
                    <p><strong>Horario:</strong> {{ $horario['nombre'] }} · {{ $horario['dias'] }} · {{ $horario['hora_inicio'] }} – {{ $horario['hora_fin'] }}</p>
                @endif
                <p><strong>Pasarela:</strong> {{ $pasarela?->nombre ?? $solicitud->pago?->pasarela }}</p>
                <p><strong>Referencia:</strong> {{ $solicitud->pago?->referencia_pago }}</p>
                <p><strong>Total:</strong> {{ $solicitud->plan->precioFormateado() }}</p>
            </div>

            <form method="POST" action="{{ route('registro-presencial.pagar', $solicitud->codigo_solicitud) }}">
                @csrf
                @include('partials.ff-cta', ['label' => 'PAGAR Y ACTIVAR MI ACCESO', 'icon' => 'pago'])
            </form>
        </div>
    </main>
</body>
</html>
