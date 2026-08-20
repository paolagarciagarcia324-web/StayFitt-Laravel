@extends('layouts.admin')

@section('title', 'Tienda')
@section('subtitle', 'Catálogo presencial')

@section('actions')
    <button class="ad-btn ad-btn-add" type="button" data-open-product-form>+ Crear producto</button>
@endsection

@section('content')
    <div class="ad-tienda-grid">
        <section class="ad-card">
            <div class="ad-card-head"><h2>Catálogo</h2></div>
            <table class="ad-table ad-users-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Estado</th>
                        <th></th>
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
                            <td>
                                <button class="ad-btn ad-btn-ghost" type="button"
                                    data-open-product-edit
                                    data-id="{{ $producto->id_producto }}"
                                    data-precio="{{ $producto->precio }}"
                                    data-stock="{{ $producto->stock_total }}"
                                    data-estado="{{ $producto->estado }}">Editar</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="ad-empty-hero">
                                    <span class="ad-empty-orb">@include('partials.admin-icon', ['icon' => 'tienda'])</span>
                                    <strong>Sin productos</strong>
                                    <button class="ad-btn" type="button" data-open-product-form>Crear producto</button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="ad-pagination">{{ $productos->links() }}</div>
        </section>

        <section class="ad-card">
            <div class="ad-card-head"><h2>Pedidos</h2></div>
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
                                <form class="ad-form" method="POST" action="{{ route('admin.tienda.orden', $orden->id_orden) }}">
                                    @csrf
                                    @method('PUT')
                                    <select name="estado_orden">
                                        @foreach (['PENDIENTE_PAGO','PAGADA','PREPARADA','ENVIADA','ENTREGADA','CANCELADA'] as $estado)
                                            <option value="{{ $estado }}" @selected($orden->estado_orden === $estado)>{{ $estado }}</option>
                                        @endforeach
                                    </select>
                                    <button class="ad-btn ad-btn-ghost" type="submit" style="margin-top:6px">Actualizar</button>
                                </form>
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

    <div class="ad-modal" data-product-edit-modal hidden>
        <div class="ad-modal-card">
            <header class="ad-modal-head">
                <div>
                    <h2>Editar producto</h2>
                </div>
                <button type="button" class="ad-modal-close" data-close-product-edit aria-label="Cerrar">×</button>
            </header>
            <form class="ad-form" method="POST" data-product-edit-form>
                @csrf
                @method('PUT')
                <div class="ad-form-row">
                    <label><span>Precio *</span><input type="number" step="0.01" name="precio" data-field="precio" required></label>
                    <label><span>Stock *</span><input type="number" name="stock_total" data-field="stock" min="0" required></label>
                </div>
                <label><span>Estado *</span>
                    <select name="estado" data-field="estado" required>
                        <option value="ACTIVO">ACTIVO</option>
                        <option value="INACTIVO">INACTIVO</option>
                    </select>
                </label>
                <div class="ad-modal-actions">
                    <button class="ad-btn ad-btn-ghost" type="button" data-close-product-edit>Cancelar</button>
                    <button class="ad-btn" type="submit">Guardar</button>
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

            var editModal = document.querySelector('[data-product-edit-modal]');
            var editForm = document.querySelector('[data-product-edit-form]');
            function openEdit(btn) {
                if (!editModal || !editForm) return;
                editForm.action = '{{ url('admin/tienda') }}/' + btn.getAttribute('data-id');
                editForm.querySelector('[data-field="precio"]').value = btn.getAttribute('data-precio') || '';
                editForm.querySelector('[data-field="stock"]').value = btn.getAttribute('data-stock') || '';
                editForm.querySelector('[data-field="estado"]').value = btn.getAttribute('data-estado') || 'ACTIVO';
                editModal.hidden = false;
                editModal.classList.add('is-open');
            }
            function closeEdit() {
                if (!editModal) return;
                editModal.hidden = true;
                editModal.classList.remove('is-open');
            }
            document.querySelectorAll('[data-open-product-edit]').forEach(function (b) {
                b.addEventListener('click', function () { openEdit(b); });
            });
            document.querySelectorAll('[data-close-product-edit]').forEach(function (b) {
                b.addEventListener('click', closeEdit);
            });
            editModal?.addEventListener('click', function (e) { if (e.target === editModal) closeEdit(); });
        })();
    </script>
@endsection
