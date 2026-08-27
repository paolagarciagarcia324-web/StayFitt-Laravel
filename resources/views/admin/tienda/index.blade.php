@extends('layouts.admin')

@section('title', 'Tienda')
@section('subtitle', 'Catálogo presencial')

@section('actions')
    <button class="ad-btn ad-btn-add" type="button" data-open-product-form>+ Crear producto</button>
@endsection

@section('content')
    <div class="ad-tienda-layout">
        <section class="ad-card ad-ml-catalog">
            <div class="ad-card-head"><h2>Catálogo</h2></div>

            @if ($productos->isEmpty())
                <div class="ad-empty-hero">
                    <span class="ad-empty-orb">@include('partials.admin-icon', ['icon' => 'tienda'])</span>
                    <strong>Sin productos</strong>
                    <button class="ad-btn" type="button" data-open-product-form>Crear producto</button>
                </div>
            @else
                <div class="ad-ml-grid">
                    @foreach ($productos as $producto)
                        @php
                            $img = $producto->imagenSrc();
                            $vendidos = (int) ($producto->unidades_vendidas ?? 0);
                        @endphp
                        <article class="ad-ml-card"
                            data-open-product-edit
                            data-id="{{ $producto->id_producto }}"
                            data-nombre="{{ $producto->nombre }}"
                            data-sku="{{ $producto->sku }}"
                            data-precio="{{ $producto->precio }}"
                            data-stock="{{ $producto->stock_total }}"
                            data-estado="{{ $producto->estado }}"
                            data-calificacion="{{ $producto->calificacion }}"
                            data-descripcion="{{ $producto->descripcion }}"
                            data-categoria="{{ $producto->id_categoria_producto }}"
                            data-imagen="{{ $img }}"
                            data-imagen-url="{{ $producto->imagen_url }}">
                            <div class="ad-ml-thumb">
                                @if ($img)
                                    <img src="{{ $img }}" alt="{{ $producto->nombre }}" loading="lazy">
                                @else
                                    <span class="ad-ml-placeholder">@include('partials.admin-icon', ['icon' => 'tienda'])</span>
                                @endif
                                <span class="ad-ml-chip {{ $producto->estado === 'ACTIVO' ? 'is-ok' : 'is-bad' }}">{{ $producto->estado }}</span>
                            </div>
                            <div class="ad-ml-body">
                                <h3>{{ $producto->nombre }}</h3>
                                <small>{{ $producto->sku }} · {{ $producto->categoria?->nombre }}</small>
                                @include('partials.admin-ml-stars', ['rating' => $producto->calificacion])
                                <span class="ad-ml-sold">{{ $vendidos }} vendidos</span>
                                <strong class="ad-ml-price">${{ number_format((float) $producto->precio, 0, ',', '.') }}</strong>
                                <div class="ad-ml-meta">
                                    <span class="ad-pill-days">Stock {{ $producto->stock_total }}</span>
                                    <button class="ad-btn ad-btn-ghost" type="button" data-open-product-edit>Editar</button>
                                    @if ($producto->estado === 'ACTIVO')
                                        <form method="POST" action="{{ route('admin.tienda.destroy', $producto->id_producto) }}" style="display:inline" onsubmit="return confirm('¿Desactivar producto?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="ad-btn ad-btn-ghost" type="submit">Desactivar</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="ad-pagination">{{ $productos->links() }}</div>
            @endif
        </section>

        <section class="ad-card ad-ml-orders">
            <div class="ad-card-head"><h2>Pedidos</h2></div>
            <div class="ad-ml-order-list">
                @forelse ($ordenes as $orden)
                    @php $comp = $orden->comprobanteSrc(); @endphp
                    <article class="ad-ml-order">
                        <div>
                            <strong>{{ $orden->numero_orden }}</strong>
                            <small>{{ $orden->cliente?->usuario?->nombres }} {{ $orden->cliente?->usuario?->apellidos }}</small>
                        </div>
                        <em>${{ number_format((float) $orden->total, 0, ',', '.') }}</em>
                        @if ($comp)
                            <div class="ad-ml-order-comp">
                                @include('partials.admin-media-thumb', ['src' => $comp, 'alt' => 'Comprobante', 'size' => 'sm', 'icon' => 'pagos'])
                                <a class="ad-link" href="{{ $comp }}" target="_blank" rel="noopener">Ver comprobante</a>
                            </div>
                        @endif
                        <form class="ad-form" method="POST" action="{{ route('admin.tienda.orden', $orden->id_orden) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <select name="estado_orden">
                                @foreach (['PENDIENTE_PAGO','PAGADA','PREPARADA','ENVIADA','ENTREGADA','CANCELADA'] as $estado)
                                    <option value="{{ $estado }}" @selected($orden->estado_orden === $estado)>{{ $estado }}</option>
                                @endforeach
                            </select>
                            <label class="ad-ml-upload" style="margin:0"><span>Comprobante</span><input type="file" name="comprobante" accept="image/*"></label>
                            <input type="url" name="comprobante_url" value="{{ $orden->comprobante_url }}" placeholder="URL comprobante">
                            <button class="ad-btn ad-btn-ghost" type="submit">Actualizar</button>
                        </form>
                    </article>
                @empty
                    <div class="ad-empty-hero" style="padding:24px">
                        <span class="ad-empty-orb">@include('partials.admin-icon', ['icon' => 'tienda'])</span>
                        <strong>Sin pedidos</strong>
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    {{-- Crear --}}
    <div class="ad-modal {{ $errors->any() && ! old('_edit') ? 'is-open' : '' }}" data-product-modal {{ $errors->any() && ! old('_edit') ? '' : 'hidden' }}>
        <div class="ad-modal-card ad-ml-modal">
            <header class="ad-modal-head">
                <div><h2>Nuevo producto</h2></div>
                <button type="button" class="ad-modal-close" data-close-product-form aria-label="Cerrar">×</button>
            </header>
            <form class="ad-form ad-ml-form" method="POST" action="{{ route('admin.tienda.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="ad-ml-form-grid">
                    <div class="ad-ml-preview-wrap">
                        <div class="ad-ml-preview" data-preview-box>
                            <img data-preview-img hidden alt="Preview">
                            <span class="ad-ml-placeholder" data-preview-empty>@include('partials.admin-icon', ['icon' => 'tienda'])</span>
                        </div>
                        <label class="ad-ml-upload">
                            <span>Subir foto</span>
                            <input type="file" name="imagen" accept="image/*" data-preview-file>
                        </label>
                        <label><span>O URL de imagen</span><input type="url" name="imagen_url" value="{{ old('imagen_url') }}" placeholder="https://..." data-preview-url></label>
                    </div>
                    <div class="ad-ml-fields">
                        <div class="ad-form-row">
                            <label><span>Nombre *</span><input type="text" name="nombre" value="{{ old('nombre') }}" required></label>
                            <label><span>SKU *</span><input type="text" name="sku" value="{{ old('sku') }}" required></label>
                        </div>
                        <div class="ad-form-row">
                            <label><span>Precio (COP) *</span><input class="ad-ml-price-input" type="number" step="0.01" name="precio" value="{{ old('precio') }}" required></label>
                            <label><span>Stock *</span><input type="number" name="stock_total" value="{{ old('stock_total', 0) }}" min="0" required></label>
                        </div>
                        <label>
                            <span>Categoría</span>
                            <select name="id_categoria_producto">
                                @foreach ($categorias as $categoria)
                                    <option value="{{ $categoria->id_categoria_producto }}" @selected(old('id_categoria_producto') == $categoria->id_categoria_producto)>{{ $categoria->nombre }}</option>
                                @endforeach
                            </select>
                        </label>
                        <div class="ad-ml-rating-field">
                            <span>Calificación / fama</span>
                            <div class="ad-ml-rating-pick" data-rating-pick>
                                @for ($i = 1; $i <= 5; $i++)
                                    <button type="button" data-star="{{ $i }}" class="{{ (float) old('calificacion', 4) >= $i ? 'is-on' : '' }}">★</button>
                                @endfor
                            </div>
                            <input type="hidden" name="calificacion" value="{{ old('calificacion', 4) }}" data-rating-value>
                        </div>
                        <label><span>Descripción</span><textarea name="descripcion" rows="3">{{ old('descripcion') }}</textarea></label>
                    </div>
                </div>
                <div class="ad-modal-actions">
                    <button class="ad-btn ad-btn-ghost" type="button" data-close-product-form>Cancelar</button>
                    <button class="ad-btn" type="submit">Publicar producto</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Editar --}}
    <div class="ad-modal" data-product-edit-modal hidden>
        <div class="ad-modal-card ad-ml-modal">
            <header class="ad-modal-head">
                <div><h2>Editar producto</h2></div>
                <button type="button" class="ad-modal-close" data-close-product-edit aria-label="Cerrar">×</button>
            </header>
            <form class="ad-form ad-ml-form" method="POST" data-product-edit-form enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="ad-ml-form-grid">
                    <div class="ad-ml-preview-wrap">
                        <div class="ad-ml-preview" data-edit-preview-box>
                            <img data-edit-preview-img hidden alt="Preview">
                            <span class="ad-ml-placeholder" data-edit-preview-empty>@include('partials.admin-icon', ['icon' => 'tienda'])</span>
                        </div>
                        <label class="ad-ml-upload">
                            <span>Cambiar foto</span>
                            <input type="file" name="imagen" accept="image/*" data-edit-preview-file>
                        </label>
                        <label><span>O URL de imagen</span><input type="url" name="imagen_url" data-field="imagen_url" placeholder="https://..." data-edit-preview-url></label>
                    </div>
                    <div class="ad-ml-fields">
                        <div class="ad-form-row">
                            <label><span>Nombre *</span><input type="text" name="nombre" data-field="nombre" required></label>
                            <label><span>SKU *</span><input type="text" name="sku" data-field="sku" required></label>
                        </div>
                        <div class="ad-form-row">
                            <label><span>Precio (COP) *</span><input class="ad-ml-price-input" type="number" step="0.01" name="precio" data-field="precio" required></label>
                            <label><span>Stock *</span><input type="number" name="stock_total" data-field="stock" min="0" required></label>
                        </div>
                        <div class="ad-form-row">
                            <label>
                                <span>Categoría</span>
                                <select name="id_categoria_producto" data-field="categoria">
                                    @foreach ($categorias as $categoria)
                                        <option value="{{ $categoria->id_categoria_producto }}">{{ $categoria->nombre }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label><span>Estado *</span>
                                <select name="estado" data-field="estado" required>
                                    <option value="ACTIVO">ACTIVO</option>
                                    <option value="INACTIVO">INACTIVO</option>
                                </select>
                            </label>
                        </div>
                        <div class="ad-ml-rating-field">
                            <span>Calificación / fama</span>
                            <div class="ad-ml-rating-pick" data-edit-rating-pick>
                                @for ($i = 1; $i <= 5; $i++)
                                    <button type="button" data-star="{{ $i }}">★</button>
                                @endfor
                            </div>
                            <input type="hidden" name="calificacion" value="0" data-field="calificacion" data-edit-rating-value>
                        </div>
                        <label><span>Descripción</span><textarea name="descripcion" rows="3" data-field="descripcion"></textarea></label>
                    </div>
                </div>
                <div class="ad-modal-actions">
                    <button class="ad-btn ad-btn-ghost" type="button" data-close-product-edit>Cancelar</button>
                    <button class="ad-btn" type="submit">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            function wirePreview(fileSel, urlSel, imgSel, emptySel) {
                var file = document.querySelector(fileSel);
                var url = document.querySelector(urlSel);
                var img = document.querySelector(imgSel);
                var empty = document.querySelector(emptySel);
                function show(src) {
                    if (!img || !empty) return;
                    if (src) {
                        img.src = src;
                        img.hidden = false;
                        empty.hidden = true;
                    } else {
                        img.removeAttribute('src');
                        img.hidden = true;
                        empty.hidden = false;
                    }
                }
                file?.addEventListener('change', function () {
                    var f = file.files && file.files[0];
                    if (!f) return show(url?.value || '');
                    var reader = new FileReader();
                    reader.onload = function (e) { show(e.target.result); };
                    reader.readAsDataURL(f);
                });
                url?.addEventListener('input', function () {
                    if (file && file.files && file.files.length) return;
                    show(url.value.trim());
                });
                return show;
            }

            function wireStars(pickSel, valueSel) {
                var pick = document.querySelector(pickSel);
                var value = document.querySelector(valueSel);
                function paint(n) {
                    if (!pick) return;
                    pick.querySelectorAll('[data-star]').forEach(function (b) {
                        b.classList.toggle('is-on', Number(b.getAttribute('data-star')) <= n);
                    });
                    if (value) value.value = String(n);
                }
                pick?.querySelectorAll('[data-star]').forEach(function (b) {
                    b.addEventListener('click', function () {
                        paint(Number(b.getAttribute('data-star')));
                    });
                });
                return paint;
            }

            var showCreatePreview = wirePreview('[data-preview-file]', '[data-preview-url]', '[data-preview-img]', '[data-preview-empty]');
            var paintCreateStars = wireStars('[data-rating-pick]', '[data-rating-value]');
            paintCreateStars(Number(document.querySelector('[data-rating-value]')?.value || 4));

            var showEditPreview = wirePreview('[data-edit-preview-file]', '[data-edit-preview-url]', '[data-edit-preview-img]', '[data-edit-preview-empty]');
            var paintEditStars = wireStars('[data-edit-rating-pick]', '[data-edit-rating-value]');

            var modal = document.querySelector('[data-product-modal]');
            function openCreate() { if (modal) { modal.hidden = false; modal.classList.add('is-open'); } }
            function closeCreate() { if (modal) { modal.hidden = true; modal.classList.remove('is-open'); } }
            document.querySelectorAll('[data-open-product-form]').forEach(function (b) { b.addEventListener('click', openCreate); });
            document.querySelectorAll('[data-close-product-form]').forEach(function (b) { b.addEventListener('click', closeCreate); });
            modal?.addEventListener('click', function (e) { if (e.target === modal) closeCreate(); });

            var editModal = document.querySelector('[data-product-edit-modal]');
            var editForm = document.querySelector('[data-product-edit-form]');
            function openEdit(btn) {
                if (!editModal || !editForm) return;
                var card = btn.closest('[data-id]') || btn;
                editForm.action = '{{ url('admin/tienda') }}/' + card.getAttribute('data-id');
                ['nombre','sku','precio','descripcion','estado','imagen_url'].forEach(function (f) {
                    var el = editForm.querySelector('[data-field="'+f+'"]');
                    if (el) el.value = card.getAttribute('data-' + (f === 'imagen_url' ? 'imagen-url' : f)) || '';
                });
                var stock = editForm.querySelector('[data-field="stock"]');
                if (stock) stock.value = card.getAttribute('data-stock') || '';
                var cat = editForm.querySelector('[data-field="categoria"]');
                if (cat) cat.value = card.getAttribute('data-categoria') || '';
                var rating = Number(card.getAttribute('data-calificacion') || 0);
                paintEditStars(rating);
                showEditPreview(card.getAttribute('data-imagen') || '');
                var file = editForm.querySelector('[data-edit-preview-file]');
                if (file) file.value = '';
                editModal.hidden = false;
                editModal.classList.add('is-open');
            }
            function closeEdit() {
                if (!editModal) return;
                editModal.hidden = true;
                editModal.classList.remove('is-open');
            }
            document.querySelectorAll('[data-open-product-edit]').forEach(function (b) {
                b.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    openEdit(b);
                });
            });
            document.querySelectorAll('[data-close-product-edit]').forEach(function (b) {
                b.addEventListener('click', closeEdit);
            });
            editModal?.addEventListener('click', function (e) { if (e.target === editModal) closeEdit(); });
        })();
    </script>
@endsection
