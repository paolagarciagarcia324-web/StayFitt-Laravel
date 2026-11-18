@extends('layouts.admin')

@section('title', 'Asistentes técnicos')
@section('subtitle')
    <span class="ad-page-intro">
        <span class="ad-page-intro-lead">Apoyo operativo</span>
        <span class="ad-page-intro-note">Registro de usuarios con rol Asistente Técnico de Coach.</span>
    </span>
@endsection

@section('content')
    <div class="ad-kpi-soft">
        <article>
            <i class="is-cyan">@include('partials.admin-icon', ['icon' => 'personas'])</i>
            <div><strong>{{ $total }}</strong><span class="ad-user-mail">Asistentes</span></div>
        </article>
        <article>
            <i class="is-ok">@include('partials.admin-icon', ['icon' => 'clientes'])</i>
            <div><strong>{{ $asistentes->total() }}</strong><span class="ad-user-mail">En este filtro</span></div>
        </article>
        <article>
            <i class="is-pink">@include('partials.admin-icon', ['icon' => 'coaches'])</i>
            <div><strong>{{ $asistentes->where('estado', 'ACTIVO')->count() }}</strong><span class="ad-user-mail">Activos en página</span></div>
        </article>
        <article>
            <i class="is-warn">@include('partials.admin-icon', ['icon' => 'alerta'])</i>
            <div><strong>{{ $asistentes->where('estado', 'INACTIVO')->count() }}</strong><span class="ad-user-mail">Inactivos</span></div>
        </article>
    </div>

    <section class="ad-card ad-users ad-cli-board ad-asistentes-board">
        <div class="ad-cli-board-toolbar ad-asistentes-toolbar">
            <div class="ad-asistentes-toolbar-left">
                <div class="ad-cli-board-toolbar-copy">
                    <strong>Directorio</strong>
                    <span>{{ $asistentes->total() }} en este filtro</span>
                </div>
                @include('partials.admin-search', [
                    'action' => route('admin.asistentes.index'),
                    'value' => $q,
                    'placeholder' => 'Buscar por nombre, correo o documento',
                    'live' => true,
                ])
            </div>
            <div class="ad-cli-board-toolbar-actions ad-asistentes-toolbar-right">
                <button type="button" class="ad-btn ad-btn-ghost ad-asistente-add-btn" data-open-asistente-create>+ Registrar asistente</button>
                <span class="ad-users-total-badge" title="Asistentes con rol técnico">
                    <em>Total</em>
                    <strong>{{ $total }}</strong>
                </span>
            </div>
        </div>

        <div class="ad-cli-board-surface">
            <table class="ad-table ad-users-table ad-cli-table ad-asistentes-table">
                <thead>
                    <tr>
                        <th class="ad-cli-col-num">#</th>
                        <th class="ad-cli-col-perfil">Perfil</th>
                        <th class="ad-cli-col-cliente">Asistente</th>
                        <th>Documento</th>
                        <th>Estado</th>
                        <th>Alta</th>
                        <th class="ad-cli-col-actions">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($asistentes as $index => $u)
                        @php
                            $nombre = trim($u->nombres.' '.$u->apellidos) ?: 'Usuario';
                            $estado = $u->estado ?: $u->estado_cuenta ?: '—';
                            $correo = $u->correo ?: ($u->email ?: '');
                            $doc = $u->documento_identidad ?: '';
                            $searchBlob = mb_strtolower(trim($nombre.' '.$correo.' '.$doc));
                            $alta = optional($u->creado_en)->format('d/m/Y') ?: '—';
                        @endphp
                        <tr
                            data-search="{{ $searchBlob }}"
                            data-asistente-row
                            data-id="{{ $u->id_user }}"
                            data-nombres="{{ $u->nombres }}"
                            data-apellidos="{{ $u->apellidos }}"
                            data-correo="{{ $correo }}"
                            data-telefono="{{ $u->telefono }}"
                            data-documento="{{ $doc }}"
                            data-estado="{{ in_array($estado, ['ACTIVO', 'INACTIVO', 'SUSPENDIDO'], true) ? $estado : 'ACTIVO' }}"
                            data-alta="{{ $alta }}"
                            data-nombre="{{ $nombre }}"
                        >
                            <td class="ad-cli-col-num">{{ $asistentes->firstItem() + $index }}</td>
                            <td class="ad-cli-col-perfil">
                                @include('partials.admin-avatar', ['user' => $u, 'zoomable' => true])
                            </td>
                            <td class="ad-cli-col-cliente">
                                <strong>{{ $nombre }}</strong>
                                <div class="ad-cli-text">{{ $correo ?: '—' }}</div>
                            </td>
                            <td>{{ $doc ?: '—' }}</td>
                            <td>
                                <span class="ad-badge {{ in_array($estado, ['ACTIVO', 'ACTIVA'], true) ? 'ad-badge-ok' : 'ad-badge-pendiente' }}">
                                    {{ $estado }}
                                </span>
                            </td>
                            <td>{{ $alta }}</td>
                            <td class="ad-cli-col-actions">
                                <div class="ad-row-actions">
                                    <button class="ad-icon-btn" type="button" title="Ver" aria-label="Ver asistente" data-asistente-view>
                                        @include('partials.admin-icon', ['icon' => 'eye'])
                                    </button>
                                    <button class="ad-icon-btn" type="button" title="Editar" aria-label="Editar asistente" data-asistente-edit>
                                        @include('partials.admin-icon', ['icon' => 'edit'])
                                    </button>
                                    <button class="ad-icon-btn is-danger" type="button" title="Eliminar rol" aria-label="Quitar rol de asistente" data-asistente-delete>
                                        @include('partials.admin-icon', ['icon' => 'trash'])
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="ad-empty">
                                Aún no hay asistentes. Usa «Registrar asistente» para vincular un cliente o crear uno externo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <p class="ad-live-search-empty" data-live-search-empty hidden>Ningún asistente coincide con esa búsqueda.</p>
            @if (method_exists($asistentes, 'links'))
                <div class="ad-pagination">{{ $asistentes->links() }}</div>
            @endif
        </div>
    </section>

    {{-- Modal registrar --}}
    @php
        $createModo = old('modo', 'cliente');
        $openCreateModal = $errors->any() && in_array(old('modo'), ['cliente', 'externo'], true);
    @endphp
    <div class="ad-modal ad-asistente-create-modal {{ $openCreateModal ? 'is-open' : '' }}" data-asistente-create-modal {{ $openCreateModal ? '' : 'hidden' }}>
        <div class="ad-modal-card ad-asistente-create-card">
            <header class="ad-modal-head">
                <div>
                    <h2>Registrar asistente</h2>
                    <p>Vincula un cliente existente o crea una cuenta externa.</p>
                </div>
                <button type="button" class="ad-modal-close" data-asistente-create-close aria-label="Cerrar">×</button>
            </header>

            @if ($errors->any() && $openCreateModal)
                <div class="ad-alert ad-alert-error ad-asistente-create-errors">
                    <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="ad-asistente-mode-tabs" role="tablist" aria-label="Modo de registro">
                <button type="button" class="ad-asistente-mode-tab {{ $createModo === 'cliente' ? 'is-active' : '' }}" data-asistente-mode="cliente" role="tab" aria-selected="{{ $createModo === 'cliente' ? 'true' : 'false' }}">Cliente existente</button>
                <button type="button" class="ad-asistente-mode-tab {{ $createModo === 'externo' ? 'is-active' : '' }}" data-asistente-mode="externo" role="tab" aria-selected="{{ $createModo === 'externo' ? 'true' : 'false' }}">Persona externa</button>
            </div>

            {{-- Modo cliente --}}
            <form class="ad-form ad-asistente-create-panel" method="POST" action="{{ route('admin.asistentes.store') }}" data-asistente-panel="cliente" @if ($createModo !== 'cliente') hidden @endif>
                @csrf
                <input type="hidden" name="modo" value="cliente">
                <input type="hidden" name="id_user" value="{{ old('id_user', '') }}" data-asistente-cliente-id>
                <div class="ad-inst-combo" data-asistente-combo>
                    <label class="ad-inst-combo-search">
                        <span>Buscar cliente</span>
                        <div class="ad-inst-combo-field">
                            <span class="ad-inst-combo-ico" aria-hidden="true">@include('partials.admin-icon', ['icon' => 'search'])</span>
                            <input type="search" placeholder="Nombre, correo o documento…" data-asistente-combo-q autocomplete="off" @disabled($candidatosCliente->isEmpty())>
                        </div>
                    </label>
                    <div class="ad-inst-combo-picked" data-asistente-combo-picked hidden>
                        <strong data-asistente-combo-picked-name></strong>
                        <button type="button" class="ad-btn ad-btn-ghost" data-asistente-combo-clear>Cambiar</button>
                    </div>
                    <ul class="ad-inst-combo-list" data-asistente-combo-list @if ($candidatosCliente->isEmpty()) hidden @endif>
                        @forelse ($candidatosCliente as $candidato)
                            @php
                                $cNombre = trim($candidato->nombres.' '.$candidato->apellidos) ?: 'Cliente';
                                $cCorreo = $candidato->correo ?: ($candidato->email ?: '');
                                $cDoc = $candidato->documento_identidad ?: '';
                                $cSearch = mb_strtolower(trim($cNombre.' '.$cCorreo.' '.$cDoc));
                            @endphp
                            <li>
                                <button type="button" class="ad-inst-combo-opt"
                                    data-asistente-combo-opt
                                    data-id="{{ $candidato->id_user }}"
                                    data-label="{{ $cNombre }}"
                                    data-mail="{{ $cCorreo }}"
                                    data-search="{{ $cSearch }}">
                                    <strong>{{ $cNombre }}</strong>
                                    <em>{{ $cCorreo ?: '—' }}@if ($cDoc) · {{ $cDoc }}@endif</em>
                                </button>
                            </li>
                        @empty
                            <li class="ad-inst-combo-empty">No hay clientes disponibles</li>
                        @endforelse
                    </ul>
                    <p class="ad-inst-combo-empty" data-asistente-combo-none hidden>Sin coincidencias</p>
                </div>
                <div class="ad-modal-actions">
                    <button type="button" class="ad-btn ad-btn-ghost" data-asistente-create-close>Cancelar</button>
                    <button type="submit" class="ad-btn" data-asistente-cliente-submit @disabled($candidatosCliente->isEmpty())>Vincular asistente</button>
                </div>
            </form>

            {{-- Modo externo --}}
            <form class="ad-form ad-asistente-create-panel" method="POST" action="{{ route('admin.asistentes.store') }}" data-asistente-panel="externo" @if ($createModo !== 'externo') hidden @endif>
                @csrf
                <input type="hidden" name="modo" value="externo">
                <div class="ad-form-row">
                    <label><span>Nombres *</span><input type="text" name="nombres" value="{{ old('nombres') }}" required></label>
                    <label><span>Apellidos *</span><input type="text" name="apellidos" value="{{ old('apellidos') }}" required></label>
                </div>
                <div class="ad-form-row">
                    <label><span>Correo *</span><input type="email" name="correo" value="{{ old('correo') }}" required></label>
                    <label><span>Documento *</span><input type="text" name="documento_identidad" value="{{ old('documento_identidad') }}" required></label>
                </div>
                <label><span>Teléfono</span><input type="text" name="telefono" value="{{ old('telefono') }}"></label>
                <p class="ad-field-hint">Acceso inicial: correo + documento.</p>
                <div class="ad-modal-actions">
                    <button type="button" class="ad-btn ad-btn-ghost" data-asistente-create-close>Cancelar</button>
                    <button type="submit" class="ad-btn">Registrar asistente</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal ver --}}
    <div class="ad-modal" data-asistente-view-modal hidden>
        <div class="ad-modal-card">
            <header class="ad-modal-head">
                <div>
                    <h2>Ficha del asistente</h2>
                    <p>Solo lectura</p>
                </div>
                <button type="button" class="ad-modal-close" data-asistente-close aria-label="Cerrar">×</button>
            </header>
            <div class="ad-form ad-asistente-view-body">
                <p><strong>Nombre:</strong> <span data-v-nombre>—</span></p>
                <p><strong>Correo:</strong> <span data-v-correo>—</span></p>
                <p><strong>Teléfono:</strong> <span data-v-telefono>—</span></p>
                <p><strong>Documento:</strong> <span data-v-documento>—</span></p>
                <p><strong>Estado:</strong> <span data-v-estado>—</span></p>
                <p><strong>Alta:</strong> <span data-v-alta>—</span></p>
            </div>
        </div>
    </div>

    {{-- Modal editar --}}
    <div class="ad-modal" data-asistente-edit-modal hidden>
        <div class="ad-modal-card">
            <header class="ad-modal-head">
                <div>
                    <h2>Editar asistente</h2>
                    <p>Actualiza los datos de la cuenta.</p>
                </div>
                <button type="button" class="ad-modal-close" data-asistente-close aria-label="Cerrar">×</button>
            </header>
            <form class="ad-form" method="POST" data-asistente-edit-form>
                @csrf
                @method('PUT')
                @if (($q ?? '') !== '')
                    <input type="hidden" name="q" value="{{ $q }}">
                @endif
                <div class="ad-form-row">
                    <label>
                        <span>Nombres *</span>
                        <input type="text" name="nombres" required>
                    </label>
                    <label>
                        <span>Apellidos *</span>
                        <input type="text" name="apellidos" required>
                    </label>
                </div>
                <div class="ad-form-row">
                    <label>
                        <span>Correo *</span>
                        <input type="email" name="correo" required>
                    </label>
                    <label>
                        <span>Teléfono</span>
                        <input type="text" name="telefono">
                    </label>
                </div>
                <div class="ad-form-row">
                    <label>
                        <span>Documento</span>
                        <input type="text" name="documento_identidad">
                    </label>
                    <label>
                        <span>Estado *</span>
                        <select name="estado" required>
                            <option value="ACTIVO">ACTIVO</option>
                            <option value="INACTIVO">INACTIVO</option>
                            <option value="SUSPENDIDO">SUSPENDIDO</option>
                        </select>
                    </label>
                </div>
                <div class="ad-form-actions" style="display:flex;gap:10px;justify-content:flex-end;margin-top:1rem">
                    <button type="button" class="ad-btn" data-asistente-close>Cancelar</button>
                    <button type="submit" class="ad-btn ad-btn-add">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal eliminar --}}
    <div class="ad-modal" data-asistente-delete-modal hidden>
        <div class="ad-modal-card">
            <header class="ad-modal-head">
                <div>
                    <h2>Quitar rol de asistente</h2>
                    <p>La cuenta de usuario se conserva; solo deja de ser asistente técnico.</p>
                </div>
                <button type="button" class="ad-modal-close" data-asistente-close aria-label="Cerrar">×</button>
            </header>
            <p class="ad-asistente-delete-msg">¿Confirmas quitar el rol a <strong data-d-nombre>—</strong>?</p>
            <form method="POST" data-asistente-delete-form>
                @csrf
                @method('DELETE')
                @if (($q ?? '') !== '')
                    <input type="hidden" name="q" value="{{ $q }}">
                @endif
                <div class="ad-form-actions" style="display:flex;gap:10px;justify-content:flex-end;margin-top:1rem">
                    <button type="button" class="ad-btn" data-asistente-close>Cancelar</button>
                    <button type="submit" class="ad-btn is-danger">Quitar rol</button>
                </div>
            </form>
        </div>
    </div>

    @include('partials.admin-avatar-zoom')

    @php $ok = session('asistente_ok'); @endphp
    @if (is_array($ok) && ! empty($ok['accion']))
        @php
            $accionLabel = match ($ok['accion'] ?? '') {
                'vinculado' => 'Asistente vinculado',
                'creado' => 'Asistente registrado',
                'actualizado' => 'Asistente actualizado',
                'eliminado' => 'Rol de asistente quitado',
                default => 'Acción completada',
            };
            $detalle = match ($ok['accion'] ?? '') {
                'vinculado' => 'El cliente ya figura en el directorio con rol técnico.',
                'creado' => 'Cuenta creada. Acceso: correo + documento.',
                'actualizado' => 'Los datos del asistente se guardaron correctamente.',
                'eliminado' => 'La cuenta se conserva; ya no figura en este directorio.',
                default => 'La operación terminó correctamente.',
            };
        @endphp
        <div class="ad-modal ad-ok-modal is-open" data-ok-modal role="dialog" aria-modal="true" aria-labelledby="ad-ok-title">
            <div class="ad-modal-card ad-ok-card">
                <div class="ad-ok-confetti" aria-hidden="true">
                    <i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i>
                </div>
                <div class="ad-ok-check" aria-hidden="true">
                    <svg viewBox="0 0 52 52" width="56" height="56">
                        <circle cx="26" cy="26" r="24" fill="none"/>
                        <path fill="none" d="M14 27l8 8 16-16"/>
                    </svg>
                </div>
                <h2 id="ad-ok-title">{{ $accionLabel }}</h2>
                <p class="ad-ok-lead">fue un éxito</p>
                <p class="ad-muted-note">{{ $detalle }}</p>
                <button type="button" class="ad-btn" data-close-ok>Continuar</button>
            </div>
        </div>
    @endif

