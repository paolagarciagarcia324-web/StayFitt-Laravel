@extends('layouts.cliente')

@section('title', 'Mi perfil')

@section('content')
    <x-cliente.page-head
        title="Mi perfil"
        subtitle="Datos personales, salud y preferencias de tu cuenta member"
        kicker="Cuenta"
    />

    <div class="cv-grid-12">
        <div class="cv-span-4">
            <x-cliente.card title="Resumen" subtitle="Tu identidad FigueFit">
                <div style="display:grid;gap:14px">
                    <div class="cv-avatar" style="width:88px;height:88px;font-size:26px">
                        @if ($user->avatarUrl())
                            <img src="{{ $user->avatarUrl() }}" alt="{{ $user->nombres }}">
                        @else
                            {{ $user->iniciales() }}
                        @endif
                    </div>

                    <div>
                        <strong style="font-size:16px">{{ $user->nombres }} {{ $user->apellidos }}</strong>
                        <p style="margin:4px 0 0;color:var(--cv-muted);font-size:13px">{{ $user->email }}</p>
                    </div>

                    <div style="display:flex;flex-wrap:wrap;gap:6px">
                        <span class="cv-badge is-pink">{{ $cliente->estado_cliente }}</span>
                        <span class="cv-badge is-cyan">{{ $cliente->modalidad }}</span>
                        @if ($cliente->objetivo_principal)
                            <span class="cv-badge">{{ $cliente->objetivo_principal }}</span>
                        @endif
                    </div>

                    <div class="cv-profile-rail">
                        <div class="cv-row">
                            <span>Teléfono</span>
                            <strong>{{ $user->telefono ?: '—' }}</strong>
                        </div>
                        <div class="cv-row">
                            <span>Ciudad</span>
                            <strong>{{ $cliente->ciudad ?: '—' }}</strong>
                        </div>
                        <div class="cv-row">
                            <span>Peso inicial</span>
                            <strong>{{ $cliente->peso_inicial_kg ? $cliente->peso_inicial_kg.' kg' : '—' }}</strong>
                        </div>
                        <div class="cv-row">
                            <span>Estatura</span>
                            <strong>{{ $cliente->estatura_cm ? $cliente->estatura_cm.' cm' : '—' }}</strong>
                        </div>
                    </div>
                </div>
            </x-cliente.card>
        </div>

        <div class="cv-span-8">
            <nav class="cv-tabs">
                <a class="cv-tab is-active" href="#datos">Datos personales</a>
                <a class="cv-tab" href="#salud">Salud y emergencia</a>
                <a class="cv-tab" href="#seguridad">Seguridad</a>
            </nav>

            <x-cliente.card title="Información de cuenta" subtitle="Actualiza tus datos y preferencias">
                <form method="POST" action="{{ route('cliente.perfil.update') }}">
                    @csrf
                    @method('PUT')

                    <div id="datos" style="display:grid;gap:16px;margin-bottom:20px">
                        <div class="cv-kicker">Datos personales</div>
                        <div class="cv-form">
                            <label>Nombres
                                <input name="nombres" value="{{ old('nombres', $user->nombres) }}" required>
                            </label>
                            <label>Apellidos
                                <input name="apellidos" value="{{ old('apellidos', $user->apellidos) }}" required>
                            </label>
                            <label>Teléfono
                                <input name="telefono" value="{{ old('telefono', $user->telefono) }}">
                            </label>
                            <label>Ciudad
                                <input name="ciudad" value="{{ old('ciudad', $cliente->ciudad) }}">
                            </label>
                            <label>Fecha de nacimiento
                                <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', optional($cliente->fecha_nacimiento)->format('Y-m-d') ?? $cliente->fecha_nacimiento) }}">
                            </label>
                            <label>Género
                                <input name="genero" value="{{ old('genero', $cliente->genero) }}">
                            </label>
                            <label class="cv-span-2">Objetivo principal
                                <input name="objetivo_principal" value="{{ old('objetivo_principal', $cliente->objetivo_principal) }}">
                            </label>
                        </div>

                        <div class="cv-kicker">Preferencias</div>
                        <div class="cv-form">
                            <label><input type="checkbox" name="notif_email" value="1" @checked(old('notif_email', $preferencias->notif_email))> Notificaciones por email</label>
                            <label><input type="checkbox" name="notif_push" value="1" @checked(old('notif_push', $preferencias->notif_push))> Notificaciones push</label>
                            <label><input type="checkbox" name="notif_whatsapp" value="1" @checked(old('notif_whatsapp', $preferencias->notif_whatsapp))> Notificaciones WhatsApp</label>
                            <label>Horario preferido
                                <input name="horario_preferido" value="{{ old('horario_preferido', $preferencias->horario_preferido) }}">
                            </label>
                            <label>Idioma
                                <select name="idioma">
                                    <option value="es" @selected(old('idioma', $preferencias->idioma) === 'es')>Español</option>
                                    <option value="en" @selected(old('idioma', $preferencias->idioma) === 'en')>English</option>
                                </select>
                            </label>
                        </div>
                    </div>

                    <div id="salud" style="display:grid;gap:16px;margin-bottom:20px">
                        <div class="cv-kicker">Salud y emergencia</div>
                        <div class="cv-form">
                            <label>Peso inicial (kg)
                                <input type="number" step="0.1" name="peso_inicial_kg" value="{{ old('peso_inicial_kg', $cliente->peso_inicial_kg) }}">
                            </label>
                            <label>Estatura (cm)
                                <input type="number" step="0.1" name="estatura_cm" value="{{ old('estatura_cm', $cliente->estatura_cm) }}">
                            </label>
                            <label class="cv-span-2">Alergias
                                <textarea name="alergias" rows="2">{{ old('alergias', $cliente->alergias) }}</textarea>
                            </label>
                            <label class="cv-span-2">Condiciones médicas
                                <textarea name="condiciones_medicas" rows="2">{{ old('condiciones_medicas', $cliente->condiciones_medicas) }}</textarea>
                            </label>
                            <label class="cv-span-2">Lesiones
                                <textarea name="lesiones" rows="2">{{ old('lesiones', $cliente->lesiones) }}</textarea>
                            </label>
                            <label class="cv-span-2">Medicamentos
                                <textarea name="medicamentos" rows="2">{{ old('medicamentos', $cliente->medicamentos) }}</textarea>
                            </label>
                            <label>Contacto emergencia
                                <input name="contacto_emergencia_nombre" value="{{ old('contacto_emergencia_nombre', $cliente->contacto_emergencia_nombre) }}">
                            </label>
                            <label>Tel. emergencia
                                <input name="contacto_emergencia_telefono" value="{{ old('contacto_emergencia_telefono', $cliente->contacto_emergencia_telefono) }}">
                            </label>
                        </div>
                    </div>

                    <button class="cv-btn" type="submit">Guardar cambios</button>
                </form>
            </x-cliente.card>

            <x-cliente.card title="Cambiar contraseña" subtitle="Mantén tu cuenta segura" style="margin-top:16px">
                <form id="seguridad" method="POST" action="{{ route('cliente.perfil.password') }}">
                    @csrf
                    @method('PUT')
                    <div class="cv-form">
                        <label>Contraseña actual
                            <input type="password" name="password_actual" required autocomplete="current-password">
                        </label>
                        <label>Nueva contraseña
                            <input type="password" name="password" required autocomplete="new-password">
                        </label>
                        <label>Confirmar nueva
                            <input type="password" name="password_confirmation" required autocomplete="new-password">
                        </label>
                        <div class="cv-span-2">
                            <button class="cv-btn cv-btn-cyan" type="submit">Actualizar contraseña</button>
                        </div>
                    </div>
                </form>
            </x-cliente.card>
        </div>
    </div>
@endsection
