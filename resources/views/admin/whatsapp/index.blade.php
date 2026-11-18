@extends('layouts.admin')

@section('title', 'Chats')
@section('subtitle', 'Grupos WhatsApp, invitaciones y atajo al chat Business')

@php
    $waDigits = preg_replace('/\D+/', '', (string) ($config?->numero_business ?? ''));
    $waUrl = $waDigits !== '' ? 'https://wa.me/'.$waDigits : 'https://web.whatsapp.com/';
    $grupoActivo = $grupos->firstWhere('estado', 'ACTIVO') ?? $grupos->first();
    $invRecientes = $historial->take(8);
@endphp

@section('actions')
    <span class="ad-chip {{ ($config?->activa ?? false) ? 'is-ok' : 'is-off' }}">
        {{ ($config?->activa ?? false) ? 'Canal activo' : 'Canal inactivo' }}
    </span>
    <a class="ad-btn" href="{{ $waUrl }}" target="_blank" rel="noopener">Abrir WhatsApp</a>
@endsection

@section('content')
    <section class="ad-chat-shell">
        <aside class="ad-chat-side">
            <input type="search" placeholder="Buscar grupo o cliente…" oninput="var q=this.value.toLowerCase(); this.parentElement.querySelectorAll('.ad-chat-item').forEach(function(el){el.hidden=q && el.textContent.toLowerCase().indexOf(q)<0})">
            <h4>Grupos</h4>
            <div class="ad-chat-list">
                @forelse ($grupos as $grupo)
                    <article class="ad-chat-item {{ $grupoActivo && $grupo->id_grupo_wa === $grupoActivo->id_grupo_wa ? 'is-on' : '' }}">
                        <strong>{{ $grupo->nombre }}</strong>
                        <em class="ad-user-mail">{{ $grupo->tipo }} · {{ $grupo->criterio ?: 'General' }}</em>
                    </article>
                @empty
                    <p class="ad-empty">Sin grupos. Guarda primero la configuración.</p>
                @endforelse
            </div>
            <h4>Clientes</h4>
            <div class="ad-chat-list">
                @forelse ($invRecientes as $inv)
                    <article class="ad-chat-item">
                        <strong>{{ trim(($inv->cliente?->usuario?->nombres).' '.($inv->cliente?->usuario?->apellidos)) ?: 'Cliente' }}</strong>
                        <em class="ad-user-mail">{{ $inv->aceptada_en ? 'Aceptó' : ($inv->cerrada_sin_aceptar ? 'Cerró' : 'Pendiente') }} · {{ optional($inv->mostrada_en)->format('d/m H:i') }}</em>
                    </article>
                @empty
                    <p class="ad-empty">Aún no hay invitaciones.</p>
                @endforelse
            </div>
        </aside>
        <div class="ad-chat-main">
            <header class="ad-chat-head">
                <div>
                    <strong>{{ $grupoActivo?->nombre ?? 'StayFit Business' }}</strong>
                    <em class="ad-user-mail">{{ $config?->horario_atencion ?: 'Horario de atención no definido' }}</em>
                </div>
                <div class="ad-chat-tools">
                    <a class="ad-btn ad-btn-ghost" href="{{ $waUrl }}" target="_blank" rel="noopener">Abrir WhatsApp</a>
                    @if ($grupoActivo?->link_invitacion)
                        <a class="ad-btn" href="{{ $grupoActivo->link_invitacion }}" target="_blank" rel="noopener">Entrar al grupo</a>
                    @endif
                </div>
            </header>
            <div class="ad-chat-bubbles">
                @if ($config?->mensaje_bienvenida)
                    <div class="ad-bubble out">{{ $config->mensaje_bienvenida }}</div>
                @endif
                @forelse ($invRecientes as $inv)
                    <div class="ad-bubble {{ $inv->aceptada_en ? 'in' : 'out' }}">
                        <strong>{{ $inv->cliente?->usuario?->nombres }} {{ $inv->cliente?->usuario?->apellidos }}</strong>
                        <span>{{ $inv->aceptada_en ? 'aceptó' : ($inv->cerrada_sin_aceptar ? 'cerró' : 'vio') }} la invitación {{ $inv->grupoWa?->nombre ?: 'directa' }} · {{ optional($inv->mostrada_en)->format('d/m H:i') }}</span>
                    </div>
                @empty
                    <div class="ad-bubble in">Cuando un cliente reciba el enlace de grupo, la actividad aparecerá aquí.</div>
                @endforelse
            </div>
            <form class="ad-chat-compose" action="{{ $waUrl }}" method="GET" target="_blank">
                <input type="text" name="text" placeholder="Escribe un mensaje para abrir WhatsApp…" style="flex:1">
                <button class="ad-btn" type="submit">Enviar en WhatsApp</button>
            </form>
        </div>
    </section>

    <details class="ad-glass-card" style="margin-top:16px">
        <summary style="cursor:pointer;font-weight:600">Configuración Business y grupos</summary>
        <div class="ad-grid-12" style="margin-top:16px">
            <div class="ad-col-7">
                <form class="ad-form" method="POST" action="{{ route('admin.whatsapp.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="ad-form-row">
                        <label><span>Número Business *</span><input type="text" name="numero_business" value="{{ old('numero_business', $config?->numero_business) }}" required></label>
                        <label><span>Horario de atención</span><input type="text" name="horario_atencion" value="{{ old('horario_atencion', $config?->horario_atencion) }}" placeholder="Lun–Vie 8:00–18:00"></label>
                    </div>
                    <label class="ad-check">
                        <input type="checkbox" name="activa" value="1" @checked(old('activa', $config?->activa ?? true))>
                        <span>Configuración activa</span>
                    </label>
                    <label><span>Link grupo principal</span><input type="url" name="link_grupo_principal" value="{{ old('link_grupo_principal', $config?->link_grupo_principal) }}"></label>
                    <label><span>Mensaje de bienvenida</span><textarea name="mensaje_bienvenida" rows="3">{{ old('mensaje_bienvenida', $config?->mensaje_bienvenida) }}</textarea></label>
                    <label>
                        <span>API token (webhook Meta)</span>
                        <input type="password" name="api_token" placeholder="{{ $config?->tieneApiToken() ? '•••••••• (dejar vacío para no cambiar)' : 'Token verify / app secret' }}" autocomplete="new-password">
                    </label>
                    <label>
                        <span>Respuestas automáticas (JSON)</span>
                        <textarea name="respuestas_automaticas" rows="4" placeholder='[{"trigger":"hola","respuesta":"¡Bienvenido a StayFit!"}]'>{{ old('respuestas_automaticas', $config?->respuestas_automaticas ? json_encode($config->respuestas_automaticas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
                    </label>
                    <div class="ad-form-actions">
                        <button class="ad-btn" type="submit">Guardar configuración</button>
                    </div>
                </form>
            </div>
            <div class="ad-col-5">
                @if ($config)
                    <form class="ad-form" method="POST" action="{{ route('admin.whatsapp.grupos') }}">
                        @csrf
                        <label><span>Nombre *</span><input type="text" name="nombre" required></label>
                        <label><span>Tipo *</span>
                            <select name="tipo" required>
                                <option value="HORARIO">HORARIO</option>
                                <option value="PLAN">PLAN</option>
                                <option value="GENERAL">GENERAL</option>
                            </select>
                        </label>
                        <label><span>Criterio</span><input type="text" name="criterio" placeholder="AM-8 / id_horario"></label>
                        <label><span>Link invitación *</span><input type="url" name="link_invitacion" required></label>
                        <label><span>Estado *</span>
                            <select name="estado" required>
                                <option value="ACTIVO">ACTIVO</option>
                                <option value="INACTIVO">INACTIVO</option>
                            </select>
                        </label>
                        <div class="ad-form-actions">
                            <button class="ad-btn" type="submit">Agregar grupo</button>
                        </div>
                    </form>
                @else
                    <p class="ad-empty">Guarda la configuración Business para habilitar grupos.</p>
                @endif

                <div class="ad-up-list" style="margin-top:16px">
                    @foreach ($grupos as $grupo)
                        <article class="ad-up-item">
                            <div>
                                <strong>{{ $grupo->nombre }}</strong>
                                <em class="ad-user-mail">{{ $grupo->tipo }} · {{ $grupo->estado }}</em>
                            </div>
                            @if ($grupo->estado === 'ACTIVO')
                                <form method="POST" action="{{ route('admin.whatsapp.grupos.destroy', $grupo->id_grupo_wa) }}" onsubmit="return confirm('¿Desactivar grupo?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="ad-btn ad-btn-ghost" type="submit">Desactivar</button>
                                </form>
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </details>
@endsection
