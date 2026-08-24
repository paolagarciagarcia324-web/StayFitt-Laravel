@extends('layouts.admin')

@section('title', 'Usuarios')
@section('subtitle')
    <span class="ad-page-intro">
        <span class="ad-page-intro-lead">Cuentas de clientes</span>
        <span class="ad-page-intro-note">Estado, roles y foto de perfil desde Admin.</span>
    </span>
@endsection

@section('content')
    <div class="ad-kpis ad-users-kpis">
        <a class="ad-kpi accent-cyan" href="{{ route('admin.clientes.virtuales') }}">
            <span class="ad-kpi-ico">@include('partials.admin-icon', ['icon' => 'clientes-virtual'])</span>
            <div class="ad-kpi-copy">
                <strong>{{ $kpis['virtuales'] ?? 0 }}</strong>
                <span>Virtuales</span>
            </div>
        </a>
        <a class="ad-kpi accent-pink" href="{{ route('admin.clientes.presenciales') }}">
            <span class="ad-kpi-ico">@include('partials.admin-icon', ['icon' => 'clientes-presencial'])</span>
            <div class="ad-kpi-copy">
                <strong>{{ $kpis['presenciales'] ?? 0 }}</strong>
                <span>Presenciales</span>
            </div>
        </a>
        <a class="ad-kpi accent-cyan" href="{{ route('admin.coaches.index') }}">
            <span class="ad-kpi-ico">@include('partials.admin-icon', ['icon' => 'coaches'])</span>
            <div class="ad-kpi-copy">
                <strong>{{ $kpis['coaches'] ?? 0 }}</strong>
                <span>Coaches</span>
            </div>
        </a>
        <a class="ad-kpi accent-pink" href="{{ route('admin.asistentes.index') }}">
            <span class="ad-kpi-ico">@include('partials.admin-icon', ['icon' => 'personas'])</span>
            <div class="ad-kpi-copy">
                <strong>{{ $kpis['asistentes'] ?? 0 }}</strong>
                <span>Asistentes</span>
            </div>
        </a>
    </div>

    <section class="ad-card ad-users">
        <div class="ad-users-bar">
            @include('partials.admin-search', [
                'action' => route('admin.usuarios.index'),
                'value' => $q,
                'placeholder' => 'Buscar por nombre o documento',
                'live' => true,
            ])
            <span class="ad-users-total-badge" title="Cuentas de cliente en el directorio">
                <em>Total</em>
                <strong>{{ $kpis['total'] ?? 0 }}</strong>
            </span>
        </div>

        <table class="ad-table ad-users-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Perfil</th>
                    <th>Nombre</th>
                    <th>Documento</th>
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
                        $tipoDoc = $u->Tipo_documento ?: ($cliente?->tipo_documento ?: '');
                        $numDoc = $u->documento_identidad ?: ($cliente?->documento ?: '');
                        $docLabel = ($tipoDoc || $numDoc)
                            ? trim($tipoDoc.' '.$numDoc)
                            : '—';
                    @endphp
                    <tr>
                        <td>{{ $usuarios->firstItem() + $index }}</td>
                        <td>@include('partials.admin-avatar', ['user' => $u])</td>
                        <td>
                            <strong>{{ $u->nombres }} {{ $u->apellidos }}</strong>
                            <em class="ad-user-mail">{{ $u->correo }}</em>
                        </td>
                        <td>{{ $docLabel }}</td>
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
                                    data-nombres="{{ $u->nombres }}"
                                    data-apellidos="{{ $u->apellidos }}"
                                    data-tipo-documento="{{ $tipoDoc ?: 'CC' }}"
                                    data-documento="{{ $numDoc }}"
                                    data-correo="{{ $u->correo }}"
                                    data-telefono="{{ $u->telefono }}"
                                    data-ciudad="{{ $cliente?->ciudad ?? '' }}"
                                    data-nacimiento="{{ $cliente?->fecha_nacimiento ? \Illuminate\Support\Carbon::parse($cliente->fecha_nacimiento)->format('Y-m-d') : '' }}"
                                    data-genero="{{ $cliente?->genero ?: 'FEMENINO' }}"
                                    data-objetivo="{{ $cliente?->objetivo_principal ?? '' }}"
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
                    <tr><td colspan="9" class="ad-empty">No hay usuarios con ese nombre o documento.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="ad-pagination">{{ $usuarios->links() }}</div>
    </section>

    <div class="ad-modal" data-user-edit-modal hidden>
        <div class="ad-modal-card ad-user-edit-modal">
            <header class="ad-modal-head ad-user-edit-head">
                <div>
                    <h2>Editar usuario</h2>
                    <p>Identidad, foto, estado y roles</p>
                </div>
                <button type="button" class="ad-modal-close" data-close-user-edit aria-label="Cerrar">×</button>
            </header>
            <form class="ad-form ad-user-edit-form" method="POST" data-user-edit-form enctype="multipart/form-data" novalidate>
                @csrf
                @method('PUT')

                <div class="ad-user-edit-body">
                    <section class="ad-user-edit-section ad-user-edit-perfil">
                        <div class="ad-user-edit-perfil-card">
                            <div class="ad-user-edit-perfil-stage">
                                <button type="button" class="ad-user-edit-avatar" data-user-edit-preview data-open-user-foto aria-label="Ver foto ampliada">
                                    <img data-user-edit-img hidden alt="Foto de perfil">
                                    <span class="ad-user-edit-avatar-empty" data-user-edit-empty>@include('partials.admin-icon', ['icon' => 'personas'])</span>
                                    <span class="ad-user-edit-avatar-ring" aria-hidden="true"></span>
                                    <span class="ad-user-edit-avatar-hint" aria-hidden="true">
                                        <em>Ampliar</em>
                                    </span>
                                </button>
                            </div>
                            <div class="ad-user-edit-perfil-content">
                                <header class="ad-user-edit-perfil-head">
                                    <span class="ad-user-edit-tag">Perfil</span>
                                    <strong>Foto de perfil</strong>
                                    <p>Imagen clara del rostro. Formatos JPG, PNG o WEBP.</p>
                                </header>
                                <div class="ad-user-edit-media">
                                    <label class="ad-user-edit-upload">
                                        <span>Subir archivo</span>
                                        <span class="ad-user-edit-upload-field" data-user-edit-file-label>Desde el dispositivo</span>
                                        <input type="file" name="foto" accept="image/*" data-user-edit-file>
                                    </label>
                                    <div class="ad-user-edit-media-or" aria-hidden="true"><span>o</span></div>
                                    <label class="ad-user-edit-url">
                                        <span>Enlace externo</span>
                                        <input type="url" name="foto_perfil_url" data-field="foto_url" placeholder="https://tu-imagen.com/foto.jpg" data-user-edit-url>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="ad-user-edit-section">
                        <h3 class="ad-user-edit-title">Identidad</h3>
                        <div class="ad-form-row">
                            <label>
                                <span>Nombres *</span>
                                <input type="text" name="nombres" data-field="nombres" required>
                            </label>
                            <label>
                                <span>Apellidos *</span>
                                <input type="text" name="apellidos" data-field="apellidos" required>
                            </label>
                        </div>
                        <div class="ad-form-row">
                            <label>
                                <span>Tipo de documento *</span>
                                <select name="tipo_documento" data-field="tipo_documento" required>
                                    @foreach (['CC' => 'Cédula', 'CE' => 'Cédula extranjería', 'TI' => 'Tarjeta identidad', 'PASAPORTE' => 'Pasaporte', 'OTRO' => 'Otro'] as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label>
                                <span>Documento *</span>
                                <input type="text" name="documento_identidad" data-field="documento" required>
                            </label>
                        </div>
                        <div class="ad-form-row">
                            <label>
                                <span>Correo *</span>
                                <input type="email" name="correo" data-field="correo" required>
                            </label>
                            <label>
                                <span>Teléfono *</span>
                                <input type="text" name="telefono" data-field="telefono" required>
                            </label>
                        </div>
                        <div class="ad-form-row three">
                            <label>
                                <span>Ciudad *</span>
                                <input type="text" name="ciudad" data-field="ciudad" required>
                            </label>
                            <label>
                                <span>Nacimiento *</span>
                                <input type="date" name="fecha_nacimiento" data-field="nacimiento" required>
                            </label>
                            <label>
                                <span>Género *</span>
                                <select name="genero" data-field="genero" required>
                                    <option value="FEMENINO">Femenino</option>
                                    <option value="MASCULINO">Masculino</option>
                                </select>
                            </label>
                        </div>
                        <label>
                            <span>Objetivo *</span>
                            <input type="text" name="objetivo_principal" data-field="objetivo" placeholder="Ej. fuerza, recomposición, movilidad" required>
                        </label>
                    </section>

                    <section class="ad-user-edit-section">
                        <h3 class="ad-user-edit-title">Estado de cuenta</h3>
                        <label>
                            <span>Estado *</span>
                            <select name="estado" data-field="estado" required>
                                @foreach (['PENDIENTE','ACTIVO','INACTIVO','BLOQUEADO'] as $e)
                                    <option value="{{ $e }}">{{ $e }}</option>
                                @endforeach
                            </select>
                        </label>
                    </section>

                    <section class="ad-user-edit-section">
                        <h3 class="ad-user-edit-title">Roles</h3>
                        <div class="ad-check-rows">
                            @foreach ($roles as $rol)
                                <label class="ad-check-row">
                                    <input type="checkbox" name="roles[]" value="{{ $rol->id_rol }}" data-role-id="{{ $rol->id_rol }}">
                                    <span>{{ $rol->codigo_rol ?? $rol->codigo ?? $rol->nombre_rol ?? ('Rol #'.$rol->id_rol) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </section>
                </div>

                <div class="ad-modal-actions ad-user-edit-actions">
                    <button class="ad-btn ad-btn-ghost" type="button" data-close-user-edit>Cancelar</button>
                    <button class="ad-btn" type="submit">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="ad-lightbox" data-user-foto-lightbox hidden>
        <button type="button" class="ad-lightbox-close" data-close-user-foto aria-label="Cerrar">×</button>
        <div class="ad-lightbox-panel">
            <img src="" alt="Foto de perfil" data-user-foto-lightbox-img>
        </div>
    </div>

    <div class="ad-modal" data-user-confirm-modal hidden>
        <div class="ad-modal-card" role="dialog" aria-modal="true" aria-labelledby="ad-user-confirm-title">
            <div class="ad-modal-head">
                <div>
                    <h2 id="ad-user-confirm-title" data-user-confirm-title>Confirmar acción</h2>
                    <p data-user-confirm-body>¿Deseas continuar?</p>
                </div>
                <button type="button" class="ad-modal-close" data-user-confirm-cancel aria-label="Cerrar">×</button>
            </div>
            <div class="ad-modal-actions">
                <button type="button" class="ad-btn ad-btn-ghost" data-user-confirm-cancel>Cancelar</button>
                <button type="button" class="ad-btn" data-user-confirm-ok>Confirmar</button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            function wirePreview(fileSel, urlSel, imgSel, emptySel, avatarSel) {
                var file = document.querySelector(fileSel);
                var url = document.querySelector(urlSel);
                var img = document.querySelector(imgSel);
                var empty = document.querySelector(emptySel);
                var avatar = avatarSel ? document.querySelector(avatarSel) : null;
                function show(src) {
                    if (!img) return;
                    if (src) {
                        img.src = src;
                        img.hidden = false;
                        if (empty) empty.hidden = true;
                        if (avatar) avatar.classList.add('has-photo');
                    } else {
                        img.hidden = true;
                        img.removeAttribute('src');
                        if (empty) empty.hidden = false;
                        if (avatar) avatar.classList.remove('has-photo');
                    }
                }
                file?.addEventListener('change', function () {
                    var f = file.files && file.files[0];
                    if (!f) return;
                    var label = document.querySelector('[data-user-edit-file-label]');
                    if (label && file.matches('[data-user-edit-file]')) label.textContent = f.name;
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

            var showEditFoto = wirePreview(
                '[data-user-edit-file]',
                '[data-user-edit-url]',
                '[data-user-edit-img]',
                '[data-user-edit-empty]',
                '[data-user-edit-preview]'
            );

            var editModal = document.querySelector('[data-user-edit-modal]');
            var editForm = document.querySelector('[data-user-edit-form]');
            var editImg = document.querySelector('[data-user-edit-img]');
            var fotoLightbox = document.querySelector('[data-user-foto-lightbox]');
            var fotoLightboxImg = document.querySelector('[data-user-foto-lightbox-img]');

            function openFotoLightbox() {
                if (!fotoLightbox || !fotoLightboxImg || !editImg || editImg.hidden || !editImg.getAttribute('src')) return;
                fotoLightboxImg.src = editImg.getAttribute('src');
                fotoLightbox.removeAttribute('hidden');
            }
            function closeFotoLightbox() {
                if (!fotoLightbox) return;
                fotoLightbox.setAttribute('hidden', '');
                if (fotoLightboxImg) fotoLightboxImg.removeAttribute('src');
            }

            document.querySelector('[data-open-user-foto]')?.addEventListener('click', function () {
                openFotoLightbox();
            });
            fotoLightbox?.addEventListener('click', function (e) {
                if (e.target === fotoLightbox || e.target.closest('[data-close-user-foto]')) closeFotoLightbox();
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && fotoLightbox && !fotoLightbox.hasAttribute('hidden')) {
                    closeFotoLightbox();
                }
            });

            var confirmModal = document.querySelector('[data-user-confirm-modal]');
            var confirmTitle = confirmModal?.querySelector('[data-user-confirm-title]');
            var confirmBody = confirmModal?.querySelector('[data-user-confirm-body]');
            var confirmOk = confirmModal?.querySelector('[data-user-confirm-ok]');
            var pendingForm = null;

            function openConfirm(form, title, body, okLabel) {
                pendingForm = form;
                if (!confirmModal) return;
                if (confirmTitle) confirmTitle.textContent = title;
                if (confirmBody) confirmBody.textContent = body;
                if (confirmOk) confirmOk.textContent = okLabel;
                confirmModal.hidden = false;
                confirmModal.classList.add('is-open');
            }
            function closeConfirm() {
                pendingForm = null;
                if (!confirmModal) return;
                confirmModal.hidden = true;
                confirmModal.classList.remove('is-open');
            }
            function formIsComplete(form) {
                if (!form) return false;
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return false;
                }
                if (form === editForm) {
                    var roles = form.querySelectorAll('[data-role-id]:checked');
                    if (!roles.length) {
                        var firstRole = form.querySelector('[data-role-id]');
                        if (firstRole) {
                            firstRole.setCustomValidity('Selecciona al menos un rol.');
                            firstRole.reportValidity();
                            firstRole.setCustomValidity('');
                        }
                        return false;
                    }
                }
                return true;
            }
            function interceptSubmit(form, title, body, okLabel) {
                if (!form) return;
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    if (!formIsComplete(form)) return;
                    openConfirm(form, title, body, okLabel);
                });
            }
            interceptSubmit(
                createForm,
                '¿Crear usuario?',
                'Se creará la cuenta con los datos del formulario. Confirma solo si todo está correcto.',
                'Sí, crear'
            );
            interceptSubmit(
                editForm,
                '¿Guardar cambios?',
                'Se actualizarán la identidad, la foto, el estado y los roles de este usuario.',
                'Sí, guardar'
            );
            document.querySelectorAll('[data-user-confirm-cancel]').forEach(function (b) {
                b.addEventListener('click', closeConfirm);
            });
            confirmOk?.addEventListener('click', function () {
                if (!pendingForm) return;
                var form = pendingForm;
                closeConfirm();
                HTMLFormElement.prototype.submit.call(form);
            });
            confirmModal?.addEventListener('click', function (e) {
                if (e.target === confirmModal) closeConfirm();
            });

            function openEdit(btn) {
                if (!editModal || !editForm) return;
                editForm.action = '{{ url('admin/usuarios') }}/' + btn.getAttribute('data-id');
                var fields = {
                    nombres: 'data-nombres',
                    apellidos: 'data-apellidos',
                    tipo_documento: 'data-tipo-documento',
                    documento: 'data-documento',
                    correo: 'data-correo',
                    telefono: 'data-telefono',
                    ciudad: 'data-ciudad',
                    nacimiento: 'data-nacimiento',
                    genero: 'data-genero',
                    objetivo: 'data-objetivo',
                    estado: 'data-estado'
                };
                Object.keys(fields).forEach(function (key) {
                    var el = editForm.querySelector('[data-field="' + key + '"]');
                    if (el) el.value = btn.getAttribute(fields[key]) || '';
                });
                if (!editForm.querySelector('[data-field="estado"]').value) {
                    editForm.querySelector('[data-field="estado"]').value = 'ACTIVO';
                }
                if (!editForm.querySelector('[data-field="genero"]').value) {
                    editForm.querySelector('[data-field="genero"]').value = 'FEMENINO';
                }
                if (!editForm.querySelector('[data-field="tipo_documento"]').value) {
                    editForm.querySelector('[data-field="tipo_documento"]').value = 'CC';
                }
                var fotoUrlField = editForm.querySelector('[data-field="foto_url"]');
                if (fotoUrlField) fotoUrlField.value = btn.getAttribute('data-foto-url') || '';
                var fileInput = editForm.querySelector('[data-user-edit-file]');
                if (fileInput) fileInput.value = '';
                var fileLabel = document.querySelector('[data-user-edit-file-label]');
                if (fileLabel) fileLabel.textContent = 'Desde el dispositivo';
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
                closeFotoLightbox();
                closeConfirm();
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

    @php $ok = session('usuario_ok'); @endphp
    @if (is_array($ok) && ! empty($ok['accion']))
        @php
            $accion = $ok['accion'] ?? '';
            $accionLabel = match ($accion) {
                'creado' => 'Usuario creado',
                default => 'Usuario actualizado',
            };
            $detalle = match ($accion) {
                'creado' => 'La cuenta quedó lista. Acceso inicial: correo + documento.',
                default => 'Los cambios de identidad, foto, estado y roles se guardaron correctamente.',
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
        <script>
            (function () {
                var box = document.querySelector('[data-ok-modal]');
                if (!box) return;
                document.body.style.overflow = 'hidden';
                function closeOk() {
                    box.hidden = true;
                    box.classList.remove('is-open');
                    document.body.style.overflow = '';
                }
                box.querySelectorAll('[data-close-ok]').forEach(function (b) {
                    b.addEventListener('click', closeOk);
                });
                box.addEventListener('click', function (e) {
                    if (e.target === box) closeOk();
                });
                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') closeOk();
                });
            })();
        </script>
    @endif
@endsection
