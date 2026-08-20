@extends('layouts.admin')

@section('title', 'Instituciones')
@section('subtitle', 'Convenios y cupos')

@section('actions')
    <button class="ad-btn ad-btn-add" type="button" data-open-inst-form>+ Nueva institución</button>
@endsection

@section('content')
    <section class="ad-card ad-users">
        <div class="ad-users-bar">
            <form class="ad-users-search" method="GET" action="{{ route('admin.instituciones.index') }}">
                <span>⌕</span>
                <input type="search" name="q" value="{{ $q }}" placeholder="Buscar institución o cliente">
            </form>
        </div>

        @forelse ($instituciones as $inst)
            <article class="ad-inst-block">
                <header class="ad-inst-head">
                    <div>
                        <h2>{{ $inst->nombre }}</h2>
                        <p>{{ $inst->ciudad ?: 'Sin ciudad' }} · {{ $inst->correo ?: $inst->telefono ?: 'Sin contacto' }}</p>
                    </div>
                    <div class="ad-inst-meta">
                        <span class="ad-pill-days">{{ (int) $inst->cupos_usados }}/{{ (int) $inst->cupos_totales }} cupos</span>
                        <span class="ad-badge {{ $inst->estado === 'ACTIVA' ? 'ad-badge-ok' : 'ad-badge-pendiente' }}">{{ $inst->estado }}</span>
                        <button class="ad-btn ad-btn-ghost" type="button"
                            data-open-enlace
                            data-id="{{ $inst->id_institucion }}">+ Enlace</button>
                        <button class="ad-btn ad-btn-ghost" type="button"
                            data-open-vincular
                            data-id="{{ $inst->id_institucion }}">Vincular</button>
                        <button class="ad-btn ad-btn-ghost" type="button"
                            data-open-inst-edit
                            data-id="{{ $inst->id_institucion }}"
                            data-nombre="{{ $inst->nombre }}"
                            data-nit="{{ $inst->nit }}"
                            data-contacto="{{ $inst->contacto_nombre }}"
                            data-correo="{{ $inst->correo }}"
                            data-telefono="{{ $inst->telefono }}"
                            data-ciudad="{{ $inst->ciudad }}"
                            data-cupos="{{ $inst->cupos_totales }}"
                            data-convenio="{{ $inst->convenio }}"
                            data-estado="{{ $inst->estado }}">Editar</button>
                    </div>
                </header>

                @include('partials.admin-client-table', [
                    'clientes' => $inst->clientes,
                    'variant' => 'virtual',
                    'compact' => true,
                    'emptyMessage' => 'Sin clientes vinculados.',
                ])

                @if ($inst->enlaces->isNotEmpty())
                    <table class="ad-table ad-table-compact" style="margin-top:12px">
                        <thead><tr><th>Token</th><th>Plan</th><th>Usos</th><th>Expira</th><th>Estado</th></tr></thead>
                        <tbody>
                            @foreach ($inst->enlaces as $enlace)
                                <tr>
                                    <td><code>{{ \Illuminate\Support\Str::limit($enlace->token, 16) }}</code></td>
                                    <td>{{ $enlace->plan?->nombre }}</td>
                                    <td>{{ $enlace->usos_actuales }}/{{ $enlace->cupos_maximos }}</td>
                                    <td>{{ optional($enlace->fecha_expiracion)->format('d/m/Y') }}</td>
                                    <td>{{ $enlace->estado }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </article>
        @empty
            <div class="ad-empty-hero">
                <span class="ad-empty-orb">@include('partials.admin-icon', ['icon' => 'instituciones'])</span>
                <strong>Sin instituciones</strong>
                <button class="ad-btn" type="button" data-open-inst-form>Crear institución</button>
            </div>
        @endforelse
    </section>

    <div class="ad-modal {{ $errors->any() ? 'is-open' : '' }}" data-inst-modal {{ $errors->any() ? '' : 'hidden' }}>
        <div class="ad-modal-card">
            <header class="ad-modal-head">
                <div><h2>Nueva institución</h2></div>
                <button type="button" class="ad-modal-close" data-close-inst-form aria-label="Cerrar">×</button>
            </header>
            <form class="ad-form" method="POST" action="{{ route('admin.instituciones.store') }}">
                @csrf
                <div class="ad-form-row">
                    <label><span>Nombre *</span><input type="text" name="nombre" value="{{ old('nombre') }}" required></label>
                    <label><span>NIT</span><input type="text" name="nit" value="{{ old('nit') }}"></label>
                </div>
                <div class="ad-form-row">
                    <label><span>Contacto</span><input type="text" name="contacto_nombre" value="{{ old('contacto_nombre') }}"></label>
                    <label><span>Ciudad</span><input type="text" name="ciudad" value="{{ old('ciudad') }}"></label>
                </div>
                <div class="ad-form-row">
                    <label><span>Correo</span><input type="email" name="correo" value="{{ old('correo') }}"></label>
                    <label><span>Teléfono</span><input type="text" name="telefono" value="{{ old('telefono') }}"></label>
                </div>
                <div class="ad-form-row">
                    <label><span>Cupos totales</span><input type="number" name="cupos_totales" value="{{ old('cupos_totales', 0) }}" min="0"></label>
                    <label><span>Convenio</span><input type="text" name="convenio" value="{{ old('convenio') }}"></label>
                </div>
                <div class="ad-modal-actions">
                    <button class="ad-btn ad-btn-ghost" type="button" data-close-inst-form>Cancelar</button>
                    <button class="ad-btn" type="submit">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="ad-modal" data-inst-edit-modal hidden>
        <div class="ad-modal-card">
            <header class="ad-modal-head">
                <div><h2>Editar institución</h2></div>
                <button type="button" class="ad-modal-close" data-close-inst-edit aria-label="Cerrar">×</button>
            </header>
            <form class="ad-form" method="POST" data-inst-edit-form>
                @csrf
                @method('PUT')
                <div class="ad-form-row">
                    <label><span>Nombre *</span><input type="text" name="nombre" data-field="nombre" required></label>
                    <label><span>NIT</span><input type="text" name="nit" data-field="nit"></label>
                </div>
                <div class="ad-form-row">
                    <label><span>Contacto</span><input type="text" name="contacto_nombre" data-field="contacto"></label>
                    <label><span>Ciudad</span><input type="text" name="ciudad" data-field="ciudad"></label>
                </div>
                <div class="ad-form-row">
                    <label><span>Correo</span><input type="email" name="correo" data-field="correo"></label>
                    <label><span>Teléfono</span><input type="text" name="telefono" data-field="telefono"></label>
                </div>
                <div class="ad-form-row">
                    <label><span>Cupos totales</span><input type="number" name="cupos_totales" data-field="cupos" min="0"></label>
                    <label><span>Convenio</span><input type="text" name="convenio" data-field="convenio"></label>
                </div>
                <label><span>Estado *</span>
                    <select name="estado" data-field="estado" required>
                        @foreach (['ACTIVA','INACTIVA','VENCIDA'] as $e)
                            <option value="{{ $e }}">{{ $e }}</option>
                        @endforeach
                    </select>
                </label>
                <div class="ad-modal-actions">
                    <button class="ad-btn ad-btn-ghost" type="button" data-close-inst-edit>Cancelar</button>
                    <button class="ad-btn" type="submit">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="ad-modal" data-enlace-modal hidden>
        <div class="ad-modal-card">
            <header class="ad-modal-head">
                <div><h2>Generar enlace</h2></div>
                <button type="button" class="ad-modal-close" data-close-enlace aria-label="Cerrar">×</button>
            </header>
            <form class="ad-form" method="POST" data-enlace-form>
                @csrf
                <div class="ad-form-row">
                    <label><span>Plan</span>
                        <select name="id_plan" required>
                            @foreach ($planes as $plan)
                                <option value="{{ $plan->id_plan }}">{{ $plan->nombre }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label><span>Cupos máximos</span><input type="number" name="cupos_maximos" min="1" value="20" required></label>
                </div>
                <label><span>Expira</span><input type="datetime-local" name="fecha_expiracion" required></label>
                <div class="ad-modal-actions">
                    <button class="ad-btn ad-btn-ghost" type="button" data-close-enlace>Cancelar</button>
                    <button class="ad-btn" type="submit">Generar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="ad-modal" data-vincular-modal hidden>
        <div class="ad-modal-card">
            <header class="ad-modal-head">
                <div><h2>Vincular cliente</h2></div>
                <button type="button" class="ad-modal-close" data-close-vincular aria-label="Cerrar">×</button>
            </header>
            <form class="ad-form" method="POST" data-vincular-form>
                @csrf
                <label><span>Cliente</span>
                    <select name="id_cliente" required>
                        @forelse ($clientesSinInst as $c)
                            <option value="{{ $c->id_cliente }}">{{ $c->usuario?->nombres }} {{ $c->usuario?->apellidos }} ({{ $c->usuario?->correo }})</option>
                        @empty
                            <option value="" disabled>No hay clientes disponibles</option>
                        @endforelse
                    </select>
                </label>
                <div class="ad-modal-actions">
                    <button class="ad-btn ad-btn-ghost" type="button" data-close-vincular>Cancelar</button>
                    <button class="ad-btn" type="submit" @disabled($clientesSinInst->isEmpty())>Vincular</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            var createModal = document.querySelector('[data-inst-modal]');
            function openCreate() { if (createModal) { createModal.hidden = false; createModal.classList.add('is-open'); } }
            function closeCreate() { if (createModal) { createModal.hidden = true; createModal.classList.remove('is-open'); } }
            document.querySelectorAll('[data-open-inst-form]').forEach(function (b) { b.addEventListener('click', openCreate); });
            document.querySelectorAll('[data-close-inst-form]').forEach(function (b) { b.addEventListener('click', closeCreate); });
            createModal?.addEventListener('click', function (e) { if (e.target === createModal) closeCreate(); });

            var editModal = document.querySelector('[data-inst-edit-modal]');
            var editForm = document.querySelector('[data-inst-edit-form]');
            function openEdit(btn) {
                if (!editModal || !editForm) return;
                editForm.action = '{{ url('admin/instituciones') }}/' + btn.getAttribute('data-id');
                ['nombre','nit','contacto','correo','telefono','ciudad','cupos','convenio','estado'].forEach(function (f) {
                    var el = editForm.querySelector('[data-field="'+f+'"]');
                    if (el) el.value = btn.getAttribute('data-'+f) || '';
                });
                editModal.hidden = false;
                editModal.classList.add('is-open');
            }
            function closeEdit() {
                if (!editModal) return;
                editModal.hidden = true;
                editModal.classList.remove('is-open');
            }
            document.querySelectorAll('[data-open-inst-edit]').forEach(function (b) {
                b.addEventListener('click', function () { openEdit(b); });
            });
            document.querySelectorAll('[data-close-inst-edit]').forEach(function (b) {
                b.addEventListener('click', closeEdit);
            });
            editModal?.addEventListener('click', function (e) { if (e.target === editModal) closeEdit(); });

            var enlaceModal = document.querySelector('[data-enlace-modal]');
            var enlaceForm = document.querySelector('[data-enlace-form]');
            document.querySelectorAll('[data-open-enlace]').forEach(function (b) {
                b.addEventListener('click', function () {
                    if (!enlaceModal || !enlaceForm) return;
                    enlaceForm.action = '{{ url('admin/instituciones') }}/' + b.getAttribute('data-id') + '/enlaces';
                    enlaceModal.hidden = false;
                    enlaceModal.classList.add('is-open');
                });
            });
            document.querySelectorAll('[data-close-enlace]').forEach(function (b) {
                b.addEventListener('click', function () {
                    if (enlaceModal) { enlaceModal.hidden = true; enlaceModal.classList.remove('is-open'); }
                });
            });
            enlaceModal?.addEventListener('click', function (e) {
                if (e.target === enlaceModal) { enlaceModal.hidden = true; enlaceModal.classList.remove('is-open'); }
            });

            var vincularModal = document.querySelector('[data-vincular-modal]');
            var vincularForm = document.querySelector('[data-vincular-form]');
            document.querySelectorAll('[data-open-vincular]').forEach(function (b) {
                b.addEventListener('click', function () {
                    if (!vincularModal || !vincularForm) return;
                    vincularForm.action = '{{ url('admin/instituciones') }}/' + b.getAttribute('data-id') + '/vincular';
                    vincularModal.hidden = false;
                    vincularModal.classList.add('is-open');
                });
            });
            document.querySelectorAll('[data-close-vincular]').forEach(function (b) {
                b.addEventListener('click', function () {
                    if (vincularModal) { vincularModal.hidden = true; vincularModal.classList.remove('is-open'); }
                });
            });
            vincularModal?.addEventListener('click', function (e) {
                if (e.target === vincularModal) { vincularModal.hidden = true; vincularModal.classList.remove('is-open'); }
            });
        })();
    </script>
@endsection
