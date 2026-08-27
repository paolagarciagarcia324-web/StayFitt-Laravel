<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro institucional | FigueFit</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
    <style>
        .ri-wrap { max-width: 520px; margin: 48px auto; padding: 24px; }
        .ri-card { background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.1); border-radius: 18px; padding: 24px; }
        .ri-card h1 { font-size: 1.4rem; margin: 0 0 8px; }
        .ri-card p { color: rgba(255,255,255,.65); font-size: 14px; }
        .ri-field { margin-bottom: 12px; }
        .ri-field label { display: block; font-size: 12px; margin-bottom: 4px; color: rgba(255,255,255,.7); }
        .ri-field input { width: 100%; padding: 10px 12px; border-radius: 10px; border: 1px solid rgba(255,255,255,.15); background: rgba(0,0,0,.25); color: #fff; }
        .ri-btn { width: 100%; padding: 12px; border: none; border-radius: 999px; background: linear-gradient(135deg,#3b82f6,#2563eb); color: #fff; font-weight: 700; cursor: pointer; margin-top: 8px; }
        .ri-error { background: rgba(248,113,113,.15); border: 1px solid rgba(248,113,113,.3); padding: 12px; border-radius: 10px; margin-bottom: 16px; color: #fecaca; font-size: 13px; }
        .ri-badge { display: inline-block; padding: 4px 10px; border-radius: 999px; background: rgba(20,184,166,.2); color: #5eead4; font-size: 11px; font-weight: 700; margin-bottom: 12px; }
    </style>
</head>
<body class="ff-body">
    <div class="ri-wrap">
        <div class="ri-card">
            <span class="ri-badge">{{ $institucion->nombre }}</span>
            <h1>Alta institucional</h1>
            <p>Plan: <strong>{{ $plan?->nombre ?? 'Convenio institucional' }}</strong></p>

            @if ($error)
                <div class="ri-error">{{ $error }}</div>
            @else
                <form method="POST" action="{{ $tipo === 'invitacion' ? route('registro-institucional.invitacion.store', $token) : route('registro-institucional.enlace.store', $token) }}">
                    @csrf
                    <div class="ri-field"><label>Nombres</label><input type="text" name="nombres" value="{{ old('nombres', $nombre_sugerido ?? '') }}" required></div>
                    <div class="ri-field"><label>Apellidos</label><input type="text" name="apellidos" value="{{ old('apellidos') }}" required></div>
                    <div class="ri-field"><label>Correo</label><input type="email" name="correo" value="{{ old('correo', $correo ?? '') }}" required @if(!empty($correo)) readonly @endif></div>
                    <div class="ri-field"><label>Documento</label><input type="text" name="documento" value="{{ old('documento') }}"></div>
                    <div class="ri-field"><label>Teléfono</label><input type="text" name="telefono" value="{{ old('telefono') }}"></div>
                    <div class="ri-field"><label>Ciudad</label><input type="text" name="ciudad" value="{{ old('ciudad', $institucion->ciudad) }}"></div>
                    <div class="ri-field"><label>Contraseña</label><input type="password" name="password" required minlength="8"></div>
                    <div class="ri-field"><label>Confirmar contraseña</label><input type="password" name="password_confirmation" required></div>
                    <label style="display:flex;gap:8px;align-items:flex-start;font-size:12px;color:rgba(255,255,255,.7);margin:12px 0">
                        <input type="checkbox" name="acepto_terminos" value="1" required style="margin-top:2px">
                        Acepto términos y condiciones del servicio FigueFit
                    </label>
                    @if ($errors->any())
                        <div class="ri-error"><ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                    @endif
                    <button type="submit" class="ri-btn">Completar registro</button>
                </form>
            @endif
        </div>
    </div>
</body>
</html>
