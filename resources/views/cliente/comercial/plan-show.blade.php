@extends('layouts.cliente')

@section('title', $plan->nombre)

@section('content')
    @php
        $beneficiosLista = [];
        if (is_array($plan->caracteristicas_json) && count($plan->caracteristicas_json)) {
            foreach ($plan->caracteristicas_json as $item) {
                $beneficiosLista[] = is_array($item) ? ($item['texto'] ?? json_encode($item)) : $item;
            }
        } elseif ($plan->beneficios) {
            $beneficiosLista = array_values(array_filter(array_map('trim', preg_split('/[\r\n]+/', $plan->beneficios))));
        }
    @endphp

    <x-cliente.page-head
        kicker="Detalle del plan"
        :title="$plan->nombre"
        :subtitle="$plan->modalidad.' · '.$plan->duracion_dias.' días · '.($plan->tipo_cliente ?: 'Individual')"
    >
        <x-slot:actions>
            <a class="cv-btn cv-btn-ghost cv-btn-sm" href="{{ route('cliente.comercial.planes') }}">Volver a planes</a>
        </x-slot:actions>
    </x-cliente.page-head>

    <section class="cv-hero">
        @if ($plan->imagenSrc())
            <div class="cv-avatar" style="width:96px;height:96px;border-radius:22px;padding:0;overflow:hidden">
                <img src="{{ $plan->imagenSrc() }}" alt="{{ $plan->nombre }}">
            </div>
        @else
            <div class="cv-avatar">{{ mb_substr($plan->nombre, 0, 1) }}</div>
        @endif

        <div>
            @if ($plan->etiqueta || $plan->popular || $plan->destacado)
                <span class="cv-badge is-pink" style="margin-bottom:8px">
                    {{ $plan->etiqueta ?: ($plan->popular ? 'Popular' : 'Destacado') }}
                </span>
            @endif
            <p class="cv-price" style="margin:0">
                ${{ number_format((float) $plan->precio, 0, ',', '.') }}
                <small>/ {{ $plan->duracion_dias }} días</small>
            </p>
            @if ($plan->descripcion)
                <p class="cv-sub" style="margin-top:10px;max-width:60ch">{{ $plan->descripcion }}</p>
            @endif
        </div>

        <div class="cv-hero__actions">
            <a class="cv-btn" href="{{ url('/') }}#planes">Solicitar / renovar</a>
            <a class="cv-btn cv-btn-ghost" href="{{ route('cliente.comercial.cupones') }}">Aplicar cupón</a>
        </div>
    </section>

    @if (count($beneficiosLista) || $plan->inclusiones)
        <div class="cv-grid-2">
            @if (count($beneficiosLista))
                <x-cliente.card title="Beneficios" subtitle="Lo que incluye tu membresía">
                    <ul class="cv-plan" style="border:0;background:transparent;padding:0;gap:10px;min-height:auto">
                        @foreach ($beneficiosLista as $beneficio)
                            <li>{{ $beneficio }}</li>
                        @endforeach
                    </ul>
                </x-cliente.card>
            @endif

            @if ($plan->inclusiones)
                <x-cliente.card title="Incluye" subtitle="Servicios y accesos">
                    <p style="margin:0;color:var(--cv-muted);font-size:13px;line-height:1.55">{{ $plan->inclusiones }}</p>
                </x-cliente.card>
            @endif
        </div>
    @endif
@endsection
