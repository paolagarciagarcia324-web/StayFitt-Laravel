@extends('layouts.cliente')

@section('content')
    <x-cliente.page-head
        kicker="Nutrición"
        title="Tu plan alimentario"
        subtitle="Macros diarios y comidas personalizadas por tu coach."
    />

    @if ($activo)
        <div class="cv-metrics">
            <x-cliente.metric label="Kcal objetivo" :value="$activo->kcal_objetivo ?? '—'" />
            <x-cliente.metric label="Proteínas" :value="($activo->proteinas_g ?? '—').' g'" />
            <x-cliente.metric label="Carbohidratos" :value="($activo->carbohidratos_g ?? '—').' g'" />
            <x-cliente.metric label="Grasas" :value="($activo->grasas_g ?? '—').' g'" />
        </div>

        <x-cliente.card
            :title="$activo->nombre"
            :subtitle="($activo->objetivo ?: 'Plan nutricional').' · Coach: '.($activo->coach?->usuario?->nombres ?? '—')"
        >
            <x-slot:actions>
                <a class="cv-btn cv-btn-sm" href="{{ route('cliente.nutricion.show', $activo->id_plan_nutricional) }}">Ver comidas</a>
            </x-slot:actions>

            @if ($activo->recomendaciones)
                <p style="margin:0 0 16px;color:var(--cv-muted);font-size:13px;line-height:1.5">{{ $activo->recomendaciones }}</p>
            @endif

            @if ($activo->comidas->isNotEmpty())
                <div class="cv-section-head">
                    <div>
                        <h2>Vista previa del día</h2>
                        <p>{{ $activo->comidas->count() }} comidas en tu plan</p>
                    </div>
                </div>
                <div class="cv-media-grid">
                    @foreach ($activo->comidas->take(4) as $comida)
                        <article class="cv-media">
                            <div class="cv-media__cover">
                                <div class="cv-media__cover-meta">
                                    <span class="cv-badge is-cyan">{{ $comida->tipo_comida }}</span>
                                    @if ($comida->calorias)
                                        <span class="cv-badge">{{ $comida->calorias }} kcal</span>
                                    @endif
                                </div>
                            </div>
                            <div class="cv-media__body">
                                <h3>{{ $comida->nombre }}</h3>
                                <p>{{ $comida->porcion ?: 'Porción estándar' }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <p class="cv-empty">Tu plan aún no tiene comidas cargadas.</p>
            @endif
        </x-cliente.card>
    @else
        <x-cliente.card title="Sin plan nutricional" subtitle="Tu coach aún no publicó un plan para ti">
            <p class="cv-empty">Cuando esté listo, verás aquí tus macros y comidas del día.</p>
        </x-cliente.card>
    @endif

    @if ($planes->count() > 1)
        <x-cliente.card title="Otros planes" subtitle="Historial o variantes asignadas">
            <div class="cv-list-feed">
                @foreach ($planes as $plan)
                    @if (!$activo || $plan->id_plan_nutricional !== $activo->id_plan_nutricional)
                        <div class="cv-feed-item">
                            <div class="cv-tile__ico" aria-hidden="true">🥗</div>
                            <div>
                                <strong>{{ $plan->nombre }}</strong>
                                <p style="margin:4px 0 0;font-size:12px;color:var(--cv-faint)">
                                    {{ $plan->kcal_objetivo ? $plan->kcal_objetivo.' kcal' : 'Sin macros' }}
                                    · {{ $plan->comidas->count() }} comidas
                                </p>
                            </div>
                            <a class="cv-btn cv-btn-ghost cv-btn-sm" href="{{ route('cliente.nutricion.show', $plan->id_plan_nutricional) }}">Abrir</a>
                        </div>
                    @endif
                @endforeach
            </div>
        </x-cliente.card>
    @endif
@endsection
