@extends('layouts.admin')

@section('title', 'Tienda')
@section('subtitle', 'Catálogo presencial · productos y pedidos recientes')

@section('content')
    <section class="ad-card ad-users">
        <div class="ad-users-bar">
            <p class="ad-empty" style="margin:0">Tienda exclusiva para clientes presenciales.</p>
            <button class="ad-btn ad-btn-add" type="button" data-open-product-form>+ Crear producto</button>
        </div>
    </section>

    <div class="ad-tienda-grid">
        <section class="ad-card">
            <h2>Catálogo</h2>
            <table class="ad-table ad-users-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($productos as $producto)
                        <tr>
                            <td>
                                <strong>{{ $producto->nombre }}</strong>
                                <em class="ad-user-mail">{{ $producto->sku }} · {{ $producto->categoria?->nombre }}</em>
                            </td>
                            <td>${{ number_format((float) $producto->precio, 0, ',', '.') }}</td>
                            <td>{{ $producto->stock_total }}</td>
                            <td>
                                <span class="ad-badge {{ $producto->estado === 'ACTIVO' ? 'ad-badge-ok' : 'ad-badge-bad' }}">{{ $producto->estado }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="ad-empty">Aún no hay productos.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="ad-pagination">{{ $productos->links() }}</div>
        </section>

        <section class="ad-card">
            <h2>Pedidos recientes</h2>
            <table class="ad-table ad-users-table">
                <thead>
                    <tr>
                        <th>Orden</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ordenes as $orden)
                        <tr>
                            <td><strong>{{ $orden->numero_orden }}</strong></td>
                            <td>{{ $orden->cliente?->usuario?->nombres }} {{ $orden->cliente?->usuario?->apellidos }}</td>
                            <td>${{ number_format((float) $orden->total, 0, ',', '.') }}</td>
                            <td>
                                <span class="ad-badge {{ in_array($orden->estado_orden, ['PAGADA','ENTREGADA']) ? 'ad-badge-ok' : 'ad-badge-pendiente' }}">{{ $orden->estado_orden }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="ad-empty">Sin pedidos todavía.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </div>

    <div class="ad-modal {{ $errors->any() ? 'is-open' : '' }}" data-product-modal {{ $errors->any() ? '' : 'hidden' }}>
        <div class="ad-modal-card">
            <header class="ad-modal-head">
                <div>
                    <h2>Nuevo producto</h2>
                    <p>Publicar artículo en la tienda presencial.</p>
                </div>
                <button type="button" class="ad-modal-close" data-close-product-form aria-label="Cerrar">×</button>
            </header>
            <form class="ad-form" method="POST" action="{{ route('admin.tienda.store') }}">
                @csrf
                <div class="ad-form-row">
                    <label><span>Nombre *</span><input type="text" name="nombre" value="{{ old('nombre') }}" required></label>
                    <label><span>SKU *</span><input type="text" name="sku" value="{{ old('sku') }}" required></label>
                </div>
                <div class="ad-form-row">
                    <label><span>Precio (COP) *</span><input type="number" step="0.01" name="precio" value="{{ old('precio') }}" required></label>
                    <label><span>Stock *</span><input type="number" name="stock_total" value="{{ old('stock_total', 0) }}" min="0" required></label>
                </div>
                <div class="ad-form-row">
                    <label>
                        <span>Categoría</span>
                        <select name="id_categoria_producto">
                            @foreach ($categorias as $categoria)
                                <option value="{{ $categoria->id_categoria_producto }}">{{ $categoria->nombre }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label><span>Descripción</span><input type="text" name="descripcion" value="{{ old('descripcion') }}"></label>
                </div>
                <div class="ad-modal-actions">
                    <button class="ad-btn ad-btn-ghost" type="button" data-close-product-form>Cancelar</button>
                    <button class="ad-btn" type="submit">Publicar producto</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            var modal = document.querySelector('[data-product-modal]');
            function open() { if (modal) { modal.hidden = false; modal.classList.add('is-open'); } }
            function close() { if (modal) { modal.hidden = true; modal.classList.remove('is-open'); } }
            document.querySelector('[data-open-product-form]')?.addEventListener('click', open);
            document.querySelectorAll('[data-close-product-form]').forEach(function (b) { b.addEventListener('click', close); });
            modal?.addEventListener('click', function (e) { if (e.target === modal) close(); });
        })();
    </script>
@endsection
