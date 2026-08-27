@extends('layouts.cliente')

@section('title', 'Carrito')

@section('content')
    <x-cliente.page-head
        kicker="Checkout"
        title="Tu carrito"
        subtitle="Revisa los productos y confirma la entrega de tu pedido."
    >
        <x-slot:actions>
            <a class="cv-btn cv-btn-ghost cv-btn-sm" href="{{ route('cliente.tienda.index') }}">Seguir comprando</a>
        </x-slot:actions>
    </x-cliente.page-head>

    @if ($items->isEmpty())
        <div class="cv-empty">
            Tu carrito está vacío.
            <div style="margin-top:14px">
                <a class="cv-btn cv-btn-sm" href="{{ route('cliente.tienda.index') }}">Ir a la tienda</a>
            </div>
        </div>
    @else
        <div class="cv-grid-2">
            <x-cliente.card title="Productos" :subtitle="count($items).' artículo(s)'">
                <div class="cv-list-feed">
                    @foreach ($items as $item)
                        @php
                            $lineaSubtotal = (float) $item->precio_unitario * (int) $item->cantidad;
                        @endphp

                        <article class="cv-feed-item">
                            <div class="cv-avatar" style="width:52px;height:52px;border-radius:14px;font-size:14px">
                                @if ($item->producto?->imagenSrc())
                                    <img src="{{ $item->producto->imagenSrc() }}" alt="">
                                @else
                                    {{ mb_substr($item->producto?->nombre ?? 'P', 0, 1) }}
                                @endif
                            </div>

                            <div>
                                <strong style="font-size:14px">{{ $item->producto?->nombre ?? 'Producto' }}</strong>
                                <p style="margin:4px 0 0;font-size:12px;color:var(--cv-muted)">
                                    ${{ number_format((float) $item->precio_unitario, 0, ',', '.') }} c/u
                                </p>
                                <form
                                    method="POST"
                                    action="{{ route('cliente.tienda.items.update', $item->id_item) }}"
                                    style="display:flex;gap:8px;align-items:center;margin-top:10px"
                                >
                                    @csrf
                                    @method('PUT')
                                    <input type="number" name="cantidad" min="0" max="20" value="{{ $item->cantidad }}" style="width:72px">
                                    <button class="cv-btn cv-btn-ghost cv-btn-sm" type="submit">Actualizar</button>
                                </form>
                            </div>

                            <div style="text-align:right">
                                <p class="cv-price" style="font-size:1.1rem;margin:0">
                                    ${{ number_format($lineaSubtotal, 0, ',', '.') }}
                                </p>
                                <small style="color:var(--cv-faint)">× {{ $item->cantidad }}</small>
                            </div>
                        </article>
                    @endforeach
                </div>
            </x-cliente.card>

            <div style="display:grid;gap:16px;align-content:start">
                <div class="cv-metrics" style="grid-template-columns:1fr">
                    <x-cliente.metric
                        label="Subtotal"
                        :value="'$'.number_format($subtotal, 0, ',', '.')"
                        hint="Antes de confirmar pedido"
                    />
                </div>

                <x-cliente.card title="Confirmar pedido" subtitle="Entrega y datos de envío">
                    <form method="POST" action="{{ route('cliente.tienda.checkout') }}" class="cv-form">
                        @csrf
                        <label>
                            Tipo de entrega
                            <select name="tipo_entrega" required>
                                <option value="RETIRO" @selected(old('tipo_entrega') === 'RETIRO')>Retiro en sede</option>
                                <option value="DOMICILIO" @selected(old('tipo_entrega') === 'DOMICILIO')>Domicilio</option>
                            </select>
                        </label>
                        <label>
                            Ciudad
                            <input name="ciudad_entrega" value="{{ old('ciudad_entrega', $cliente->ciudad) }}">
                        </label>
                        <label class="cv-span-2">
                            Dirección (si domicilio)
                            <input name="direccion_entrega" value="{{ old('direccion_entrega') }}">
                        </label>
                        <label class="cv-span-2">
                            Observación
                            <textarea name="observacion" rows="2">{{ old('observacion') }}</textarea>
                        </label>
                        <div class="cv-span-2">
                            <button class="cv-btn" type="submit" style="width:100%">
                                Enviar pedido · ${{ number_format($subtotal, 0, ',', '.') }}
                            </button>
                        </div>
                    </form>
                </x-cliente.card>
            </div>
        </div>
    @endif
@endsection
