@extends('layouts.cliente')

@section('content')
    <x-cliente.page-head
        kicker="Nutrición"
        :title="$plan->nombre"
        subtitle="Detalle de comidas y macros del día"
    >
        <x-slot:actions>
            <a class="cv-btn cv-btn-ghost cv-btn-sm" href="{{ route('cliente.nutricion.index') }}">Volver</a>
        </x-slot:actions>
    </x-cliente.page-head>

    <div class="cv-metrics">
        <x-cliente.metric label="Kcal" :value="$plan->kcal_objetivo ?? '—'" />
        <x-cliente.metric label="Proteínas" :value="($plan->proteinas_g ?? '—').' g'" />
        <x-cliente.metric label="Carbohidratos" :value="($plan->carbohidratos_g ?? '—').' g'" />
        <x-cliente.metric label="Grasas" :value="($plan->grasas_g ?? '—').' g'" />
    </div>

    <x-cliente.card title="Comidas del plan" subtitle="Distribución diaria recomendada">
        @forelse ($plan->comidas as $comida)
            <article class="cv-media" style="margin-bottom:12px">
                <div class="cv-media__cover" style="aspect-ratio:16/5;min-height:80px">
                    <div class="cv-media__cover-meta">
                        <span class="cv-badge is-cyan">{{ $comida->tipo_comida }}</span>
                        @if ($comida->calorias)
                            <span class="cv-badge is-pink">{{ $comida->calorias }} kcal</span>
                        @endif
                    </div>
                </div>
                <div class="cv-media__body">
                    <h3>{{ $comida->nombre }}</h3>
                    @if ($comida->porcion)
                        <p><strong>Porción:</strong> {{ $comida->porcion }}</p>
                    @endif
                    @if ($comida->receta)
                        <p style="margin-top:6px">{{ $comida->receta }}</p>
                    @endif
                    @if ($comida->alergenos)
                        <p style="margin-top:8px;font-size:12px;color:var(--cv-warn)">
                            <strong>Alérgenos:</strong> {{ $comida->alergenos }}
                        </p>
                    @endif
                </div>
            </article>
        @empty
            <p class="cv-empty">Este plan no tiene comidas cargadas.</p>
        @endforelse
    </x-cliente.card>
@endsection
