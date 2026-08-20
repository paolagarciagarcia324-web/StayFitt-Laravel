<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ya eres cliente | FigueFit</title>
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
            <p class="ff-body">La pasarela validó tu pago. Ya eres cliente presencial con acceso a tu panel.</p>

            <div class="ff-success-box">
                <p><strong>Código:</strong> {{ $solicitud->codigo_solicitud }}</p>
                <p><strong>Plan:</strong> {{ $solicitud->plan->nombre }}</p>
                @if (! empty($horario))
                    <p><strong>Horario:</strong> {{ $horario['nombre'] }} · {{ $horario['dias'] }} · {{ $horario['hora_inicio'] }} – {{ $horario['hora_fin'] }}</p>
                @endif
                <p><strong>Estado:</strong> {{ $solicitud->estado_solicitud }}</p>
                <p><strong>Pago:</strong> {{ $solicitud->pago?->estado_pago }} · {{ $solicitud->pago?->metodo_pago }}</p>
                <p><strong>Correo de acceso:</strong> {{ $solicitud->correo }}</p>
                <p><strong>Contraseña inicial:</strong> tu número de documento</p>
            </div>

            <p class="ff-pre-note">Entra con tu correo y documento para abrir tu panel de cliente.</p>
            @include('partials.ff-cta', ['href' => route('login'), 'label' => 'INICIAR SESIÓN', 'icon' => 'login'])
        </div>
    </main>

    @if ($mostrarWhatsapp && $linkWhatsapp)
        <div class="ff-wa-modal" id="ff-wa-modal" role="dialog" aria-modal="true" aria-labelledby="ff-wa-title">
            <div class="ff-wa-card">
                <button type="button" class="ff-wa-close" id="ff-wa-close" aria-label="Cerrar">×</button>
                <p class="ff-kicker">Comunidad de tu horario</p>
                <h2 id="ff-wa-title">Únete al WhatsApp</h2>
                <p class="ff-body">
                    @if (! empty($horario))
                        Grupo de <strong>{{ $horario['nombre'] }}</strong>
                        ({{ $horario['dias'] }} · {{ $horario['hora_inicio'] }} – {{ $horario['hora_fin'] }}).
                    @else
                        Grupo presencial FigueFit.
                    @endif
                    Aquí estarán las integrantes de tu horario.
                </p>
                @include('partials.ff-cta', ['href' => $linkWhatsapp, 'label' => 'ABRIR GRUPO DE WHATSAPP', 'icon' => 'whatsapp', 'class' => 'is-cyan', 'attrs' => ['target' => '_blank', 'rel' => 'noopener']])
                <p class="ff-pre-note">Guarda este enlace. También puedes cerrar esta tarjeta e ingresar a tu panel.</p>
            </div>
        </div>
        <script>
            (() => {
                const modal = document.getElementById('ff-wa-modal');
                const closeBtn = document.getElementById('ff-wa-close');
                const close = () => modal?.classList.add('is-hidden');
                closeBtn?.addEventListener('click', close);
                modal?.addEventListener('click', (e) => {
                    if (e.target === modal) close();
                });
            })();
        </script>
    @endif
</body>
</html>
