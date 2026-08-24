@extends('layouts.admin')

@php
    $u = $cliente->usuario;
    $nombre = trim(($u?->nombres).' '.($u?->apellidos)) ?: 'Cliente';
    $planActivo = $cliente->planActivo();
    $diasPlan = $cliente->diasRestantesPlan();
    $listRoute = $cliente->modalidad === 'PRESENCIAL'
        ? route('admin.clientes.presenciales')
        : route('admin.clientes.virtuales');
@endphp

@section('title', 'Ficha cliente')
@section('subtitle')
    <span class="ad-page-intro">
        <span class="ad-page-intro-lead">{{ $nombre }}</span>
        <span class="ad-page-intro-note">Solo lectura. Usa editar para cambiar datos, coach o accesos.</span>
    </span>
@endsection

@section('actions')
    <a class="ad-btn ad-btn-ghost" href="{{ $listRoute }}">Volver</a>
    <a class="ad-btn" href="{{ route('admin.clientes.edit', $cliente->id_cliente) }}">Editar</a>
@endsection

@section('content')
    <section class="ad-cli-profile">
        <header class="ad-cli-hero">
            <div class="ad-cli-hero-main">
                @include('partials.admin-avatar', ['user' => $u])
                <div class="ad-cli-hero-text">
                    <strong>{{ $nombre }}</strong>
                    <em>{{ $u?->correo ?: 'Sin correo' }}</em>
                    <div class="ad-cli-badges">
                        <span class="ad-badge {{ $cliente->modalidad === 'PRESENCIAL' ? 'ad-badge-ok' : 'ad-badge-pendiente' }}">{{ $cliente->modalidad }}</span>
                        <span class="ad-badge {{ in_array($cliente->estado_cliente, ['ACTIVA', 'ACTIVO']) ? 'ad-badge-ok' : 'ad-badge-pendiente' }}">{{ $cliente->estado_cliente }}</span>
                        @if ($cliente->tipo_cliente)
                            <span class="ad-badge ad-badge-bad">{{ $cliente->tipo_cliente }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <a class="ad-icon-btn" href="{{ route('admin.clientes.edit', $cliente->id_cliente) }}" title="Editar" aria-label="Editar cliente">
                @include('partials.admin-icon', ['icon' => 'edit'])
            </a>
        </header>

        <div class="ad-cli-grid">
            <article class="ad-cli-stat">
                <span class="ad-cli-stat-ico" aria-hidden="true">@include('partials.admin-icon', ['icon' => 'phone'])</span>
                <div>
                    <em>Teléfono</em>
                    <strong>{{ $u?->telefono ?: '—' }}</strong>
                </div>
            </article>
            <article class="ad-cli-stat">
                <span class="ad-cli-stat-ico" aria-hidden="true">@include('partials.admin-icon', ['icon' => 'instituciones'])</span>
                <div>
                    <em>Ciudad</em>
                    <strong>{{ $cliente->ciudad ?: '—' }}</strong>
                </div>
            </article>
            <article class="ad-cli-stat">
                <span class="ad-cli-stat-ico" aria-hidden="true">@include('partials.admin-icon', ['icon' => 'target'])</span>
                <div>
                    <em>Objetivo</em>
                    <strong>{{ $cliente->objetivo_principal ?: '—' }}</strong>
                </div>
            </article>
            <article class="ad-cli-stat">
                <span class="ad-cli-stat-ico" aria-hidden="true">@include('partials.admin-icon', ['icon' => 'planes'])</span>
                <div>
                    <em>Plan</em>
                    <strong>{{ $planActivo?->plan?->nombre ?? '—' }}</strong>
                </div>
            </article>
            <article class="ad-cli-stat">
                <span class="ad-cli-stat-ico is-pink" aria-hidden="true">@include('partials.admin-icon', ['icon' => 'fecha'])</span>
                <div>
                    <em>Racha</em>
                    <strong>{{ $cliente->diasRacha() }} días</strong>
                </div>
            </article>
            <article class="ad-cli-stat">
                <span class="ad-cli-stat-ico" aria-hidden="true">@include('partials.admin-icon', ['icon' => 'clock'])</span>
                <div>
                    <em>Días de plan</em>
                    <strong>
                        @if ($diasPlan === null)
                            —
                        @elseif ($diasPlan < 0)
                            Vencido
                        @else
                            {{ $diasPlan }} días
                        @endif
                    </strong>
                </div>
            </article>
            @if ($cliente->modalidad === 'VIRTUAL')
                <article class="ad-cli-stat">
                    <span class="ad-cli-stat-ico" aria-hidden="true">@include('partials.admin-icon', ['icon' => 'instituciones'])</span>
                    <div>
                        <em>Institución</em>
                        <strong>{{ $cliente->institucion?->nombre ?? '—' }}</strong>
                    </div>
                </article>
            @else
                <article class="ad-cli-stat">
                    <span class="ad-cli-stat-ico" aria-hidden="true">@include('partials.admin-icon', ['icon' => 'clock'])</span>
                    <div>
                        <em>Horario</em>
                        <strong>{{ $cliente->horario?->nombre ?? '—' }}</strong>
                    </div>
                </article>
                <article class="ad-cli-stat">
                    <span class="ad-cli-stat-ico" aria-hidden="true">@include('partials.admin-icon', ['icon' => 'fecha'])</span>
                    <div>
                        <em>Nacimiento</em>
                        <strong>{{ $cliente->fecha_nacimiento ? \Illuminate\Support\Carbon::parse($cliente->fecha_nacimiento)->format('d/m/Y') : '—' }}</strong>
                    </div>
                </article>
            @endif
            <article class="ad-cli-stat">
                <span class="ad-cli-stat-ico is-pink" aria-hidden="true">@include('partials.admin-icon', ['icon' => 'coaches'])</span>
                <div>
                    <em>Coach</em>
                    <strong>{{ trim(($cliente->coach?->usuario?->nombres).' '.($cliente->coach?->usuario?->apellidos)) ?: 'Sin asignar' }}</strong>
                </div>
            </article>
        </div>

        <section class="ad-cli-block">
            <header class="ad-cli-block-head">
                <h2>Accesos</h2>
                <span>{{ $cliente->accesos->count() }} módulo(s)</span>
            </header>
            @forelse ($cliente->accesos as $acceso)
                <div class="ad-cli-access">
                    <div>
                        <strong>{{ $acceso->modulo?->nombre ?: ('#'.$acceso->id_modulo) }}</strong>
                        <em>{{ $acceso->fecha_inicio ?: '—' }} → {{ $acceso->fecha_fin ?: '—' }}</em>
                    </div>
                    <span class="ad-badge {{ $acceso->estado_acceso === 'ACTIVO' ? 'ad-badge-ok' : 'ad-badge-pendiente' }}">{{ $acceso->estado_acceso }}</span>
                </div>
            @empty
                <div class="ad-empty-hero" style="padding:28px">
                    <span class="ad-empty-orb">@include('partials.admin-icon', ['icon' => 'planes'])</span>
                    <strong>Sin accesos</strong>
                </div>
            @endforelse
        </section>
    </section>
@endsection
