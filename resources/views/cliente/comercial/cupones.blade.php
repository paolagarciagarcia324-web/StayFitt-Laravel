@extends('layouts.cliente')

@section('title', 'Cupones')

@section('content')
    <x-cliente.page-head
        kicker="Beneficios"
        title="Cupones y descuentos"
        subtitle="Valida un código promocional o revisa los cupones activos para tu membresía."
    >
        <x-slot:actions>
            <a class="cv-btn cv-btn-ghost cv-btn-sm" href="{{ route('cliente.comercial.planes') }}">Ver planes</a>
        </x-slot:actions>
    </x-cliente.page-head>

    <div class="cv-grid-2">
        <x-cliente.card title="Validar cupón" subtitle="Ingresa tu código promocional">
            <form method="POST" action="{{ route('cliente.comercial.cupones.validar') }}" class="cv-form">
                @csrf
                <label class="cv-span-2">
                    Código
                    <input name="codigo" value="{{ old('codigo') }}" placeholder="Ej. FIGUE10" required autocomplete="off">
                </label>
                <div class="cv-span-2">
                    <button class="cv-btn" type="submit">Validar cupón</button>
                </div>
            </form>
        </x-cliente.card>

        <x-cliente.card title="Cómo funciona" subtitle="Tips rápidos">
            <ul class="cv-plan" style="border:0;background:transparent;padding:0;gap:10px;min-height:auto">
                <li>Los cupones pueden aplicar descuento porcentual o monto fijo.</li>
                <li>Algunos están ligados a un plan específico.</li>
                <li>Revisa la fecha de vencimiento antes de solicitar tu plan.</li>
            </ul>
        </x-cliente.card>
    </div>

    <div class="cv-section-head">
        <div>
            <h2>Cupones activos</h2>
            <p>Disponibles para miembros FigueFit</p>
        </div>
    </div>

    @if ($cupones->isEmpty())
        <div class="cv-empty">No hay cupones públicos activos en este momento.</div>
    @else
        <div class="cv-grid-3">
            @foreach ($cupones as $cupon)
                @php
                    $esPorcentaje = strtoupper((string) ($cupon->tipo_descuento ?: $cupon->tipo)) === 'PORCENTAJE';
                    $valorDescuento = $esPorcentaje
                        ? number_format((float) $cupon->valor, 0).'% OFF'
                        : '$'.number_format((float) $cupon->valor, 0, ',', '.').' OFF';
                @endphp

                <article class="cv-plan">
                    <div class="cv-plan__ribbon">
                        <span class="cv-badge is-cyan">Activo</span>
                    </div>

                    <div>
                        <div class="cv-kicker">Código promocional</div>
                        <h3 class="cv-title" style="font-size:1.15rem;margin-top:4px;letter-spacing:0.06em">
                            {{ $cupon->codigo }}
                        </h3>
                    </div>

                    <p class="cv-price" style="font-size:1.5rem">{{ $valorDescuento }}</p>

                    <ul>
                        @if ($cupon->plan)
                            <li>Plan: {{ $cupon->plan->nombre }}</li>
                        @else
                            <li>Aplica a planes elegibles</li>
                        @endif
                        <li>Vence: {{ optional($cupon->fecha_fin)->format('d/m/Y') ?? 'Sin vencimiento' }}</li>
                    </ul>

                    <a class="cv-btn cv-btn-ghost cv-btn-sm" href="{{ route('cliente.comercial.planes') }}">
                        Ver planes
                    </a>
                </article>
            @endforeach
        </div>
    @endif
@endsection
