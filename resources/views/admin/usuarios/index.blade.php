@extends('layouts.admin')

@section('title', 'Usuarios')
@section('subtitle', 'Cuentas del sistema')

@section('content')
    <section class="ad-card ad-users">
        <div class="ad-users-bar">
            <form class="ad-users-search" method="GET" action="{{ route('admin.usuarios.index') }}">
                <span>⌕</span>
                <input type="search" name="q" value="{{ $q }}" placeholder="Buscar por nombre">
            </form>
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
                        $foto = $u->foto_perfil_url;
                    @endphp
                    <tr>
                        <td>{{ $usuarios->firstItem() + $index }}</td>
                        <td>
                            @if ($foto)
                                <img class="ad-user-avatar" src="{{ $foto }}" alt="{{ $u->nombres }}">
                            @else
                                <span class="ad-user-avatar is-initials">{{ $u->iniciales() }}</span>
                            @endif
                        </td>
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
                            @if ($cliente?->modalidad === 'PRESENCIAL' && $cliente->fecha_nacimiento)
                                {{ \Illuminate\Support\Carbon::parse($cliente->fecha_nacimiento)->format('d/m/Y') }}
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            <button class="ad-btn ad-btn-ghost" type="button"
                                data-open-user-edit
                                data-id="{{ $u->id_user }}"
                                data-estado="{{ $u->estado ?: $u->estado_cuenta ?: 'ACTIVO' }}"
                                data-roles="{{ $u->roles->pluck('id_rol')->implode(',') }}">Editar</button>
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
        <div class="ad-modal-card">
            <header class="ad-modal-head">
                <div>
                    <h2>Nuevo usuario presencial</h2>
                    <p>Mismos datos de la ficha de inscripción en sala.</p>
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

            <form class="ad-form" method="POST" action="{{ route('admin.usuarios.store') }}">
                @csrf
                <div class="ad-form-block">
                    <h3>01 Datos personales</h3>
                    <div class="ad-form-row">
                        <label><span>Nombres *</span><input type="text" name="nombres" value="{{ old('nombres') }}" required></label>
                        <label><span>Apellidos *</span><input type="text" name="apellidos" value="{{ old('apellidos') }}" required></label>
                    </div>
                    <div class="ad-form-row">
                        <label>
                            <span>Tipo de documento *</span>
                            <select name="tipo_documento" required>
                                @foreach (['CC' => 'Cédula', 'CE' => 'Cédula extranjería', 'TI' => 'Tarjeta identidad', 'PASAPORTE' => 'Pasaporte', 'OTRO' => 'Otro'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('tipo_documento', 'CC') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label><span>Documento *</span><input type="text" name="documento_identidad" value="{{ old('documento_identidad') }}" required></label>
                    </div>
                    <div class="ad-form-row">
                        <label><span>Correo *</span><input type="email" name="correo" value="{{ old('correo') }}" required></label>
                        <label><span>Teléfono *</span><input type="text" name="telefono" value="{{ old('telefono') }}" required></label>
                    </div>
                    <div class="ad-form-row">
                        <label><span>Ciudad *</span><input type="text" name="ciudad" value="{{ old('ciudad') }}" required></label>
                        <label><span>Fecha de nacimiento *</span><input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" required></label>
                    </div>
                    <div class="ad-form-row">
                        <label>
                            <span>Género *</span>
                            <select name="genero" required>
                                <option value="">Selecciona...</option>
                                @foreach (['FEMENINO', 'MASCULINO', 'OTRO', 'NO_INFORMA'] as $genero)
                                    <option value="{{ $genero }}" @selected(old('genero') === $genero)>{{ ucfirst(strtolower(str_replace('_', ' ', $genero))) }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label><span>Objetivo *</span><input type="text" name="objetivo_principal" value="{{ old('objetivo_principal') }}" required></label>
                    </div>
                </div>

                <div class="ad-form-block">
                    <h3>02 Medidas iniciales</h3>
                    <div class="ad-form-row three">
                        <label><span>Peso (kg) *</span><input type="number" step="0.01" name="peso_kg" value="{{ old('peso_kg') }}" required></label>
                        <label><span>Estatura (cm) *</span><input type="number" step="0.01" name="estatura_cm" value="{{ old('estatura_cm') }}" required></label>
                        <label><span>Cintura (cm) *</span><input type="number" step="0.01" name="cintura_cm" value="{{ old('cintura_cm') }}" required></label>
                        <label><span>Cadera (cm) *</span><input type="number" step="0.01" name="cadera_cm" value="{{ old('cadera_cm') }}" required></label>
                        <label><span>Brazo (cm) *</span><input type="number" step="0.01" name="brazo_cm" value="{{ old('brazo_cm') }}" required></label>
                        <label><span>Pierna (cm) *</span><input type="number" step="0.01" name="pierna_cm" value="{{ old('pierna_cm') }}" required></label>
                    </div>
                </div>

                <div class="ad-form-block">
                    <h3>03 Salud y emergencia</h3>
                    <div class="ad-form-row">
                        <label><span>Alergias</span><textarea name="alergias" rows="2">{{ old('alergias') }}</textarea></label>
                        <label><span>Condiciones médicas</span><textarea name="condiciones_medicas" rows="2">{{ old('condiciones_medicas') }}</textarea></label>
                        <label><span>Lesiones</span><textarea name="lesiones" rows="2">{{ old('lesiones') }}</textarea></label>
                        <label><span>Medicamentos</span><textarea name="medicamentos" rows="2">{{ old('medicamentos') }}</textarea></label>
                        <label><span>Contacto de emergencia *</span><input type="text" name="contacto_emergencia_nombre" value="{{ old('contacto_emergencia_nombre') }}" required></label>
                        <label><span>Teléfono de emergencia *</span><input type="text" name="contacto_emergencia_telefono" value="{{ old('contacto_emergencia_telefono') }}" required></label>
                    </div>
                </div>

                <div class="ad-form-block">
                    <h3>04 Horario</h3>
                    <div class="ad-horario-pick">
                        @forelse ($horarios as $horario)
                            <label class="ad-horario-opt">
                                <input type="radio" name="id_horario" value="{{ $horario->id_horario }}" @checked((string) old('id_horario') === (string) $horario->id_horario) required>
                                <span>
                                    <strong>{{ $horario->nombre }}</strong>
                                    <em>{{ $horario->dias }} · {{ substr((string) $horario->hora_inicio, 0, 5) }} – {{ substr((string) $horario->hora_fin, 0, 5) }}</em>
                                </span>
                            </label>
                        @empty
                            <p class="ad-empty">No hay horarios activos.</p>
                        @endforelse
                    </div>
                    <label><span>Observaciones (opcional)</span><textarea name="observacion_usuario" rows="2">{{ old('observacion_usuario') }}</textarea></label>
                </div>

                <div class="ad-modal-actions">
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
            <form class="ad-form" method="POST" data-user-edit-form>
                @csrf
                @method('PUT')
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
