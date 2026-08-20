@extends('layouts.admin')

@section('title', 'Estadísticas')
@section('subtitle', 'Indicadores clave')

@section('content')
    @php
        $clientesActivos = (int) ($metricas?->total_clientes_activos ?? $metricas?->clientes_activos ?? $clientesPorModalidad->sum());
        $pagosValidados = (int) ($pagosPorEstado->firstWhere('estado_pago', 'VALIDADO')?->total ?? 0);
        $ingresoValidado = (float) ($pagosPorEstado->firstWhere('estado_pago', 'VALIDADO')?->monto ?? 0);
        $virtuales = (int) ($clientesPorModalidad['VIRTUAL'] ?? 0);
        $presenciales = (int) ($clientesPorModalidad['PRESENCIAL'] ?? 0);
        $mix = max(1, $virtuales + $presenciales);
        $pctPresencial = (int) round(($presenciales / $mix) * 100);
    @endphp

    <div class="ad-kpis">
        <div class="ad-kpi accent-pink">
            <span class="ad-kpi-ico">@include('partials.admin-icon', ['icon' => 'clientes'])</span>
            <div class="ad-kpi-copy">
                <strong>{{ $clientesActivos }}</strong>
                <span>Clientes activos</span>
            </div>
        </div>
        <div class="ad-kpi accent-cyan">
            <span class="ad-kpi-ico">@include('partials.admin-icon', ['icon' => 'pagos'])</span>
            <div class="ad-kpi-copy">
                <strong>{{ $solicitudesPorEstado['PENDIENTE'] ?? 0 }}</strong>
                <span>Solicitudes pendientes</span>
            </div>
        </div>
        <div class="ad-kpi accent-pink">
            <span class="ad-kpi-ico">@include('partials.admin-icon', ['icon' => 'pagos'])</span>
            <div class="ad-kpi-copy">
                <strong>{{ $pagosValidados }}</strong>
                <span>Pagos validados</span>
            </div>
        </div>
        <div class="ad-kpi accent-cyan">
            <span class="ad-kpi-ico">@include('partials.admin-icon', ['icon' => 'estadisticas'])</span>
            <div class="ad-kpi-copy">
                <strong>${{ number_format($ingresoValidado, 0, ',', '.') }}</strong>
                <span>Ingresos validados</span>
            </div>
        </div>
    </div>

    <div class="ad-split">
        <section class="ad-card ad-card-center">
            <div class="ad-card-head"><h2>Modalidad</h2></div>
            <div class="ad-donut" style="background: conic-gradient(var(--pink) 0 {{ $pctPresencial }}%, var(--cyan) {{ $pctPresencial }}% 100%);">
                <div>
                    <strong>{{ $virtuales + $presenciales }}</strong>
                    <small>clientes</small>
                </div>
            </div>
            <div class="ad-legend">
                <span><i style="background:var(--cyan)"></i> Virtual {{ $virtuales }}</span>
                <span><i style="background:var(--pink)"></i> Presencial {{ $presenciales }}</span>
            </div>
        </section>

        <section class="ad-card">
            <div class="ad-card-head"><h2>Cupos por plan</h2></div>
            @forelse ($cupos as $plan)
                @php
                    $max = max(1, (int) $plan->cupos_totales);
                    $pct = min(100, round(((int) $plan->cupos_ocupados / $max) * 100));
                @endphp
                <div class="ad-chart-row">
                    <div>
                        <strong>{{ $plan->nombre }}</strong>
                        <em>{{ $plan->cupos_ocupados }}/{{ $plan->cupos_totales }}</em>
                    </div>
                    <div class="ad-bar"><span style="width: {{ $pct }}%"></span></div>
                </div>
            @empty
                <div class="ad-empty-hero">
                    <span class="ad-empty-orb">@include('partials.admin-icon', ['icon' => 'planes'])</span>
                    <strong>Sin planes</strong>
                </div>
            @endforelse
        </section>
    </div>
@endsection
