@extends('layouts.admin')

@section('title', 'WhatsApp')
@section('subtitle', 'Grupos presencial')

@section('content')
    <section class="ad-card ad-users">
        <h2>Configuración Business</h2>
        <form class="ad-form" method="POST" action="{{ route('admin.whatsapp.update') }}">
            @csrf
            @method('PUT')
            <div class="ad-form-row">
                <label><span>Número Business *</span><input type="text" name="numero_business" value="{{ old('numero_business', $config?->numero_business) }}" required></label>
                <label><span>Horario de atención</span><input type="text" name="horario_atencion" value="{{ old('horario_atencion', $config?->horario_atencion) }}"></label>
            </div>
            <label><span>Link grupo principal</span><input type="url" name="link_grupo_principal" value="{{ old('link_grupo_principal', $config?->link_grupo_principal) }}"></label>
            <label><span>Mensaje de bienvenida</span><textarea name="mensaje_bienvenida" rows="3">{{ old('mensaje_bienvenida', $config?->mensaje_bienvenida) }}</textarea></label>
            <label style="display:flex;gap:8px;align-items:center">
                <input type="checkbox" name="activa" value="1" @checked(old('activa', $config?->activa ?? true))>
                <span style="margin:0">Configuración activa</span>
            </label>
            <button class="ad-btn" type="submit">Guardar configuración</button>
        </form>
    </section>

    <section class="ad-card ad-users" style="margin-top:16px">
        <div class="ad-users-bar">
            <h2 style="margin:0;font-size:15px">Grupos</h2>
        </div>
        <table class="ad-table ad-users-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Criterio</th>
                    <th>Link</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($grupos as $grupo)
                    <tr>
                        <td><strong>{{ $grupo->nombre }}</strong></td>
                        <td>{{ $grupo->tipo }}</td>
                        <td>{{ $grupo->criterio ?: '—' }}</td>
                        <td><a class="ad-link" href="{{ $grupo->link_invitacion }}" target="_blank" rel="noopener">Invitación</a></td>
                        <td><span class="ad-badge {{ $grupo->estado === 'ACTIVO' ? 'ad-badge-ok' : 'ad-badge-bad' }}">{{ $grupo->estado }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="ad-empty">Sin grupos. Guarda primero la configuración.</td></tr>
                @endforelse
            </tbody>
        </table>

        @if ($config)
            <form class="ad-form" method="POST" action="{{ route('admin.whatsapp.grupos') }}" style="margin-top:14px">
                @csrf
                <div class="ad-form-row">
                    <label><span>Nombre *</span><input type="text" name="nombre" required></label>
                    <label><span>Tipo *</span>
                        <select name="tipo" required>
                            <option value="HORARIO">HORARIO</option>
                            <option value="PLAN">PLAN</option>
                            <option value="GENERAL">GENERAL</option>
                        </select>
                    </label>
                </div>
                <div class="ad-form-row">
                    <label><span>Criterio</span><input type="text" name="criterio" placeholder="mañana / premium"></label>
                    <label><span>Link invitación *</span><input type="url" name="link_invitacion" required></label>
                </div>
                <label><span>Estado *</span>
                    <select name="estado" required>
                        <option value="ACTIVO">ACTIVO</option>
                        <option value="INACTIVO">INACTIVO</option>
                    </select>
                </label>
                <button class="ad-btn ad-btn-ghost" type="submit">Agregar grupo</button>
            </form>
        @endif
    </section>
@endsection
