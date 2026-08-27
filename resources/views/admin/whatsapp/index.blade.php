@extends('layouts.admin')

@section('title', 'WhatsApp')
@section('subtitle', 'Configuración Business, grupos e invitaciones')

@section('actions')
    <span class="ad-chip {{ ($config?->activa ?? false) ? 'is-ok' : 'is-off' }}">
        {{ ($config?->activa ?? false) ? 'Conexión activa' : 'Configuración inactiva' }}
    </span>
@endsection

@section('content')
    <div class="ad-status-rail">
        <x-admin.stat label="Número Business" :value="$config?->numero_business ?: '—'" tone="cyan" />
        <x-admin.stat label="Grupos" :value="$grupos->count()" tone="pink" />
        <x-admin.stat label="Invitaciones" :value="$historial->count()" />
        <x-admin.stat
            label="Estado"
            :value="($config?->activa ?? false) ? 'ACTIVA' : 'OFF'"
            :tone="($config?->activa ?? false) ? 'cyan' : 'pink'"
        />
    </div>

    <div class="ad-grid-12">
        <div class="ad-col-7">
            <x-admin.panel title="Configuración general" subtitle="Datos del canal Business y atención">
                <form class="ad-form" method="POST" action="{{ route('admin.whatsapp.update') }}">
                    @csrf
                    @method('PUT')

                    <details class="ad-form-section" open>
                        <summary>Información principal</summary>
                        <div class="ad-form-section__body">
                            <div class="ad-form-row">
                                <label><span>Número Business *</span><input type="text" name="numero_business" value="{{ old('numero_business', $config?->numero_business) }}" required></label>
                                <label><span>Horario de atención</span><input type="text" name="horario_atencion" value="{{ old('horario_atencion', $config?->horario_atencion) }}" placeholder="Lun–Vie 8:00–18:00"></label>
                            </div>
                            <label class="ad-check">
                                <input type="checkbox" name="activa" value="1" @checked(old('activa', $config?->activa ?? true))>
                                <span>Configuración activa</span>
                            </label>
                        </div>
                    </details>

                    <details class="ad-form-section" open>
                        <summary>Grupo principal y bienvenida</summary>
                        <div class="ad-form-section__body">
                            <label><span>Link grupo principal</span><input type="url" name="link_grupo_principal" value="{{ old('link_grupo_principal', $config?->link_grupo_principal) }}"></label>
                            <label><span>Mensaje de bienvenida</span><textarea name="mensaje_bienvenida" rows="3">{{ old('mensaje_bienvenida', $config?->mensaje_bienvenida) }}</textarea></label>
                        </div>
                    </details>

                    <details class="ad-form-section">
                        <summary>Integración y automatizaciones</summary>
                        <div class="ad-form-section__body">
                            <label>
                                <span>API token (webhook Meta)</span>
                                <input type="password" name="api_token" placeholder="{{ $config?->tieneApiToken() ? '•••••••• (dejar vacío para no cambiar)' : 'Token verify / app secret' }}" autocomplete="new-password">
                            </label>
                            <label>
                                <span>Respuestas automáticas (JSON)</span>
                                <textarea name="respuestas_automaticas" rows="4" placeholder='[{"trigger":"hola","respuesta":"¡Bienvenido a FigueFit!"}]'>{{ old('respuestas_automaticas', $config?->respuestas_automaticas ? json_encode($config->respuestas_automaticas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
                            </label>
                        </div>
                    </details>

                    <div class="ad-form-actions">
                        <button class="ad-btn" type="submit">Guardar configuración</button>
                    </div>
                </form>
            </x-admin.panel>
        </div>

        <div class="ad-col-5">
            <x-admin.panel title="Actividad reciente" subtitle="Últimas invitaciones registradas" flush>
                <div class="ad-table-wrap">
                    <table class="ad-table ad-users-table ad-table-compact">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($historial->take(8) as $inv)
                                <tr>
                                    <td>
                                        <strong>{{ $inv->cliente?->usuario?->nombres }} {{ $inv->cliente?->usuario?->apellidos }}</strong>
                                        <em class="ad-user-mail">{{ $inv->grupoWa?->nombre ?: 'Link directo' }}</em>
                                    </td>
                                    <td>
                                        @if ($inv->aceptada_en)
                                            <span class="ad-badge ad-badge-ok">Aceptada</span>
                                        @elseif ($inv->cerrada_sin_aceptar)
                                            <span class="ad-badge">Cerrada</span>
                                        @else
                                            <span class="ad-badge ad-badge-bad">Pendiente</span>
                                        @endif
                                    </td>
                                    <td>{{ optional($inv->mostrada_en)->format('d/m H:i') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="ad-empty">Sin invitaciones aún.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-admin.panel>
        </div>
    </div>

    <div class="ad-grid-12">
        <div class="ad-col-8">
            <x-admin.panel title="Grupos WhatsApp" subtitle="Horario / plan / general" flush>
                <div class="ad-table-wrap">
                    <table class="ad-table ad-users-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Criterio</th>
                                <th>Estado</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($grupos as $grupo)
                                <tr>
                                    <td>
                                        <strong>{{ $grupo->nombre }}</strong>
                                        <em class="ad-user-mail"><a class="ad-link" href="{{ $grupo->link_invitacion }}" target="_blank" rel="noopener">Invitación</a></em>
                                    </td>
                                    <td>{{ $grupo->tipo }}</td>
                                    <td>{{ $grupo->criterio ?: '—' }}</td>
                                    <td><span class="ad-badge {{ $grupo->estado === 'ACTIVO' ? 'ad-badge-ok' : 'ad-badge-bad' }}">{{ $grupo->estado }}</span></td>
                                    <td>
                                        <div class="ad-row-actions">
                                            @if ($grupo->estado === 'ACTIVO')
                                                <details>
                                                    <summary class="ad-link" style="cursor:pointer">Editar</summary>
                                                    <form class="ad-form" method="POST" action="{{ route('admin.whatsapp.grupos.update', $grupo->id_grupo_wa) }}" style="margin-top:8px;min-width:220px">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="text" name="nombre" value="{{ $grupo->nombre }}" required>
                                                        <input type="text" name="criterio" value="{{ $grupo->criterio }}">
                                                        <input type="url" name="link_invitacion" value="{{ $grupo->link_invitacion }}" required>
                                                        <select name="tipo" required>
                                                            <option value="HORARIO" @selected($grupo->tipo === 'HORARIO')>HORARIO</option>
                                                            <option value="PLAN" @selected($grupo->tipo === 'PLAN')>PLAN</option>
                                                            <option value="GENERAL" @selected($grupo->tipo === 'GENERAL')>GENERAL</option>
                                                        </select>
                                                        <select name="estado" required>
                                                            <option value="ACTIVO" @selected($grupo->estado === 'ACTIVO')>ACTIVO</option>
                                                            <option value="INACTIVO" @selected($grupo->estado === 'INACTIVO')>INACTIVO</option>
                                                        </select>
                                                        <button class="ad-btn ad-btn-ghost" type="submit">Guardar</button>
                                                    </form>
                                                </details>
                                                <form method="POST" action="{{ route('admin.whatsapp.grupos.destroy', $grupo->id_grupo_wa) }}" onsubmit="return confirm('¿Desactivar grupo?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="ad-btn ad-btn-ghost" type="submit">Desactivar</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="ad-empty">Sin grupos. Guarda primero la configuración.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-admin.panel>
        </div>

        <div class="ad-col-4">
            <x-admin.panel title="Nuevo grupo" subtitle="Alta rápida">
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
            </x-admin.panel>
        </div>
    </div>

    <x-admin.panel title="Historial completo de invitaciones" subtitle="Auditoría de enlaces mostrados a clientes" flush>
        <div class="ad-table-wrap">
            <table class="ad-table ad-users-table">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Grupo</th>
                        <th>Mostrada</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($historial as $inv)
                        <tr>
                            <td>{{ $inv->cliente?->usuario?->nombres }} {{ $inv->cliente?->usuario?->apellidos }}</td>
                            <td>{{ $inv->grupoWa?->nombre ?: 'Link directo' }}</td>
                            <td>{{ optional($inv->mostrada_en)->format('d/m/Y H:i') }}</td>
                            <td>
                                @if ($inv->aceptada_en)
                                    <span class="ad-badge ad-badge-ok">Aceptada</span>
                                @elseif ($inv->cerrada_sin_aceptar)
                                    <span class="ad-badge">Cerrada</span>
                                @else
                                    <span class="ad-badge ad-badge-bad">Pendiente</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="ad-empty">Sin invitaciones registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.panel>
@endsection
