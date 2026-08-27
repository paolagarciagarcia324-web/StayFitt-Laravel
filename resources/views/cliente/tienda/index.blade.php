@extends('layouts.cliente')

@section('title', 'Tienda')

@section('content')
    <x-cliente.page-head
        kicker="Tienda presencial"
        title="Productos exclusivos"
        subtitle="Suplementos, merchandising y accesorios disponibles para miembros FigueFit."
    >
        <x-slot:actions>
            <a class="cv-btn cv-btn-ghost cv-btn-sm" href="{{ route('cliente.tienda.ordenes') }}">Mis pedidos</a>
            <a class="cv-btn cv-btn-sm" href="{{ route('cliente.tienda.carrito') }}">
                Carrito ({{ $itemsCount }})
            </a>
        </x-slot:actions>
    </x-cliente.page-head>

    @if ($productos->isEmpty())
        <div class="cv-empty">No hay productos disponibles en este momento.</div>
    @else
        <div class="cv-media-grid">
            @foreach ($productos as $producto)
                @php $sinStock = ($producto->stock_total ?? 1) < 1; @endphp

                <article class="cv-media">
                    <div class="cv-media__cover">
                        @if ($producto->imagenSrc())
                            <img src="{{ $producto->imagenSrc() }}" alt="{{ $producto->nombre }}">
                        @endif
                        <div class="cv-media__cover-meta">
                            <span class="cv-badge is-cyan">{{ $producto->categoria?->nombre ?? 'General' }}</span>
                            @if ($sinStock)
                                <span class="cv-badge is-pink">Agotado</span>
                            @endif
                        </div>
                    </div>

                    <div class="cv-media__body">
                        <h3>{{ $producto->nombre }}</h3>
                        @if ($producto->descripcion)
                            <p>{{ \Illuminate\Support\Str::limit($producto->descripcion, 100) }}</p>
                        @endif
                        <p class="cv-price">${{ number_format((float) $producto->precio, 0, ',', '.') }}</p>
                        <p style="margin:0;font-size:11px;color:var(--cv-faint)">
                            Stock: {{ $producto->stock_total ?? '—' }}
                        </p>

                        <form method="POST" action="{{ route('cliente.tienda.agregar', $producto->id_producto) }}" style="margin-top:auto">
                            @csrf
                            <input type="hidden" name="cantidad" value="1">
                            <button class="cv-btn cv-btn-sm" type="submit" style="width:100%" @disabled($sinStock)>
                                {{ $sinStock ? 'Sin stock' : 'Agregar al carrito' }}
                            </button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>

        @if ($productos->hasPages())
            <div style="margin-top:8px">{{ $productos->links() }}</div>
        @endif
    @endif
@endsection
