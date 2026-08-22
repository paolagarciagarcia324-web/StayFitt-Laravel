<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprar {{ $plan->nombre }} | FigueFit</title>
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
                <p class="ff-kicker">Virtual · {{ $plan->tipo_cliente }}</p>
                <h1>Tu ficha de compra</h1>
                <p class="ff-body"><strong>{{ $plan->nombre }}.</strong> {{ $plan->descripcion }}</p>

                <div class="ff-reg-price">
                    <span>Inversión mensual</span>
                    <strong>{{ $plan->precioFormateado() }}</strong>
                </div>

                <ul class="ff-reg-points">
                    @foreach (array_slice($plan->caracteristicas_json ?? ['Entrenamiento virtual', 'Seguimiento en tu panel', 'Activación al pagar', 'Acceso desde cualquier lugar'], 0, 4) as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>

                <p class="ff-pre-note">Acceso: tu correo + documento como contraseña inicial.</p>
                </div>
                <a class="ff-back-home" href="{{ route('welcome') }}#planes">
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

                <form class="ff-reg-form" method="POST" action="{{ route('pre-registro.store', $plan) }}">
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
                            <label>
                                <span>Género *</span>
                                <select name="genero" required>
                                    <option value="">Selecciona...</option>
                                    <option value="FEMENINO" @selected(old('genero') === 'FEMENINO')">Femenino</option>
                                    <option value="MASCULINO" @selected(old('genero') === 'MASCULINO')">Masculino</option>
                                </select>
                            </label>
                            <label><span>Objetivo</span><input type="text" name="objetivo_principal" value="{{ old('objetivo_principal') }}" placeholder="Fuerza, salud, definición..."></label>
                        </div>
                    </div>

                    <div class="ff-reg-block">
                        <header class="ff-reg-block-head">
                            <span>02</span>
                            <h2>Medidas iniciales</h2>
                        </header>
                        <div class="ff-reg-grid three">
                            <label><span>Peso (kg) *</span><input type="number" step="0.01" name="peso_kg" value="{{ old('peso_kg') }}" required></label>
                            <label><span>Estatura (cm) *</span><input type="number" step="0.01" name="estatura_cm" value="{{ old('estatura_cm') }}" required></label>
                            <label><span>Cintura (cm) *</span><input type="number" step="0.01" name="cintura_cm" value="{{ old('cintura_cm') }}" required></label>
                            <label><span>Cadera (cm) *</span><input type="number" step="0.01" name="cadera_cm" value="{{ old('cadera_cm') }}" required></label>
                            <label><span>Brazo (cm) *</span><input type="number" step="0.01" name="brazo_cm" value="{{ old('brazo_cm') }}" required></label>
                            <label><span>Pierna (cm) *</span><input type="number" step="0.01" name="pierna_cm" value="{{ old('pierna_cm') }}" required></label>
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
                            <h2>Pago del plan</h2>
                        </header>

                        @include('partials.pago-simulacion', ['plan' => $plan])

                        <label class="ff-reg-obs">
                            <span>Observaciones (opcional)</span>
                            <textarea name="observacion_usuario" rows="2">{{ old('observacion_usuario') }}</textarea>
                        </label>

                        <label class="ff-terms">
                            <input type="checkbox" name="acepto_terminos" value="1" @checked(old('acepto_terminos')) required>
                            <span>Acepto los términos y condiciones de FigueFit *</span>
                        </label>

                        @include('partials.ff-cta', ['label' => 'PAGAR Y ACTIVAR', 'icon' => 'pago', 'class' => 'ff-reg-submit'])
                    </div>
                </form>
            </section>
        </div>
    </main>
</body>
</html>
