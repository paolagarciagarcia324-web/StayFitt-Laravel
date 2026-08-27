@extends('layouts.institucional')

@section('title', 'Reportes')

@section('content')
    <div class="in-page">
        <section class="in-hero" style="grid-template-columns:1fr auto">
            <div>
                <div class="in-kicker">Analítica B2B</div>
                <h1 class="in-title">Reportes institucionales</h1>
                <p class="in-sub">Participación, uso del servicio y progreso agregado del convenio</p>
            </div>
            <div class="in-hero__actions">
                <a class="in-btn" href="{{ route('institucional.reportes.export') }}">Exportar CSV</a>
            </div>
        </section>

        <div class="in-metrics">
            <x-institucional.metric label="Total miembros" :value="$reporte['total_miembros']" />
            <x-institucional.metric label="Activos" :value="$reporte['activos']" />
            <x-institucional.metric label="Participación" :value="$reporte['participacion_pct'].'%'" hint="Con actividad registrada" />
            <x-institucional.metric label="Progresos" :value="$reporte['progresos_registrados']" />
            <x-institucional.metric label="Suspendidos" :value="$reporte['suspendidos']" />
        </div>

        <div class="in-grid-12">
            <div class="in-span-7">
                <x-institucional.card title="Participación mensual">
                    @php $part = $reporte['participacion_mensual']; @endphp
                    @if (empty($part['values']) || max($part['values']) === 0)
                        <p class="in-empty">Sin datos suficientes.</p>
                    @else
                        @php $max = max(1, max($part['values'])); @endphp
                        <div class="in-chart-bars">
                            @foreach ($part['labels'] as $i => $label)
                                <div class="in-chart-bar">
                                    <div style="height: {{ round(($part['values'][$i] / $max) * 100) }}%"></div>
                                    <span>{{ $label }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-institucional.card>
            </div>
            <div class="in-span-5">
                <x-institucional.card title="Por modalidad">
                    @forelse ($reporte['por_modalidad'] as $mod => $count)
                        <div class="in-invite-row">
                            <strong>{{ $mod ?: 'Sin modalidad' }}</strong>
                            <span class="in-badge is-blue">{{ $count }}</span>
                        </div>
                    @empty
                        <p class="in-empty">Sin miembros.</p>
                    @endforelse
                </x-institucional.card>

                <x-institucional.card title="Resumen de uso" style="margin-top:16px">
                    <div class="in-info-pill" style="margin-bottom:8px">
                        <span>Miembros con actividad</span>
                        <strong>{{ $reporte['miembros_con_actividad'] }} de {{ $reporte['activos'] }} activos</strong>
                    </div>
                    <div class="in-progress"><span style="width:{{ min(100, $reporte['participacion_pct']) }}%"></span></div>
                </x-institucional.card>
            </div>
        </div>
    </div>
@endsection
