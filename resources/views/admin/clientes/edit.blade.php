@extends('layouts.admin')

@php
    $u = $cliente->usuario;
    $nombre = trim(($u?->nombres).' '.($u?->apellidos)) ?: 'Cliente';
    $listRoute = $cliente->modalidad === 'PRESENCIAL'
        ? route('admin.clientes.presenciales')
        : route('admin.clientes.virtuales');
@endphp

@section('title', 'Editar cliente')
@section('subtitle')
    <span class="ad-page-intro">
        <span class="ad-page-intro-lead">{{ $nombre }}</span>
        <span class="ad-page-intro-note">Actualiza perfil, coach y accesos de módulo.</span>
    </span>
@endsection

@section('actions')
    <a class="ad-btn ad-btn-ghost" href="{{ $listRoute }}">Volver</a>
    <a class="ad-btn ad-btn-ghost" href="{{ route('admin.clientes.show', $cliente->id_cliente) }}">Ver ficha</a>
@endsection

@section('content')
    <section class="ad-cli-profile is-edit">
        <header class="ad-cli-hero">
            <div class="ad-cli-hero-main">
                @include('partials.admin-avatar', ['user' => $u])
                <div class="ad-cli-hero-text">
                    <strong>{{ $nombre }}</strong>
                    <em>{{ $u?->correo ?: 'Sin correo' }}</em>
                    <div class="ad-cli-badges">
                        <span class="ad-badge {{ $cliente->modalidad === 'PRESENCIAL' ? 'ad-badge-ok' : 'ad-badge-pendiente' }}">{{ $cliente->modalidad }}</span>
                        <span class="ad-badge ad-badge-pendiente">Editando</span>
                    </div>
                </div>
            </div>
        </header>

        <section class="ad-cli-block">
            <header class="ad-cli-block-head">
                <h2>Perfil</h2>
                <span>Datos de cuenta</span>
            </header>
            <form class="ad-form ad-cli-form" method="POST" action="{{ route('admin.clientes.update', $cliente->id_cliente) }}">
                @csrf
                @method('PUT')
                <div class="ad-cli-form-grid">
                    <label>
                        <span class="ad-cli-label">@include('partials.admin-icon', ['icon' => 'instituciones']) Ciudad</span>
                        <input type="text" name="ciudad" value="{{ old('ciudad', $cliente->ciudad) }}" placeholder="Ciudad">
                    </label>
                    <label>
                        <span class="ad-cli-label">@include('partials.admin-icon', ['icon' => 'phone']) Teléfono</span>
                        <input type="text" name="telefono" value="{{ old('telefono', $u?->telefono) }}" placeholder="Teléfono">
                    </label>
                    <label>
                        <span class="ad-cli-label">@include('partials.admin-icon', ['icon' => 'target']) Objetivo</span>
                        <input type="text" name="objetivo_principal" value="{{ old('objetivo_principal', $cliente->objetivo_principal) }}" placeholder="Objetivo principal">
                    </label>
                    <label>
                        <span class="ad-cli-label">@include('partials.admin-icon', ['icon' => 'clientes']) Estado *</span>
                        <select name="estado_cliente" required>
                            @foreach (['PROSPECTO','PENDIENTE_PAGO','ACTIVO','ACTIVA','INACTIVO','SUSPENDIDO'] as $e)
                                <option value="{{ $e }}" @selected(old('estado_cliente', $cliente->estado_cliente) === $e)>{{ $e }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
                <div class="ad-cli-form-actions">
                    <button class="ad-btn" type="submit">Guardar perfil</button>
                </div>
            </form>
        </section>

        <section class="ad-cli-block">
            <header class="ad-cli-block-head">
                <h2>Coach</h2>
                <span>{{ trim(($cliente->coach?->usuario?->nombres).' '.($cliente->coach?->usuario?->apellidos)) ?: 'Sin asignar' }}</span>
            </header>
            <form class="ad-form ad-cli-form" method="POST" action="{{ route('admin.clientes.coach', $cliente->id_cliente) }}">
                @csrf
                <div class="ad-cli-form-grid">
                    <label>
                        <span class="ad-cli-label">@include('partials.admin-icon', ['icon' => 'coaches']) Coach *</span>
                        <select name="id_coach" required>
                            @forelse ($coaches as $coach)
                                <option value="{{ $coach->id_coach }}" @selected((int) $cliente->id_coach === (int) $coach->id_coach || (int) $cliente->id_coach_asignado === (int) $coach->id_coach)>
                                    {{ $coach->usuario?->nombres }} {{ $coach->usuario?->apellidos }} — {{ $coach->especialidad }}
                                </option>
                            @empty
                                <option value="" disabled selected>No hay coaches activos</option>
                            @endforelse
                        </select>
                    </label>
                    <label>
                        <span class="ad-cli-label">@include('partials.admin-icon', ['icon' => 'edit']) Motivo</span>
                        <input type="text" name="motivo" value="{{ old('motivo') }}" placeholder="Opcional">
                    </label>
                </div>
                <div class="ad-cli-form-actions">
                    <button class="ad-btn" type="submit" @if ($coaches->isEmpty()) disabled @endif>Asignar coach</button>
                </div>
            </form>
        </section>

        <section class="ad-cli-block">
            <header class="ad-cli-block-head">
                <h2>Accesos</h2>
                <span>{{ $cliente->accesos->count() }} módulo(s)</span>
            </header>
            @forelse ($cliente->accesos as $acceso)
                <form class="ad-form ad-cli-access-edit" method="POST" action="{{ route('admin.clientes.acceso', [$cliente->id_cliente, $acceso->id_acceso]) }}">
                    @csrf
                    @method('PUT')
                    <div class="ad-cli-access-edit-head">
                        <strong>{{ $acceso->modulo?->nombre ?: ('#'.$acceso->id_modulo) }}</strong>
                        <em>{{ $acceso->fecha_inicio ?: '—' }} → {{ $acceso->fecha_fin ?: '—' }}</em>
                    </div>
                    <div class="ad-cli-form-grid is-access">
                        <label>
                            <span>Estado</span>
                            <select name="estado_acceso" required>
                                @foreach (['ACTIVO','SUSPENDIDO','VENCIDO','INACTIVO'] as $e)
                                    <option value="{{ $e }}" @selected($acceso->estado_acceso === $e)>{{ $e }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            <span>Fecha fin</span>
                            <input type="date" name="fecha_fin" value="{{ $acceso->fecha_fin ? \Illuminate\Support\Carbon::parse($acceso->fecha_fin)->format('Y-m-d') : '' }}">
                        </label>
                        <div class="ad-cli-form-actions is-inline">
                            <button class="ad-btn ad-btn-ghost" type="submit">Actualizar</button>
                        </div>
                    </div>
                </form>
            @empty
                <div class="ad-empty-hero" style="padding:28px">
                    <span class="ad-empty-orb">@include('partials.admin-icon', ['icon' => 'planes'])</span>
                    <strong>Sin accesos</strong>
                </div>
            @endforelse
        </section>
    </section>
@endsection
