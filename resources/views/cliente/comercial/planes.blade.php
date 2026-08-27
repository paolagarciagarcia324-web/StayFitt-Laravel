@extends('layouts.cliente')

@section('title', 'Planes disponibles')

@section('content')
    <x-cliente.page-head
        kicker="Membresía"
        title="Planes disponibles"
        subtitle="Compara opciones y elige el plan que mejor se adapte a tu objetivo fitness."
    >
        <x-slot:actions>
            <a class="cv-btn cv-btn-ghost cv-btn-sm" href="{{ route('cliente.comercial.solicitudes') }}">Mis solicitudes</a>
            <a class="cv-btn cv-btn-sm" href="{{ route('cliente.comercial.cupones') }}">Cupones</a>
        </x-slot:actions>
    </x-cliente.page-head>

    @if ($planes->isEmpty())
        <div class="cv-empty">No hay planes publicados en este momento. Vuelve pronto o contacta a FigueFit.</div>
    @else
        <div class="cv-grid-3">
            @foreach ($planes as $plan)
                @php
                    $destacado = $plan->popular || $plan->destacado;
                    $beneficiosLista = [];
                    if (is_array($plan->caracteristicas_json) && count($plan->caracteristicas_json)) {
                        foreach ($plan->caracteristicas_json as $item) {
                            $beneficiosLista[] = is_array($item) ? ($item['texto'] ?? json_encode($item)) : $item;
                        }
                    } elseif ($plan->beneficios) {
                        $beneficiosLista = array_values(array_filter(array_map('trim', preg_split('/[\r\n]+/', $plan->beneficios))));
                    }
                    if (empty($beneficiosLista) && $plan->descripcion) {
                        $beneficiosLista = [\Illuminate\Support\Str::limit($plan->descripcion, 120)];
                    }
                @endphp

                <article class="cv-plan {{ $destacado ? 'is-featured' : '' }}">
                    @if ($plan->etiqueta || $plan->popular || $plan->destacado)
                        <div class="cv-plan__ribbon">
                            <span class="cv-badge is-pink">{{ $plan->etiqueta ?: ($plan->popular ? 'Popular' : 'Destacado') }}</span>
                        </div>
                    @endif

                    @if ($plan->imagenSrc())
                        <div class="cv-media__cover" style="border-radius:14px;margin:-4px -4px 0">
                            <img src="{{ $plan->imagenSrc() }}" alt="{{ $plan->nombre }}">
                        </div>
                    @endif

                    <div>
                        <div class="cv-kicker">{{ $plan->modalidad ?: 'Plan' }}</div>
                        <h2 class="cv-title" style="font-size:1.25rem;margin-top:4px">{{ $plan->nombre }}</h2>
                        <p class="cv-sub" style="margin-top:6px;font-size:12px">
                            {{ $plan->tipo_cliente ?: 'Individual' }} · {{ $plan->duracion_dias }} días
                        </p>
                    </div>

                    <p class="cv-price">
                        ${{ number_format((float) $plan->precio, 0, ',', '.') }}
                        <small>/ {{ $plan->duracion_dias }} días</small>
                    </p>

                    @if (count($beneficiosLista))
                        <ul>
                            @foreach (array_slice($beneficiosLista, 0, 6) as $beneficio)
                                <li>{{ $beneficio }}</li>
                            @endforeach
                        </ul>
                    @endif

                    <a class="cv-btn cv-btn-sm" href="{{ route('cliente.comercial.planes.show', $plan->id_plan) }}">
                        Ver detalle
                    </a>
                </article>
            @endforeach
        </div>
    @endif
@endsection