<script>
(function () {
    var viewModal = document.querySelector('[data-asistente-view-modal]');
    var editModal = document.querySelector('[data-asistente-edit-modal]');
    var deleteModal = document.querySelector('[data-asistente-delete-modal]');
    var createModal = document.querySelector('[data-asistente-create-modal]');
    var editForm = document.querySelector('[data-asistente-edit-form]');
    var deleteForm = document.querySelector('[data-asistente-delete-form]');

    function setBodyLock(on) {
        document.body.style.overflow = on ? 'hidden' : '';
    }

    function openModal(modal) {
        if (!modal) return;
        modal.hidden = false;
        modal.classList.add('is-open');
        setBodyLock(true);
    }

    function closeModal(modal) {
        if (!modal) return;
        modal.hidden = true;
        modal.classList.remove('is-open');
    }

    function closeModals() {
        [viewModal, editModal, deleteModal].forEach(closeModal);
        if (!createModal || createModal.hidden) {
            setBodyLock(false);
        }
    }

    function closeCreateModal() {
        closeModal(createModal);
        if (![viewModal, editModal, deleteModal, createModal].some(function (m) {
            return m && !m.hidden && m.classList.contains('is-open');
        })) {
            setBodyLock(false);
        }
    }

    var okModal = document.querySelector('[data-ok-modal]');
    if (okModal) {
        setBodyLock(true);
        function closeOk() {
            okModal.hidden = true;
            okModal.classList.remove('is-open');
            setBodyLock(false);
        }
        okModal.querySelectorAll('[data-close-ok]').forEach(function (b) {
            b.addEventListener('click', closeOk);
        });
        okModal.addEventListener('click', function (e) {
            if (e.target === okModal) closeOk();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && okModal.classList.contains('is-open')) closeOk();
        });
    }

    /* Registrar asistente — modos y combobox */
    var modeTabs = document.querySelectorAll('[data-asistente-mode]');
    var panels = document.querySelectorAll('[data-asistente-panel]');
    var clienteId = document.querySelector('[data-asistente-cliente-id]');
    var comboQ = document.querySelector('[data-asistente-combo-q]');
    var comboList = document.querySelector('[data-asistente-combo-list]');
    var comboNone = document.querySelector('[data-asistente-combo-none]');
    var comboPicked = document.querySelector('[data-asistente-combo-picked]');
    var comboPickedName = document.querySelector('[data-asistente-combo-picked-name]');
    var clienteSubmit = document.querySelector('[data-asistente-cliente-submit]');

    function setModo(modo) {
        modeTabs.forEach(function (tab) {
            var active = tab.getAttribute('data-asistente-mode') === modo;
            tab.classList.toggle('is-active', active);
            tab.setAttribute('aria-selected', active ? 'true' : 'false');
        });
        panels.forEach(function (panel) {
            panel.hidden = panel.getAttribute('data-asistente-panel') !== modo;
        });
    }

    modeTabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            setModo(tab.getAttribute('data-asistente-mode'));
        });
    });

    function resetCombo() {
        if (clienteId) clienteId.value = '';
        if (comboQ) {
            comboQ.value = '';
            comboQ.disabled = false;
        }
        if (comboPicked) comboPicked.hidden = true;
        if (comboList) comboList.hidden = false;
        if (comboNone) comboNone.hidden = true;
        document.querySelectorAll('[data-asistente-combo-opt]').forEach(function (opt) {
            opt.hidden = false;
        });
        if (clienteSubmit) {
            clienteSubmit.disabled = !document.querySelector('[data-asistente-combo-opt]');
        }
    }

    function filterCombo(query) {
        var q = (query || '').trim().toLowerCase();
        var visible = 0;
        document.querySelectorAll('[data-asistente-combo-opt]').forEach(function (opt) {
            var hay = !q || (opt.getAttribute('data-search') || '').indexOf(q) !== -1;
            opt.hidden = !hay;
            if (hay) visible++;
        });
        if (comboList) comboList.hidden = visible === 0;
        if (comboNone) comboNone.hidden = visible > 0 || !document.querySelector('[data-asistente-combo-opt]');
    }

    function pickCombo(opt) {
        if (!opt || !clienteId) return;
        clienteId.value = opt.getAttribute('data-id') || '';
        var label = opt.getAttribute('data-label') || '';
        var mail = opt.getAttribute('data-mail') || '';
        if (comboPickedName) comboPickedName.textContent = mail ? (label + ' · ' + mail) : label;
        if (comboPicked) comboPicked.hidden = false;
        if (comboList) comboList.hidden = true;
        if (comboNone) comboNone.hidden = true;
        if (comboQ) {
            comboQ.value = '';
            comboQ.disabled = true;
        }
        if (clienteSubmit) clienteSubmit.disabled = !clienteId.value;
    }

    comboQ?.addEventListener('input', function () {
        filterCombo(comboQ.value);
    });
    document.querySelectorAll('[data-asistente-combo-opt]').forEach(function (opt) {
        opt.addEventListener('click', function () { pickCombo(opt); });
    });
    document.querySelector('[data-asistente-combo-clear]')?.addEventListener('click', function () {
        resetCombo();
        comboQ?.focus();
    });
    document.querySelector('[data-asistente-panel="cliente"]')?.addEventListener('submit', function (e) {
        if (!clienteId || !clienteId.value) {
            e.preventDefault();
            comboQ?.focus();
        }
    });

    if (clienteId && clienteId.value) {
        document.querySelectorAll('[data-asistente-combo-opt]').forEach(function (opt) {
            if (opt.getAttribute('data-id') === clienteId.value) pickCombo(opt);
        });
    }

    document.querySelector('[data-open-asistente-create]')?.addEventListener('click', function () {
        resetCombo();
        setModo('cliente');
        openModal(createModal);
        setTimeout(function () { comboQ?.focus(); }, 40);
    });
    document.querySelectorAll('[data-asistente-create-close]').forEach(function (btn) {
        btn.addEventListener('click', closeCreateModal);
    });
    createModal?.addEventListener('click', function (e) {
        if (e.target === createModal) closeCreateModal();
    });

    if (createModal && createModal.classList.contains('is-open')) {
        setBodyLock(true);
        var initialModo = @json(old('modo', 'cliente'));
        if (initialModo === 'externo' || initialModo === 'cliente') setModo(initialModo);
    }

    function rowData(btn) {
        var row = btn.closest('[data-asistente-row]');
        if (!row) return null;
        return {
            id: row.getAttribute('data-id'),
            nombres: row.getAttribute('data-nombres') || '',
            apellidos: row.getAttribute('data-apellidos') || '',
            nombre: row.getAttribute('data-nombre') || '',
            correo: row.getAttribute('data-correo') || '',
            telefono: row.getAttribute('data-telefono') || '',
            documento: row.getAttribute('data-documento') || '',
            estado: row.getAttribute('data-estado') || 'ACTIVO',
            alta: row.getAttribute('data-alta') || '—',
        };
    }

    document.querySelectorAll('[data-asistente-view]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var d = rowData(btn);
            if (!d || !viewModal) return;
            viewModal.querySelector('[data-v-nombre]').textContent = d.nombre || '—';
            viewModal.querySelector('[data-v-correo]').textContent = d.correo || '—';
            viewModal.querySelector('[data-v-telefono]').textContent = d.telefono || '—';
            viewModal.querySelector('[data-v-documento]').textContent = d.documento || '—';
            viewModal.querySelector('[data-v-estado]').textContent = d.estado || '—';
            viewModal.querySelector('[data-v-alta]').textContent = d.alta || '—';
            openModal(viewModal);
        });
    });

    document.querySelectorAll('[data-asistente-edit]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var d = rowData(btn);
            if (!d || !editForm) return;
            editForm.action = '{{ url('/admin/asistentes') }}/' + d.id;
            editForm.querySelector('[name="nombres"]').value = d.nombres;
            editForm.querySelector('[name="apellidos"]').value = d.apellidos;
            editForm.querySelector('[name="correo"]').value = d.correo;
            editForm.querySelector('[name="telefono"]').value = d.telefono;
            editForm.querySelector('[name="documento_identidad"]').value = d.documento;
            editForm.querySelector('[name="estado"]').value = d.estado;
            openModal(editModal);
        });
    });

    document.querySelectorAll('[data-asistente-delete]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var d = rowData(btn);
            if (!d || !deleteForm) return;
            deleteForm.action = '{{ url('/admin/asistentes') }}/' + d.id;
            var nameEl = deleteModal.querySelector('[data-d-nombre]');
            if (nameEl) nameEl.textContent = d.nombre || 'este usuario';
            openModal(deleteModal);
        });
    });

    document.querySelectorAll('[data-asistente-close]').forEach(function (btn) {
        btn.addEventListener('click', closeModals);
    });

    [viewModal, editModal, deleteModal].forEach(function (modal) {
        if (!modal) return;
        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeModals();
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        if (okModal && okModal.classList.contains('is-open')) return;
        if (createModal && createModal.classList.contains('is-open')) closeCreateModal();
        else closeModals();
    });
})();
</script>
@endsection
