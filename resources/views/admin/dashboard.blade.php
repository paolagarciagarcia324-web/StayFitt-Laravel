@extends('layouts.admin')

@section('title', 'Dashboard')
@section('subtitle', 'Operación diaria')

@php
    $virtuales = (int) ($metricas->clientes_virtuales ?? 0);
    $presenciales = (int) ($metricas->clientes_presenciales ?? 0);
    $mix = max(1, $virtuales + $presenciales);
    $pctVirtual = (int) round(($virtuales / $mix) * 100);
    $pctPresencial = 100 - $pctVirtual;
@endphp

@section('content')
    <div class="ad-kpis">
        <a class="ad-kpi accent-pink" href="{{ route('admin.clientes.virtuales') }}">
            <span class="ad-kpi-ico">@include('partials.admin-icon', ['icon' => 'clientes'])</span>
            <div class="ad-kpi-copy">
                <strong>{{ $metricas->total_clientes_activos ?? 0 }}</strong>
                <span>Clientes activos</span>
            </div>
            @include('partials.admin-spark', ['values' => $sparks['clientes'] ?? [], 'color' => '#ff2d78'])
        </a>
        <a class="ad-kpi accent-cyan" href="{{ route('admin.pagos.index') }}">
            <span class="ad-kpi-ico">@include('partials.admin-icon', ['icon' => 'pagos'])</span>
            <div class="ad-kpi-copy">
                <strong>${{ number_format((float) ($metricas->ingreso_mes_actual ?? 0), 0, ',', '.') }}</strong>
                <span>Ingreso del mes</span>
            </div>
            @include('partials.admin-spark', ['values' => $sparks['ingresos'] ?? [], 'color' => '#2ef5e6'])
        </a>
        <a class="ad-kpi accent-pink" href="{{ route('admin.planes.index') }}">
            <span class="ad-kpi-ico">@include('partials.admin-icon', ['icon' => 'planes'])</span>
            <div class="ad-kpi-copy">
                <strong>{{ $planesVendidos ?? 0 }}</strong>
                <span>Planes vendidos</span>
            </div>
            @include('partials.admin-spark', ['values' => $sparks['planes'] ?? [], 'color' => '#ff2d78'])
        </a>
        <a class="ad-kpi accent-cyan" href="{{ route('admin.tienda.index') }}">
            <span class="ad-kpi-ico">@include('partials.admin-icon', ['icon' => 'tienda'])</span>
            <div class="ad-kpi-copy">
                <strong>{{ $productosVendidos ?? 0 }}</strong>
                <span>Productos vendidos</span>
            </div>
            @include('partials.admin-spark', ['values' => $sparks['productos'] ?? [], 'color' => '#2ef5e6'])
        </a>
    </div>

    <div class="ad-split">
        <section class="ad-card">
            <div class="ad-card-head">
                <h2>Ocupación de horarios</h2>
                <a class="ad-btn ad-btn-ghost" href="{{ route('admin.horarios.index') }}">Ver</a>
            </div>
            @forelse ($horarios as $horario)
                @php
                    $max = max(1, (int) $horario->cupo_maximo);
                    $pct = min(100, round(((int) $horario->cupo_ocupado / $max) * 100));
                @endphp
                <div class="ad-chart-row">
                    <div>
                        <strong>{{ $horario->nombre }}</strong>
                        <em>{{ $horario->cupo_ocupado }}/{{ $horario->cupo_maximo }}</em>
                    </div>
                    <div class="ad-bar"><span style="width: {{ $pct }}%"></span></div>
                </div>
            @empty
                <p class="ad-empty">Aún no hay horarios presenciales.</p>
            @endforelse
        </section>

        <section class="ad-card ad-card-center">
            <div class="ad-card-head">
                <h2>Modalidad</h2>
            </div>
            <div class="ad-donut" style="background: conic-gradient(var(--pink) 0 {{ $pctPresencial }}%, var(--cyan) {{ $pctPresencial }}% 100%);">
                <div>
                    <strong>{{ $virtuales + $presenciales }}</strong>
                    <small>clientes</small>
                </div>
            </div>
            <ul class="ad-legend">
                <li><i class="pink"></i> Presencial {{ $presenciales }}</li>
                <li><i class="cyan"></i> Virtual {{ $virtuales }}</li>
            </ul>
        </section>
    </div>

    <div class="ad-triple">
        <section class="ad-card">
            <div class="ad-card-head">
                <h2>Pedidos tienda</h2>
                <a class="ad-btn ad-btn-ghost" href="{{ route('admin.tienda.index') }}">Ver</a>
            </div>
            @forelse ($ordenes as $orden)
                <div class="ad-list-row">
                    <div>
                        <strong>{{ $orden->numero_orden }}</strong>
                        <em>{{ $orden->cliente?->usuario?->nombres }} {{ $orden->cliente?->usuario?->apellidos }}</em>
                    </div>
                    <span>${{ number_format((float) $orden->total, 0, ',', '.') }}</span>
                </div>
            @empty
                <p class="ad-empty">Sin pedidos.</p>
            @endforelse
        </section>

        <section class="ad-card">
            <div class="ad-card-head">
                <h2>Próximos eventos</h2>
                <a class="ad-btn ad-btn-ghost" href="{{ route('admin.eventos.index') }}">Ver</a>
            </div>
            @forelse ($proximosEventos as $evento)
                <div class="ad-list-row">
                    <div>
                        <strong>{{ $evento->nombre }}</strong>
                        <em>{{ optional($evento->fecha_inicio)->format('d/m H:i') }} · {{ $evento->modalidad }}</em>
                    </div>
                    <span>{{ (int) $evento->cupo_inscritos }}/{{ $evento->cupo_maximo ?: '—' }}</span>
                </div>
            @empty
                <p class="ad-empty">No hay eventos próximos. Ábrelos desde el calendario.</p>
                <a class="ad-btn ad-btn-ghost" href="{{ route('admin.calendario.index') }}">Ir al calendario</a>
            @endforelse
        </section>

        <section class="ad-card">
            <div class="ad-card-head">
                <h2>Atajos</h2>
            </div>
            <div class="ad-quick">
                <a href="{{ route('admin.clientes.virtuales') }}">Clientes virtuales <small>ver</small></a>
                <a href="{{ route('admin.planes.create') }}">Nuevo plan <small>+</small></a>
                <a href="{{ route('admin.tienda.index') }}">Tienda <small>stock</small></a>
                <a href="{{ route('admin.pasarelas.index') }}">Pasarelas <small>config</small></a>
            </div>
        </section>
    </div>
@endsection
