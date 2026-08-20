@extends('layouts.admin')

@section('title', 'Detalle cliente')
@section('subtitle', trim(($cliente->usuario?->nombres).' '.($cliente->usuario?->apellidos)))

@section('actions')
    <a class="ad-btn ad-btn-ghost" href="{{ $cliente->modalidad === 'PRESENCIAL' ? route('admin.clientes.presenciales') : route('admin.clientes.virtuales') }}">Volver</a>
@endsection

@section('content')
    <section class="ad-card ad-users">
        <div class="ad-users-bar">
            <div style="display:flex;gap:12px;align-items:center">
                @include('partials.admin-avatar', ['user' => $cliente->usuario])
                <div>
                    <strong>{{ $cliente->usuario?->nombres }} {{ $cliente->usuario?->apellidos }}</strong>
                    <em class="ad-user-mail">{{ $cliente->usuario?->correo }}</em>
                </div>
            </div>
            <span class="ad-badge {{ $cliente->modalidad === 'PRESENCIAL' ? 'ad-badge-ok' : 'ad-badge-pendiente' }}">{{ $cliente->modalidad }}</span>
        </div>

        <form class="ad-form" method="POST" action="{{ route('admin.clientes.update', $cliente->id_cliente) }}">
            @csrf
            @method('PUT')
            <div class="ad-form-row">
                <label><span>Ciudad</span><input type="text" name="ciudad" value="{{ old('ciudad', $cliente->ciudad) }}"></label>
                <label><span>Teléfono</span><input type="text" name="telefono" value="{{ old('telefono', $cliente->usuario?->telefono) }}"></label>
            </div>
            <div class="ad-form-row">
                <label><span>Objetivo</span><input type="text" name="objetivo_principal" value="{{ old('objetivo_principal', $cliente->objetivo_principal) }}"></label>
                <label><span>Estado *</span>
                    <select name="estado_cliente" required>
                        @foreach (['PROSPECTO','PENDIENTE_PAGO','ACTIVO','ACTIVA','INACTIVO','SUSPENDIDO'] as $e)
                            <option value="{{ $e }}" @selected(old('estado_cliente', $cliente->estado_cliente) === $e)>{{ $e }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <div class="ad-modal-actions">
                <button class="ad-btn" type="submit">Guardar</button>
            </div>
        </form>
    </section>

    <section class="ad-card ad-users" style="margin-top:16px">
        <div class="ad-card-head">
            <h2>Coach</h2>
            <span class="ad-pill-days">{{ trim(($cliente->coach?->usuario?->nombres).' '.($cliente->coach?->usuario?->apellidos)) ?: 'Sin asignar' }}</span>
        </div>
        <form class="ad-form" method="POST" action="{{ route('admin.clientes.coach', $cliente->id_cliente) }}">
            @csrf
            <div class="ad-form-row">
                <label><span>Coach *</span>
                    <select name="id_coach" required>
                        @foreach ($coaches as $coach)
                            <option value="{{ $coach->id_coach }}" @selected((int) $cliente->id_coach === (int) $coach->id_coach)>
                                {{ $coach->usuario?->nombres }} {{ $coach->usuario?->apellidos }} — {{ $coach->especialidad }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <label><span>Motivo</span><input type="text" name="motivo" value="{{ old('motivo') }}"></label>
            </div>
            <button class="ad-btn" type="submit">Asignar</button>
        </form>
    </section>

    <section class="ad-card ad-users" style="margin-top:16px">
        <div class="ad-card-head"><h2>Accesos</h2></div>
        <table class="ad-table ad-users-table">
            <thead>
                <tr>
                    <th>Módulo</th>
                    <th>Vigencia</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($cliente->accesos as $acceso)
                    <tr>
                        <td>{{ $acceso->modulo?->nombre ?: ('#'.$acceso->id_modulo) }}</td>
                        <td>{{ $acceso->fecha_inicio }} → {{ $acceso->fecha_fin ?: '—' }}</td>
                        <td><span class="ad-badge {{ $acceso->estado_acceso === 'ACTIVO' ? 'ad-badge-ok' : 'ad-badge-pendiente' }}">{{ $acceso->estado_acceso }}</span></td>
                        <td>
                            <form class="ad-form" method="POST" action="{{ route('admin.clientes.acceso', [$cliente->id_cliente, $acceso->id_acceso]) }}">
                                @csrf
                                @method('PUT')
                                <div class="ad-form-row">
                                    <label><span>Estado</span>
                                        <select name="estado_acceso">
                                            @foreach (['ACTIVO','SUSPENDIDO','VENCIDO','INACTIVO'] as $e)
                                                <option value="{{ $e }}" @selected($acceso->estado_acceso === $e)>{{ $e }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label><span>Fecha fin</span><input type="date" name="fecha_fin" value="{{ $acceso->fecha_fin ? \Illuminate\Support\Carbon::parse($acceso->fecha_fin)->format('Y-m-d') : '' }}"></label>
                                </div>
                                <button class="ad-btn ad-btn-ghost" type="submit">Actualizar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <div class="ad-empty-hero" style="padding:24px">
                                <span class="ad-empty-orb">@include('partials.admin-icon', ['icon' => 'planes'])</span>
                                <strong>Sin accesos</strong>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
