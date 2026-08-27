@extends('layouts.cliente')

@section('title', 'Mi análisis')

@section('content')
    <x-cliente.page-head
        title="Mi análisis"
        subtitle="Actividad e indicadores personales de los últimos 30 días"
        kicker="Rendimiento"
    >
        <x-slot:actions>
            <a class="cv-btn cv-btn-ghost cv-btn-sm" href="{{ route('cliente.progreso.create') }}">Registrar progreso</a>
            <a class="cv-btn cv-btn-sm" href="{{ route('cliente.entrenamiento.index') }}">Ir a entrenar</a>
        </x-slot:actions>
    </x-cliente.page-head>

    <div class="cv-metrics">
        <x-cliente.metric label="Rutinas" :value="$kpis['rutinas_30d']" hint="Completadas (30 d)" />
        <x-cliente.metric label="Videos" :value="$kpis['videos_completados']" hint="Contenido visto" />
        <x-cliente.metric label="Progreso" :value="$kpis['registros_progreso']" hint="Registros totales" />
        <x-cliente.metric label="Racha" :value="$kpis['racha'].' d'" hint="Días consecutivos" />
    </div>

    <div class="cv-grid-2">
        <x-cliente.metric label="Pagos validados" :value="$kpis['pagos_validados']" hint="Historial aprobado" />
        <x-cliente.metric label="Mensajes enviados" :value="$mensajesEnviados" hint="Últimos 30 días" />
    </div>

    <div class="cv-grid-12">
        <div class="cv-span-6">
            <x-cliente.card title="Rutinas completadas" subtitle="Actividad diaria (30 días)">
                @if ($serieRutinas->isEmpty())
                    <p class="cv-empty">Sin actividad de entrenamiento en 30 días.</p>
                @else
                    @php $maxR = max(1, (int) $serieRutinas->max('total')); @endphp
                    <div class="cv-chart">
                        @foreach ($serieRutinas as $punto)
                            <span
                                style="height: {{ max(8, ((int) $punto->total / $maxR) * 100) }}%"
                                title="{{ $punto->fecha }}: {{ $punto->total }}"
                            ></span>
                        @endforeach
                    </div>
                    <p style="margin:10px 0 0;color:var(--cv-faint);font-size:12px">
                        Total: {{ $kpis['rutinas_30d'] }} rutinas · Pico: {{ $maxR }} / día
                    </p>
                @endif
            </x-cliente.card>
        </div>

        <div class="cv-span-6">
            <x-cliente.card title="Serie de peso" subtitle="Evolución corporal reciente">
                @if ($seriePeso->isEmpty())
                    <p class="cv-empty">Registra progreso para ver tu curva de peso.</p>
                @else
                    @php $maxP = max(1, (float) $seriePeso->max('peso_kg')); @endphp
                    <div class="cv-chart">
                        @foreach ($seriePeso as $punto)
                            <span
                                style="height: {{ max(8, ((float) $punto->peso_kg / $maxP) * 100) }}%"
                                title="{{ optional($punto->fecha_registro)->format('d/m') }}: {{ $punto->peso_kg }} kg"
                            ></span>
                        @endforeach
                    </div>
                    @php $ultimo = $seriePeso->last(); @endphp
                    <p style="margin:10px 0 0;color:var(--cv-faint);font-size:12px">
                        Último registro: {{ $ultimo->peso_kg }} kg
                        @if ($ultimo->imc)
                            · IMC {{ number_format((float) $ultimo->imc, 1) }}
                        @endif
                    </p>
                @endif
            </x-cliente.card>
        </div>
    </div>

    <x-cliente.card title="Resumen de membresía" subtitle="Estado actual de tu plan">
        <div class="cv-grid-3">
            <div class="cv-metric">
                <span>Días restantes</span>
                <strong>{{ $kpis['dias_plan'] ?? '—' }}</strong>
                <small>En tu plan vigente</small>
            </div>
            <div class="cv-metric">
                <span>Estado</span>
                <strong>{{ $cliente->estado_cliente }}</strong>
                <small>Cuenta member</small>
            </div>
            <div class="cv-metric">
                <span>Modalidad</span>
                <strong>{{ $cliente->modalidad }}</strong>
                <small>Tipo de servicio</small>
            </div>
        </div>
    </x-cliente.card>
@endsection
