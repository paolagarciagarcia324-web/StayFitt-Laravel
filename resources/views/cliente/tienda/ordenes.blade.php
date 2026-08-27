@extends('layouts.cliente')

@section('title', 'Mis pedidos')

@section('content')
    @php
        $estadoBadge = fn (string $estado) => match (true) {
            in_array(strtoupper($estado), ['ENTREGADA', 'ENTREGADO', 'COMPLETADA', 'COMPLETADO'], true) => 'is-ok',
            in_array(strtoupper($estado), ['PENDIENTE', 'EN_PREPARACION', 'EN CAMINO'], true) => 'is-cyan',
            in_array(strtoupper($estado), ['CANCELADA', 'CANCELADO', 'RECHAZADA'], true) => 'is-pink',
            default => '',
        };
    @endphp

    <x-cliente.page-head
        kicker="Tienda"
        title="Mis pedidos"
        subtitle="Historial de compras, entregas y estado de tus órdenes."
    >
        <x-slot:actions>
            <a class="cv-btn cv-btn-ghost cv-btn-sm" href="{{ route('cliente.tienda.index') }}">Volver a tienda</a>
            <a class="cv-btn cv-btn-sm" href="{{ route('cliente.tienda.carrito') }}">Carrito</a>
        </x-slot:actions>
    </x-cliente.page-head>

    @if ($ordenes->isEmpty())
        <div class="cv-empty">
            Sin pedidos todavía.
            <div style="margin-top:14px">
                <a class="cv-btn cv-btn-sm" href="{{ route('cliente.tienda.index') }}">Explorar productos</a>
            </div>
        </div>
    @else
        <div class="cv-list-feed">
            @foreach ($ordenes as $orden)
                <article class="cv-card">
                    <div class="cv-card__head">
                        <div>
                            <h3>Pedido {{ $orden->numero_orden }}</h3>
                            <p>
                                {{ $orden->tipo_entrega === 'DOMICILIO' ? 'Entrega a domicilio' : 'Retiro en sede' }}
                                · {{ $orden->detalles->sum('cantidad') }} ítem(s)
                            </p>
                        </div>
                        <span class="cv-badge {{ $estadoBadge((string) $orden->estado_orden) }}">
                            {{ $orden->estado_orden }}
                        </span>
                    </div>
                    <div class="cv-card__body" style="padding-top:0">
                        <div class="cv-row" style="border:0;padding:0 0 10px">
                            <span>Total</span>
                            <strong class="cv-price" style="font-size:1.15rem">
                                ${{ number_format((float) $orden->total, 0, ',', '.') }}
                            </strong>
                        </div>

                        @if ($orden->detalles->isNotEmpty())
                            <div class="cv-list-feed" style="gap:8px">
                                @foreach ($orden->detalles as $detalle)
                                    <div class="cv-feed-item" style="padding:10px;background:rgba(255,255,255,0.02)">
                                        <div class="cv-avatar" style="width:40px;height:40px;border-radius:10px;font-size:12px">
                                            @if ($detalle->producto?->imagenSrc())
                                                <img src="{{ $detalle->producto->imagenSrc() }}" alt="">
                                            @else
                                                {{ mb_substr($detalle->producto?->nombre ?? 'P', 0, 1) }}
                                            @endif
                                        </div>
                                        <div>
                                            <strong style="font-size:13px">{{ $detalle->producto?->nombre ?? 'Producto' }}</strong>
                                            <p style="margin:2px 0 0;font-size:12px;color:var(--cv-muted)">
                                                Cantidad: {{ $detalle->cantidad }}
                                            </p>
                                        </div>
                                        <strong style="font-size:13px">
                                            ${{ number_format((float) ($detalle->subtotal ?? ($detalle->precio_unitario * $detalle->cantidad)), 0, ',', '.') }}
                                        </strong>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        @if ($ordenes->hasPages())
            <div>{{ $ordenes->links() }}</div>
        @endif
    @endif
@endsection
