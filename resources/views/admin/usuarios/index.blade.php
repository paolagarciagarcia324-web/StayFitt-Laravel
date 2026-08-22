@extends('layouts.admin')

@section('title', 'Usuarios')
@section('subtitle', 'Cuentas de clientes')

@section('content')
    <section class="ad-card ad-users">
        <div class="ad-users-bar">
            @include('partials.admin-search', [
                'action' => route('admin.usuarios.index'),
                'value' => $q,
                'placeholder' => 'Buscar por nombre',
                'live' => true,
            ])
            <button class="ad-btn ad-btn-add" type="button" data-open-user-form>+ Crear nuevo usuario</button>
        </div>

        <table class="ad-table ad-users-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Perfil</th>
                    <th>Nombre</th>
                    <th>Modalidad</th>
                    <th>Plan</th>
                    <th>Días restantes</th>
                    <th>Nacimiento</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($usuarios as $index => $u)
                    @php
                        $cliente = $u->cliente;
                        $planActivo = $cliente?->planesCliente
                            ?->where('estado_plan_cliente', 'ACTIVO')
                            ->sortByDesc('fecha_fin')
                            ->first();
                        $fin = $planActivo?->fecha_fin;
                        $dias = $fin ? (int) now()->startOfDay()->diffInDays($fin, false) : null;
                    @endphp
                    <tr>
                        <td>{{ $usuarios->firstItem() + $index }}</td>
                        <td>@include('partials.admin-avatar', ['user' => $u])</td>
                        <td>
                            <strong>{{ $u->nombres }} {{ $u->apellidos }}</strong>
                            <em class="ad-user-mail">{{ $u->correo }}</em>
                        </td>
                        <td>
                            @if ($cliente?->modalidad)
                                <span class="ad-badge {{ $cliente->modalidad === 'PRESENCIAL' ? 'ad-badge-ok' : 'ad-badge-pendiente' }}">{{ $cliente->modalidad }}</span>
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $planActivo?->plan?->nombre ?? '—' }}</td>
                        <td>
                            @if ($dias === null)
                                —
                            @elseif ($dias < 0)
                                <span class="ad-badge ad-badge-bad">Vencido</span>
                            @else
                                <span class="ad-pill-days">{{ $dias }} días</span>
                            @endif
                        </td>
                        <td>
                            @if ($cliente?->fecha_nacimiento)
                                {{ \Illuminate\Support\Carbon::parse($cliente->fecha_nacimiento)->format('d/m/Y') }}
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            <div class="ad-row-actions">
                                <button class="ad-icon-btn" type="button"
                                    title="Editar"
                                    aria-label="Editar usuario"
                                    data-open-user-edit
                                    data-id="{{ $u->id_user }}"
                                    data-estado="{{ $u->estado ?: $u->estado_cuenta ?: 'ACTIVO' }}"
                                    data-roles="{{ $u->roles->pluck('id_rol')->implode(',') }}"
                                    data-foto="{{ $u->avatarUrl() }}"
                                    data-foto-url="{{ $u->foto_perfil_url }}">
                                    @include('partials.admin-icon', ['icon' => 'edit'])
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="ad-empty">No hay usuarios con ese nombre.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="ad-pagination">{{ $usuarios->links() }}</div>
    </section>

    <div class="ad-modal {{ $errors->any() ? 'is-open' : '' }}" data-user-modal {{ $errors->any() ? '' : 'hidden' }}>
        <div class="ad-modal-card ad-user-create-modal">
            <header class="ad-modal-head ad-user-create-head">
                <div>
                    <h2>Nuevo usuario</h2>
                    <p>Alta presencial · ficha de sala</p>
                </div>
                <button type="button" class="ad-modal-close" data-close-user-form aria-label="Cerrar">×</button>
            </header>

            @if ($errors->any())
                <div class="ad-alert ad-alert-error">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="ad-form ad-user-create-form" method="POST" action="{{ route('admin.usuarios.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="ad-user-create-body">
                    @include('admin.usuarios._create_fields')
                </div>
                <div class="ad-modal-actions ad-user-create-actions">
                    <button class="ad-btn ad-btn-ghost" type="button" data-close-user-form>Cancelar</button>
                    <button class="ad-btn" type="submit">Crear usuario</button>
                </div>
            </form>
        </div>
    </div>

    <div class="ad-modal" data-user-edit-modal hidden>
        <div class="ad-modal-card">
            <header class="ad-modal-head">
                <div>
                    <h2>Editar usuario</h2>
                    <p>Estado de cuenta y roles.</p>
                </div>
                <button type="button" class="ad-modal-close" data-close-user-edit aria-label="Cerrar">×</button>
            </header>
            <form class="ad-form" method="POST" data-user-edit-form enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="ad-media-upload-block">
                    <div class="ad-ml-preview is-avatar" data-user-edit-preview>
                        <img data-user-edit-img hidden alt="Preview">
                        <span class="ad-ml-placeholder" data-user-edit-empty>@include('partials.admin-icon', ['icon' => 'usuarios'])</span>
                    </div>
                    <div>
                        <label class="ad-ml-upload"><span>Cambiar foto</span><input type="file" name="foto" accept="image/*" data-user-edit-file></label>
                        <label><span>O URL</span><input type="url" name="foto_perfil_url" data-field="foto_url" placeholder="https://..." data-user-edit-url></label>
                    </div>
                </div>
                <label><span>Estado *</span>
                    <select name="estado" data-field="estado" required>
                        @foreach (['PENDIENTE','ACTIVO','INACTIVO','BLOQUEADO'] as $e)
                            <option value="{{ $e }}">{{ $e }}</option>
                        @endforeach
                    </select>
                </label>
                <div>
                    <span class="ad-form-label">Roles</span>
                    <div class="ad-checks">
                        @foreach ($roles as $rol)
                            <label>
                                <input type="checkbox" name="roles[]" value="{{ $rol->id_rol }}" data-role-id="{{ $rol->id_rol }}">
                                {{ $rol->codigo_rol ?? $rol->codigo ?? $rol->nombre_rol ?? ('Rol #'.$rol->id_rol) }}
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="ad-modal-actions">
                    <button class="ad-btn ad-btn-ghost" type="button" data-close-user-edit>Cancelar</button>
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
                    if (!img) return;
                    if (src) { img.src = src; img.hidden = false; if (empty) empty.hidden = true; }
                    else { img.hidden = true; if (empty) empty.hidden = false; }
                }
                file?.addEventListener('change', function () {
                    var f = file.files && file.files[0];
                    if (!f) return;
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

            wirePreview('[data-user-create-file]', '[data-user-create-url]', '[data-user-create-img]', '[data-user-create-empty]');
            var showEditFoto = wirePreview('[data-user-edit-file]', '[data-user-edit-url]', '[data-user-edit-img]', '[data-user-edit-empty]');

            var modal = document.querySelector('[data-user-modal]');
            var openBtn = document.querySelector('[data-open-user-form]');
            function open() { if (!modal) return; modal.hidden = false; modal.classList.add('is-open'); }
            function close() { if (!modal) return; modal.hidden = true; modal.classList.remove('is-open'); }
            openBtn?.addEventListener('click', open);
            document.querySelectorAll('[data-close-user-form]').forEach(function (btn) {
                btn.addEventListener('click', close);
            });
            modal?.addEventListener('click', function (event) {
                if (event.target === modal) close();
            });

            var editModal = document.querySelector('[data-user-edit-modal]');
            var editForm = document.querySelector('[data-user-edit-form]');
            function openEdit(btn) {
                if (!editModal || !editForm) return;
                editForm.action = '{{ url('admin/usuarios') }}/' + btn.getAttribute('data-id');
                editForm.querySelector('[data-field="estado"]').value = btn.getAttribute('data-estado') || 'ACTIVO';
                var fotoUrlField = editForm.querySelector('[data-field="foto_url"]');
                if (fotoUrlField) fotoUrlField.value = btn.getAttribute('data-foto-url') || '';
                if (showEditFoto) showEditFoto(btn.getAttribute('data-foto') || '');
                var roles = (btn.getAttribute('data-roles') || '').split(',').filter(Boolean);
                editForm.querySelectorAll('[data-role-id]').forEach(function (cb) {
                    cb.checked = roles.indexOf(cb.getAttribute('data-role-id')) !== -1;
                });
                editModal.hidden = false;
                editModal.classList.add('is-open');
            }
            function closeEdit() {
                if (!editModal) return;
                editModal.hidden = true;
                editModal.classList.remove('is-open');
            }
            document.querySelectorAll('[data-open-user-edit]').forEach(function (b) {
                b.addEventListener('click', function () { openEdit(b); });
            });
            document.querySelectorAll('[data-close-user-edit]').forEach(function (b) {
                b.addEventListener('click', closeEdit);
            });
            editModal?.addEventListener('click', function (e) { if (e.target === editModal) closeEdit(); });
        })();
    </script>
@endsection
