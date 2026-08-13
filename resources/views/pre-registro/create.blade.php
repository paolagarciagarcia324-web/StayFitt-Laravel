<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pre-registro | {{ $plan->nombre }} | FigueFit</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
</head>
<body class="ff-body ff-pre-page">
    <header class="ff-header">
        <a href="{{ route('welcome') }}#planes" class="ff-logo" aria-label="FigueFit">
            <img src="{{ asset('images/welcome/Logo.png') }}" alt="FigueFit">
        </a>
        <div class="ff-actions">
            <a class="ff-btn" href="{{ route('login') }}">INICIAR SESIÓN</a>
        </div>
    </header>

    <main class="ff-pre-main">
        <div class="ff-pre-wrap">
            <aside class="ff-pre-summary">
                <p class="ff-kicker">Solicitud de plan</p>
                <h1>{{ $plan->nombre }}</h1>
                <p class="ff-plan-price">{{ $plan->precioFormateado() }}</p>
                <p class="ff-body">{{ $plan->descripcion }}</p>
                <ul class="ff-plan-features">
                    @foreach ($plan->caracteristicas_json ?? [] as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
                @if ($plan->modulos->isNotEmpty())
                    <div class="ff-plan-modules">
                        @foreach ($plan->modulos as $modulo)
                            <span>{{ $modulo->nombre }}</span>
                        @endforeach
                    </div>
                @endif
                <p class="ff-pre-note">Tu solicitud quedará en estado <strong>PENDIENTE</strong> hasta que el administrador valide tu pago.</p>
                <a class="ff-back-home" href="{{ route('welcome') }}">← volver al inicio</a>
            </aside>

            <section class="ff-pre-form-card">
                <h2>Completa tu pre-registro</h2>
                <p class="ff-body">Adjunta tus datos y el comprobante de pago para iniciar el proceso.</p>

                @if ($errors->any())
                    <div class="ff-alert ff-alert-error">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form class="ff-form ff-form-grid" method="POST" action="{{ route('pre-registro.store', $plan) }}" enctype="multipart/form-data">
                    @csrf

                    <div class="ff-form-row">
                        <label>
                            <span>Nombres *</span>
                            <input type="text" name="nombres" value="{{ old('nombres') }}" required>
                        </label>
                        <label>
                            <span>Apellidos *</span>
                            <input type="text" name="apellidos" value="{{ old('apellidos') }}" required>
                        </label>
                    </div>

                    <div class="ff-form-row">
                        <label>
                            <span>Documento de identidad *</span>
                            <input type="text" name="documento_identidad" value="{{ old('documento_identidad') }}" required>
                        </label>
                        <label>
                            <span>Correo electrónico *</span>
                            <input type="email" name="correo" value="{{ old('correo') }}" required>
                        </label>
                    </div>

                    <div class="ff-form-row">
                        <label>
                            <span>Teléfono</span>
                            <input type="text" name="telefono" value="{{ old('telefono') }}">
                        </label>
                        <label>
                            <span>Ciudad</span>
                            <input type="text" name="ciudad" value="{{ old('ciudad') }}">
                        </label>
                    </div>

                    <label>
                        <span>Modalidad de entrenamiento *</span>
                        <select name="modalidad" required>
                            <option value="">Selecciona...</option>
                            <option value="VIRTUAL" @selected(old('modalidad') === 'VIRTUAL')>Virtual</option>
                            <option value="PRESENCIAL" @selected(old('modalidad') === 'PRESENCIAL')>Presencial</option>
                        </select>
                    </label>

                    <div class="ff-form-row">
                        <label>
                            <span>Método de pago *</span>
                            <select name="metodo_pago" required>
                                <option value="">Selecciona...</option>
                                @foreach (['TRANSFERENCIA', 'NEQUI', 'DAVIPLATA', 'EFECTIVO', 'PSE', 'OTRO'] as $metodo)
                                    <option value="{{ $metodo }}" @selected(old('metodo_pago') === $metodo)>{{ ucfirst(strtolower(str_replace('_', ' ', $metodo))) }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            <span>Referencia de pago *</span>
                            <input type="text" name="referencia_pago" value="{{ old('referencia_pago') }}" required>
                        </label>
                    </div>

                    <label>
                        <span>Comprobante de pago * (JPG, PNG o PDF)</span>
                        <input type="file" name="comprobante" accept=".jpg,.jpeg,.png,.pdf" required>
                    </label>

                    <label>
                        <span>Observaciones (opcional)</span>
                        <textarea name="observacion_usuario" rows="3">{{ old('observacion_usuario') }}</textarea>
                    </label>

                    <label class="ff-check">
                        <input type="checkbox" name="acepto_terminos" value="1" @checked(old('acepto_terminos')) required>
                        <span>Acepto los términos y condiciones de FigueFit *</span>
                    </label>

                    <button class="ff-cta" type="submit">ENVIAR SOLICITUD →</button>
                </form>
            </section>
        </div>
    </main>
</body>
</html>
