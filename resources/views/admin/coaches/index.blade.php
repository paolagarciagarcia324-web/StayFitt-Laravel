@extends('layouts.admin')

@section('title', 'Coaches')
@section('subtitle')
    <span class="ad-page-intro">
        <span class="ad-page-intro-lead">Equipo de coaches</span>
        <span class="ad-page-intro-note">Especialidad, clientes asignados y estado del coach.</span>
    </span>
@endsection

@section('content')
    @if (session('coach_temp_password'))
        <div class="ad-card" style="margin-bottom:16px;border-color:var(--cyan)">
            <strong>Contraseña temporal del coach:</strong> <code>{{ session('coach_temp_password') }}</code>
            <span class="ad-user-mail"> — Comunicar al coach de forma segura.</span>
        </div>
    @endif

    <section class="ad-card ad-users">
        <div class="ad-users-bar">
            @include('partials.admin-search', [
                'action' => route('admin.coaches.index'),
                'value' => $q,
                'placeholder' => 'Buscar por nombre',
                'live' => true,
            ])
            <button class="ad-btn ad-btn-add" type="button" data-open-coach-form>+ Crear coach</button>
        </div>

        <div class="ad-people-grid">
            @forelse ($coaches as $index => $c)
                @php
                    $u = $c->usuario;
                    $nombre = trim(($u?->nombres).' '.($u?->apellidos)) ?: 'Coach';
                    $searchBlob = mb_strtolower(trim($nombre.' '.($u?->correo ?? '').' '.($c->especialidad ?? '')));
                @endphp
                <article class="ad-people-card" data-inst-card data-search="{{ $searchBlob }}">
                    <span class="ad-people-status {{ $c->estado_coach === 'ACTIVO' ? 'is-ok' : ($c->estado_coach === 'VACACIONES' ? 'is-wait' : 'is-off') }}">{{ $c->estado_coach }}</span>
                    <header>
                        @include('partials.admin-avatar', ['user' => $u, 'zoomable' => true])
                        <div>
                            <strong>{{ $nombre }}</strong>
                            <em class="ad-user-mail">{{ $c->especialidad ?: 'Coach StayFit' }}</em>
                        </div>
                    </header>
                    <div class="ad-people-meta">
                        <span><em>Correo</em> {{ $u?->correo ?: '—' }}</span>
                        <span><em>Teléfono</em> {{ $u?->telefono ?: '—' }}</span>
                        <span><em>Clientes</em> {{ $c->clientes->count() }}</span>
                        <span><em>Alta</em> {{ optional($c->creado_en)->format('d M Y') ?: '—' }}</span>
                    </div>
                    <div class="ad-people-actions">
                        <button class="ad-btn" type="button"
                            data-open-coach-edit
                            data-id="{{ $c->id_coach }}"
                            data-especialidad="{{ $c->especialidad }}"
                            data-biografia="{{ $c->biografia }}"
                            data-certificaciones="{{ $c->certificaciones }}"
                            data-estado="{{ $c->estado_coach }}"
                            data-telefono="{{ $u?->telefono }}">Editar</button>
                        @if ($c->estado_coach === 'ACTIVO')
                            <form method="POST" action="{{ route('admin.coaches.destroy', $c->id_coach) }}" onsubmit="return confirm('¿Desactivar este coach?')">
                                @csrf
                                @method('DELETE')
                                <button class="ad-btn ad-btn-ghost" type="submit">Desactivar</button>
                            </form>
                        @endif
                    </div>
                </article>
            @empty
                <p class="ad-empty">No hay coaches registrados.</p>
            @endforelse
        </div>
        <p class="ad-live-search-empty" data-live-search-empty hidden>Ningún coach coincide con esa búsqueda.</p>
        <div class="ad-pagination">{{ $coaches->links() }}</div>
    </section>

    <div class="ad-modal {{ $errors->any() ? 'is-open' : '' }}" data-coach-modal {{ $errors->any() ? '' : 'hidden' }}>
        <div class="ad-modal-card">
            <header class="ad-modal-head">
                <div>
                    <h2>Nuevo coach</h2>
                    <p>Cuenta de acceso + perfil profesional.</p>
                </div>
                <button type="button" class="ad-modal-close" data-close-coach-form aria-label="Cerrar">×</button>
            </header>
            @if ($errors->any())
                <div class="ad-alert ad-alert-error"><ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif
            <form class="ad-form" method="POST" action="{{ route('admin.coaches.store') }}">
                @csrf
                <div class="ad-form-block"><h3>Datos personales</h3>
                    <div class="ad-form-row">
                        <label><span>Nombres *</span><input type="text" name="nombres" value="{{ old('nombres') }}" required></label>
                        <label><span>Apellidos *</span><input type="text" name="apellidos" value="{{ old('apellidos') }}" required></label>
                    </div>
                    <div class="ad-form-row">
                        <label><span>Correo *</span><input type="email" name="correo" value="{{ old('correo') }}" required></label>
                        <label><span>Documento *</span><input type="text" name="documento_identidad" value="{{ old('documento_identidad') }}" required></label>
                    </div>
                    <label><span>Teléfono</span><input type="text" name="telefono" value="{{ old('telefono') }}"></label>
                </div>
                <div class="ad-form-block"><h3>Perfil coach</h3>
                    <div class="ad-form-row">
                        <label><span>Código</span><input type="text" name="codigo_coach" value="{{ old('codigo_coach') }}" placeholder="Auto si vacío"></label>
                        <label><span>Especialidad *</span><input type="text" name="especialidad" value="{{ old('especialidad') }}" required></label>
                    </div>
                    <label><span>Biografía</span><textarea name="biografia" rows="2">{{ old('biografia') }}</textarea></label>
                    <label><span>Certificaciones</span><input type="text" name="certificaciones" value="{{ old('certificaciones') }}"></label>
                    <label><span>Estado *</span>
                        <select name="estado_coach" required>
                            @foreach (['ACTIVO', 'INACTIVO', 'VACACIONES'] as $e)
                                <option value="{{ $e }}" @selected(old('estado_coach', 'ACTIVO') === $e)>{{ $e }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
                <div class="ad-modal-actions">
                    <button class="ad-btn ad-btn-ghost" type="button" data-close-coach-form>Cancelar</button>
                    <button class="ad-btn" type="submit">Crear coach</button>
                </div>
            </form>
        </div>
    </div>

    <div class="ad-modal" data-coach-edit-modal hidden>
        <div class="ad-modal-card ad-coach-edit-card">
            <header class="ad-modal-head ad-coach-edit-head">
                <div>
                    <h2>Editar coach</h2>
                    <p>Actualiza perfil profesional, contacto y estado operativo</p>
                </div>
                <button type="button" class="ad-modal-close" data-close-coach-edit aria-label="Cerrar">×</button>
            </header>
            <form class="ad-form ad-coach-edit-form" method="POST" data-coach-edit-form>
                @csrf
                @method('PUT')
                <div class="ad-coach-edit-body">
                    <section class="ad-coach-edit-section">
                        <h3 class="ad-coach-edit-title">Perfil</h3>
                        <div class="ad-form-row">
                            <label><span>Especialidad *</span><input type="text" name="especialidad" data-field="especialidad" required></label>
                            <label><span>Certificaciones</span><input type="text" name="certificaciones" data-field="certificaciones" placeholder="Ej. NSCA, ACE"></label>
                        </div>
                    </section>
                    <section class="ad-coach-edit-section">
                        <h3 class="ad-coach-edit-title">Contacto</h3>
                        <label><span>Teléfono</span><input type="text" name="telefono" data-field="telefono"></label>
                        <label><span>Biografía</span><textarea name="biografia" rows="3" data-field="biografia" placeholder="Breve perfil del coach"></textarea></label>
                    </section>
                    <section class="ad-coach-edit-section">
                        <h3 class="ad-coach-edit-title">Estado</h3>
                        <label><span>Estado operativo *</span>
                            <select name="estado_coach" data-field="estado" required>
                                @foreach (['ACTIVO', 'INACTIVO', 'VACACIONES', 'SUSPENDIDO'] as $e)
                                    <option value="{{ $e }}">{{ $e }}</option>
                                @endforeach
                            </select>
                        </label>
                    </section>
                </div>
                <div class="ad-modal-actions ad-coach-edit-actions">
                    <button class="ad-btn ad-btn-ghost" type="button" data-close-coach-edit>Cancelar</button>
                    <button class="ad-btn" type="submit">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>

    @include('partials.admin-avatar-zoom')

    <script>
        (function () {
            var modal = document.querySelector('[data-coach-modal]');
            function open() {
                if (!modal) return;
                modal.hidden = false;
                modal.classList.add('is-open');
                document.body.style.overflow = 'hidden';
            }
            function close() {
                if (!modal) return;
                modal.hidden = true;
                modal.classList.remove('is-open');
                document.body.style.overflow = '';
            }
            document.querySelector('[data-open-coach-form]')?.addEventListener('click', open);
            document.querySelectorAll('[data-close-coach-form]').forEach(function (b) { b.addEventListener('click', close); });
            modal?.addEventListener('click', function (e) { if (e.target === modal) close(); });

            var editModal = document.querySelector('[data-coach-edit-modal]');
            var editForm = document.querySelector('[data-coach-edit-form]');
            function openEdit(btn) {
                if (!editModal || !editForm) return;
                editForm.action = '{{ url('admin/coaches') }}/' + btn.getAttribute('data-id');
                editForm.querySelector('[data-field="especialidad"]').value = btn.getAttribute('data-especialidad') || '';
                editForm.querySelector('[data-field="telefono"]').value = btn.getAttribute('data-telefono') || '';
                editForm.querySelector('[data-field="biografia"]').value = btn.getAttribute('data-biografia') || '';
                editForm.querySelector('[data-field="certificaciones"]').value = btn.getAttribute('data-certificaciones') || '';
                editForm.querySelector('[data-field="estado"]').value = btn.getAttribute('data-estado') || 'ACTIVO';
                editModal.hidden = false;
                editModal.classList.add('is-open');
                document.body.style.overflow = 'hidden';
            }
            function closeEdit() {
                if (!editModal) return;
                editModal.hidden = true;
                editModal.classList.remove('is-open');
                document.body.style.overflow = '';
            }
            document.querySelectorAll('[data-open-coach-edit]').forEach(function (b) {
                b.addEventListener('click', function () { openEdit(b); });
            });
            document.querySelectorAll('[data-close-coach-edit]').forEach(function (b) {
                b.addEventListener('click', closeEdit);
            });
            editModal?.addEventListener('click', function (e) { if (e.target === editModal) closeEdit(); });
        })();
    </script>
@endsection
