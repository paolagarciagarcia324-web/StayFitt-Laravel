<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro presencial | FigueFit</title>
    @include('partials.favicon')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
</head>
<body class="ff-body ff-pre-page ff-reg-page">
    <header class="ff-header">
        <span class="ff-logo" aria-label="FigueFit">
            <img src="{{ asset('images/welcome/Logo.png') }}" alt="FigueFit">
        </span>
        <div class="ff-actions">
            @include('partials.ff-cta', ['href' => route('login'), 'label' => 'INICIAR SESIÓN', 'icon' => 'login', 'class' => 'ff-btn ff-btn-quiet'])
        </div>
    </header>

    <main class="ff-pre-main ff-reg-main">
        <div class="ff-reg-shell">
            <aside class="ff-reg-aside">
                <div class="ff-reg-aside-body">
                <p class="ff-kicker">Presencial · Campoalegre</p>
                <h1>Tu ficha de inscripción</h1>
                <p class="ff-body">Servicio personalizado en sala. Eliges horario, pagas aquí y tu rutina queda en el panel de cliente.</p>

                <div class="ff-reg-price">
                    <span>Inversión mensual</span>
                    <strong>{{ $plan->precioFormateado() }}</strong>
                </div>

                <ul class="ff-reg-points">
                    <li>Horario a tu medida</li>
                    <li>Rutinas en tu panel</li>
                    <li>Activación al pagar</li>
                    <li>WhatsApp del grupo</li>
                </ul>

                <p class="ff-pre-note">Acceso: tu correo + documento como contraseña inicial.</p>
                </div>
                <a class="ff-back-home" href="{{ route('welcome') }}#registro">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M15.4 5.3a1 1 0 0 1 .05 1.4L11.16 12l4.29 5.3a1 1 0 1 1-1.55 1.26l-4.8-5.93a1 1 0 0 1 0-1.26l4.8-5.93a1 1 0 0 1 1.5-.14Z"/></svg>
                    Volver
                </a>
            </aside>

            <section class="ff-reg-panel">
                @if ($errors->any())
                    <div class="ff-alert ff-alert-error">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form class="ff-reg-form" method="POST" action="{{ route('registro-presencial.store') }}">
                    @csrf

                    <div class="ff-reg-block">
                        <header class="ff-reg-block-head">
                            <span>01</span>
                            <h2>Datos personales</h2>
                        </header>
                        <div class="ff-reg-grid two">
                            <label><span>Nombres *</span><input type="text" name="nombres" value="{{ old('nombres') }}" required></label>
                            <label><span>Apellidos *</span><input type="text" name="apellidos" value="{{ old('apellidos') }}" required></label>
                            <label>
                                <span>Tipo de documento *</span>
                                <select name="tipo_documento" required>
                                    @foreach (['CC' => 'Cédula', 'CE' => 'Cédula extranjería', 'TI' => 'Tarjeta identidad', 'PASAPORTE' => 'Pasaporte', 'OTRO' => 'Otro'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('tipo_documento', 'CC') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label><span>Documento *</span><input type="text" name="documento_identidad" value="{{ old('documento_identidad') }}" required></label>
                            <label><span>Correo *</span><input type="email" name="correo" value="{{ old('correo') }}" required></label>
                            <label><span>Teléfono *</span><input type="text" name="telefono" value="{{ old('telefono') }}" required></label>
                            <label><span>Ciudad *</span><input type="text" name="ciudad" value="{{ old('ciudad') }}" required></label>
                            <label><span>Fecha de nacimiento *</span><input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" required></label>
                            <label>
                                <span>Género *</span>
                                <select name="genero" required>
                                    <option value="FEMENINO" @selected(old('genero', 'FEMENINO') === 'FEMENINO')">Femenino</option>
                                </select>
                                <small class="ff-field-note">Presencial: solo género femenino.</small>
                            </label>
                            <label><span>Objetivo *</span><input type="text" name="objetivo_principal" value="{{ old('objetivo_principal') }}" placeholder="Fuerza, salud, definición..." required></label>
                        </div>
                    </div>

                    <div class="ff-reg-block">
                        <header class="ff-reg-block-head">
                            <span>02</span>
                            <h2>Medidas iniciales</h2>
                        </header>
                        <div class="ff-reg-grid three">
                            <label><span>Peso (kg) *</span><input type="number" step="0.01" min="0.1" name="peso_kg" value="{{ old('peso_kg') }}" required></label>
                            <label><span>Estatura (cm) *</span><input type="number" step="0.01" min="0.1" name="estatura_cm" value="{{ old('estatura_cm') }}" required></label>
                            <label><span>Cintura (cm) *</span><input type="number" step="0.01" min="0.1" name="cintura_cm" value="{{ old('cintura_cm') }}" required></label>
                            <label><span>Cadera (cm) *</span><input type="number" step="0.01" min="0.1" name="cadera_cm" value="{{ old('cadera_cm') }}" required></label>
                            <label><span>Brazo (cm) *</span><input type="number" step="0.01" min="0.1" name="brazo_cm" value="{{ old('brazo_cm') }}" required></label>
                            <label><span>Pierna (cm) *</span><input type="number" step="0.01" min="0.1" name="pierna_cm" value="{{ old('pierna_cm') }}" required></label>
                        </div>
                    </div>

                    <div class="ff-reg-block">
                        <header class="ff-reg-block-head">
                            <span>03</span>
                            <h2>Salud y emergencia</h2>
                        </header>
                        <div class="ff-reg-grid two">
                            <label><span>Alergias</span><textarea name="alergias" rows="2">{{ old('alergias') }}</textarea></label>
                            <label><span>Condiciones médicas</span><textarea name="condiciones_medicas" rows="2">{{ old('condiciones_medicas') }}</textarea></label>
                            <label><span>Lesiones</span><textarea name="lesiones" rows="2">{{ old('lesiones') }}</textarea></label>
                            <label><span>Medicamentos</span><textarea name="medicamentos" rows="2">{{ old('medicamentos') }}</textarea></label>
                            <label><span>Contacto de emergencia *</span><input type="text" name="contacto_emergencia_nombre" value="{{ old('contacto_emergencia_nombre') }}" required></label>
                            <label><span>Teléfono de emergencia *</span><input type="text" name="contacto_emergencia_telefono" value="{{ old('contacto_emergencia_telefono') }}" required></label>
                        </div>
                    </div>

                    <div class="ff-reg-block">
                        <header class="ff-reg-block-head">
                            <span>04</span>
                            <h2>Horario y pago</h2>
                        </header>

                        <p class="ff-reg-hint">Elige el horario al que quieres pertenecer *</p>
                        <div class="ff-horario-grid" role="radiogroup" aria-label="Horarios disponibles">
                            @foreach ($horarios as $horario)
                                <label class="ff-horario-card">
                                    <input type="radio" name="id_horario" value="{{ $horario->id_horario }}" @checked((string) old('id_horario') === (string) $horario->id_horario) required>
                                    <span class="ff-horario-body">
                                        <strong>{{ $horario->nombre }}</strong>
                                        <em>{{ $horario->dias }}</em>
                                        <small>{{ $horario->rangoHora12() }}</small>
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        <p class="ff-reg-hint">Pago en línea *</p>
                        @include('partials.pago-simulacion', ['plan' => $plan])

                        <label class="ff-reg-obs">
                            <span>Observaciones (opcional)</span>
                            <textarea name="observacion_usuario" rows="2">{{ old('observacion_usuario') }}</textarea>
                        </label>

                        <div class="ff-terms-card">
                            <label class="ff-terms-label">
                                <input type="checkbox" name="acepto_terminos" value="1" @checked(old('acepto_terminos')) required>
                                <span>Acepto los términos y condiciones de FigueFit *</span>
                            </label>
                            <button type="button" class="ff-terms-detail-btn" data-policy-trigger="terminos" aria-haspopup="dialog" aria-label="Ver detalle de términos y condiciones">
                                <svg viewBox="0 0 24 24" width="13" height="13" aria-hidden="true"><path fill="currentColor" d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2Zm0 18a8 8 0 1 1 8-8 8.009 8.009 0 0 1-8 8Zm0-13a1.25 1.25 0 1 0 1.25 1.25A1.25 1.25 0 0 0 12 7Zm1 4h-2a1 1 0 0 0 0 2h1v4a1 1 0 0 0 2 0v-5a1 1 0 0 0-1-1Z"/></svg>
                                Ver detalle
                            </button>
                        </div>

                        @include('partials.ff-cta', ['label' => 'CONTINUAR AL PAGO', 'icon' => 'pago', 'class' => 'ff-reg-submit'])
                    </div>
                </form>
            </section>
        </div>
    </main>

    @include('partials.modal-terminos-politicas')
</body>
</html>
