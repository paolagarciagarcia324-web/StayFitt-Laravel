@extends('layouts.institucional')

@section('title', 'Plan institucional')

@section('content')
    <div class="in-page">
        <section class="in-hero" style="grid-template-columns:1fr">
            <div>
                <div class="in-kicker">Convenio comercial</div>
                <h1 class="in-title">Plan contratado</h1>
                <p class="in-sub">Beneficios, cupos y renovación del convenio con FigueFit</p>
            </div>
        </section>

        <div class="in-convenio-banner">
            <div>
                <strong style="font-size:18px">{{ $plan?->nombre ?? 'Plan por definir' }}</strong>
                <div style="font-size:12px;color:var(--in-muted);margin-top:4px">
                    {{ $institucion->convenio ?? 'Convenio institucional' }}
                </div>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <span class="in-badge {{ $institucion->convenioVigente() ? 'is-ok' : 'is-warn' }}">
                    {{ $institucion->convenioVigente() ? 'Vigente' : 'No vigente' }}
                </span>
                @if ($plan)
                    <span class="in-badge is-blue">{{ $plan->modalidad }}</span>
                @endif
            </div>
        </div>

        <div class="in-metrics">
            <x-institucional.metric label="Cupos contratados" :value="$institucion->cupos_totales ?: '∞'" />
            <x-institucional.metric label="Cupos en uso" :value="$institucion->cupos_usados" />
            <x-institucional.metric label="Disponibles" :value="$institucion->cuposDisponibles() === PHP_INT_MAX ? '∞' : $institucion->cuposDisponibles()" />
            <x-institucional.metric label="Inicio" :value="$institucion->fecha_inicio?->format('d/m/Y') ?? '—'" />
            <x-institucional.metric label="Renovación" :value="$institucion->fecha_fin?->format('d/m/Y') ?? '—'" hint="Fecha fin convenio" />
        </div>

        <div class="in-grid-12">
            <div class="in-span-6">
                <x-institucional.card title="Beneficios incluidos">
                    @if (empty($beneficios))
                        <p class="in-empty">Consulta con tu administrador FigueFit los beneficios del plan.</p>
                    @else
                        <ul class="in-benefits">
                            @foreach ($beneficios as $b)
                                @if (trim($b) !== '')
                                    <li>{{ trim($b) }}</li>
                                @endif
                            @endforeach
                        </ul>
                    @endif
                </x-institucional.card>
            </div>
            <div class="in-span-6">
                <x-institucional.card title="Módulos del plan">
                    @if ($plan && $plan->modulos->isNotEmpty())
                        <ul class="in-benefits">
                            @foreach ($plan->modulos as $mod)
                                <li>{{ $mod->nombre ?? $mod->codigo }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="in-empty">Módulos no configurados.</p>
                    @endif
                </x-institucional.card>
            </div>
        </div>
    </div>
@endsection
