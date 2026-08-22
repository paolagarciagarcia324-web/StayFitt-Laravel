@extends('layouts.admin')

@section('title', 'Horarios')
@section('subtitle')
    <span class="ad-page-intro">
        <span class="ad-page-intro-lead">Gestión de sesiones, disponibilidad y comunidades para servicios presenciales.</span>
        <span class="ad-page-intro-note">Los horarios con cupos disponibles se habilitan automáticamente para el registro presencial.</span>
    </span>
@endsection

@section('actions')
    <button class="ad-btn" type="button" data-open-horario-create>Agregar horario</button>
@endsection

@section('content')
    <section class="ad-horario-board">
        @forelse ($horarios as $horario)
            @php
                $grupoWa = $horario->nombreGrupoWhatsapp();
                $urlWa = $horario->urlGrupoWhatsapp();
                $cupoMax = max(1, (int) $horario->cupo_maximo);
                $cupoPct = min(100, (int) round(((int) $horario->cupo_ocupado / $cupoMax) * 100));
                $horaHero = \App\Models\HorarioPresencial::formatoHora12((string) $horario->hora_inicio);
            @endphp
            <article class="ad-horario-card {{ $horario->activo ? 'is-on' : 'is-off' }}">
                <div class="ad-horario-card-top">
                    <div class="ad-horario-time" aria-hidden="true">
                        <strong>{{ $horaHero }}</strong>
                        <span>{{ \App\Models\HorarioPresencial::formatoHora12((string) $horario->hora_fin) }}</span>
                    </div>
                    <span class="ad-badge {{ $horario->activo ? 'ad-badge-ok' : 'ad-badge-bad' }}">
                        {{ $horario->activo ? 'ACTIVO' : 'INACTIVO' }}
                    </span>
                </div>

                <h3>{{ $horario->nombre }}</h3>
                <p class="ad-horario-code">{{ $horario->codigo }}</p>

                <div class="ad-horario-days">
                    @foreach (preg_split('/\s*·\s*/u', (string) $horario->dias) as $dia)
                        @if (trim($dia) !== '')
                            <span>{{ trim($dia) }}</span>
                        @endif
                    @endforeach
                </div>

                <div class="ad-horario-cupo">
                    <div class="ad-horario-cupo-meta">
                        <span>Cupo</span>
                        <strong>{{ $horario->cupo_ocupado }}/{{ $horario->cupo_maximo }}</strong>
                    </div>
                    <div class="ad-horario-cupo-bar" role="presentation">
                        <i style="width: {{ $cupoPct }}%"></i>
                    </div>
                </div>

                <a class="ad-horario-wa"
                   href="{{ $urlWa }}"
                   target="_blank"
                   rel="noopener"
                   title="Abrir {{ $grupoWa }}">
                    <span>WhatsApp</span> {{ $grupoWa }}
                </a>

                <div class="ad-horario-card-actions">
                    <button class="ad-icon-btn" type="button"
                        title="Editar"
                        aria-label="Editar horario"
                        data-open-horario
                        data-id="{{ $horario->id_horario }}"
                        data-nombre="{{ $horario->nombre }}"
                        data-dias="{{ $horario->dias }}"
                        data-hora-inicio="{{ substr((string) $horario->hora_inicio, 0, 5) }}"
                        data-hora-fin="{{ substr((string) $horario->hora_fin, 0, 5) }}"
                        data-link="{{ $grupoWa }}"
                        data-cupo="{{ $horario->cupo_maximo }}"
                        data-activo="{{ $horario->activo ? '1' : '0' }}">
                        @include('partials.admin-icon', ['icon' => 'edit'])
                    </button>
                    <form method="POST" action="{{ route('admin.horarios.destroy', $horario->id_horario) }}" class="ad-inline-form" data-horario-delete-form>
                        @csrf
                        @method('DELETE')
                        <button class="ad-icon-btn is-danger" type="button" title="Eliminar" aria-label="Eliminar horario" data-horario-delete>
                            @include('partials.admin-icon', ['icon' => 'trash'])
                        </button>
                    </form>
                </div>
            </article>
        @empty
            <div class="ad-empty-hero ad-horario-empty">
                <span class="ad-empty-orb">@include('partials.admin-icon', ['icon' => 'horarios'])</span>
                <strong>Sin horarios</strong>
                <button class="ad-btn" type="button" data-open-horario-create>Agregar el primero</button>
            </div>
        @endforelse
    </section>

    <div class="ad-modal" data-horario-modal hidden>
        <div class="ad-modal-card">
            <header class="ad-modal-head">
                <div>
                    <h2 data-horario-title>Editar horario</h2>
                </div>
                <button type="button" class="ad-modal-close" data-close-horario aria-label="Cerrar">×</button>
            </header>
            <form class="ad-form" method="POST" data-horario-form>
                @csrf
                <input type="hidden" name="_method" value="PUT" data-horario-method>
                <div class="ad-horario-identity-fields" data-identity-fields>
                    <label>
                        <span>Nombre *</span>
                        <input type="text" name="nombre" data-field="nombre" maxlength="120" placeholder="Mañana intensiva" required>
                    </label>
                    <label>
                        <span>Días *</span>
                        <input type="text" name="dias" data-field="dias" maxlength="80" placeholder="Lun · Mié · Vie" required>
                    </label>
                    <div class="ad-horario-time-fields">
                        <label>
                            <span>Hora inicio *</span>
                            <input type="time" name="hora_inicio" data-field="hora_inicio" required>
                        </label>
                        <label>
                            <span>Hora fin *</span>
                            <input type="time" name="hora_fin" data-field="hora_fin" required>
                        </label>
                    </div>
                </div>
                <label>
                    <span>Grupo WhatsApp</span>
                    <input type="text" name="link_whatsapp" data-field="link" maxlength="300" placeholder="FigueFit CLUB 6:00 AM">
                </label>
                <label><span>Cupo máximo *</span><input type="number" name="cupo_maximo" data-field="cupo" min="1" max="80" required></label>
                <label class="ad-horario-activo-toggle" data-activo-toggle>
                    <input type="checkbox" name="activo" value="1" data-field="activo">
                    <span class="ad-horario-activo-ui" aria-hidden="true"></span>
                    <span class="ad-horario-activo-text">
                        <strong data-activo-label>Activo</strong>
                        <em data-activo-hint>Visible en el registro presencial</em>
                    </span>
                </label>
                <p class="ad-page-intro-note" data-create-hint hidden style="margin:0 0 8px">
                    Si lo dejas activo, se mostrará al instante en el registro presencial.
                </p>
                <div class="ad-modal-actions">
                    <button class="ad-btn ad-btn-ghost" type="button" data-close-horario>Cancelar</button>
                    <button class="ad-btn" type="submit" data-horario-submit>Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="ad-modal" data-horario-confirm-modal hidden>
        <div class="ad-modal-card" role="dialog" aria-modal="true" aria-labelledby="ad-horario-del-title">
            <header class="ad-modal-head">
                <div>
                    <h2 id="ad-horario-del-title">Eliminar horario</h2>
                    <p>Esta acción no se puede deshacer.</p>
                </div>
                <button type="button" class="ad-modal-close" data-horario-confirm-cancel aria-label="Cerrar">×</button>
            </header>
            <div class="ad-modal-actions">
                <button type="button" class="ad-btn ad-btn-ghost" data-horario-confirm-cancel>Cancelar</button>
                <button type="button" class="ad-btn is-danger" data-horario-confirm-ok>Sí, eliminar</button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var modal = document.querySelector('[data-horario-modal]');
            var form = document.querySelector('[data-horario-form]');
            var title = document.querySelector('[data-horario-title]');
            var methodInput = document.querySelector('[data-horario-method]');
            var createHint = document.querySelector('[data-create-hint]');
            var submitBtn = document.querySelector('[data-horario-submit]');
            var activoInput = form && form.querySelector('[data-field="activo"]');
            var activoLabel = form && form.querySelector('[data-activo-label]');
            var activoHint = form && form.querySelector('[data-activo-hint]');
            var storeUrl = @json(route('admin.horarios.store'));
            var confirmModal = document.querySelector('[data-horario-confirm-modal]');
            var pendingDeleteForm = null;

            function syncActivoUi() {
                if (!activoInput) return;
                var on = !!activoInput.checked;
                if (activoLabel) activoLabel.textContent = on ? 'Activo' : 'Inactivo';
                if (activoHint) activoHint.textContent = on
                    ? 'Visible en el registro presencial'
                    : 'Oculto en el registro presencial';
                form.querySelector('[data-activo-toggle]')?.classList.toggle('is-off', !on);
            }

            function setCreateMode(isCreate) {
                if (createHint) createHint.hidden = !isCreate;
                if (methodInput) methodInput.disabled = isCreate;
                if (submitBtn) submitBtn.textContent = isCreate ? 'Crear horario' : 'Guardar';
            }

            function openEdit(btn) {
                if (!modal || !form) return;
                setCreateMode(false);
                form.action = '{{ url('admin/horarios') }}/' + btn.getAttribute('data-id');
                if (methodInput) methodInput.value = 'PUT';
                title.textContent = 'Editar horario';
                form.querySelector('[data-field="nombre"]').value = btn.getAttribute('data-nombre') || '';
                form.querySelector('[data-field="dias"]').value = btn.getAttribute('data-dias') || '';
                form.querySelector('[data-field="hora_inicio"]').value = btn.getAttribute('data-hora-inicio') || '';
                form.querySelector('[data-field="hora_fin"]').value = btn.getAttribute('data-hora-fin') || '';
                form.querySelector('[data-field="link"]').value = btn.getAttribute('data-link') || '';
                form.querySelector('[data-field="cupo"]').value = btn.getAttribute('data-cupo') || '';
                if (activoInput) activoInput.checked = btn.getAttribute('data-activo') === '1';
                syncActivoUi();
                modal.hidden = false;
                modal.classList.add('is-open');
                document.body.style.overflow = 'hidden';
            }

            function openCreate() {
                if (!modal || !form) return;
                setCreateMode(true);
                form.action = storeUrl;
                if (methodInput) methodInput.value = 'POST';
                title.textContent = 'Nuevo horario';
                form.querySelector('[data-field="nombre"]').value = '';
                form.querySelector('[data-field="link"]').value = '';
                form.querySelector('[data-field="cupo"]').value = '18';
                if (activoInput) activoInput.checked = true;
                form.querySelector('[data-field="dias"]').value = 'Lun · Mié · Vie';
                form.querySelector('[data-field="hora_inicio"]').value = '06:00';
                form.querySelector('[data-field="hora_fin"]').value = '07:00';
                syncActivoUi();
                modal.hidden = false;
                modal.classList.add('is-open');
                document.body.style.overflow = 'hidden';
            }

            function close() {
                if (modal) {
                    modal.hidden = true;
                    modal.classList.remove('is-open');
                    document.body.style.overflow = '';
                }
            }

            activoInput?.addEventListener('change', syncActivoUi);

            document.querySelectorAll('[data-open-horario]').forEach(function (b) {
                b.addEventListener('click', function () { openEdit(b); });
            });
            document.querySelectorAll('[data-open-horario-create]').forEach(function (b) {
                b.addEventListener('click', openCreate);
            });
            document.querySelectorAll('[data-close-horario]').forEach(function (b) {
                b.addEventListener('click', close);
            });
            modal?.addEventListener('click', function (e) { if (e.target === modal) close(); });

            function openConfirm(deleteForm) {
                pendingDeleteForm = deleteForm;
                if (!confirmModal) return;
                confirmModal.hidden = false;
                confirmModal.classList.add('is-open');
                document.body.style.overflow = 'hidden';
            }
            function closeConfirm() {
                pendingDeleteForm = null;
                if (!confirmModal) return;
                confirmModal.hidden = true;
                confirmModal.classList.remove('is-open');
                document.body.style.overflow = '';
            }
            document.querySelectorAll('[data-horario-delete]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var f = btn.closest('[data-horario-delete-form]');
                    if (f) openConfirm(f);
                });
            });
            document.querySelectorAll('[data-horario-confirm-cancel]').forEach(function (b) {
                b.addEventListener('click', closeConfirm);
            });
            confirmModal?.querySelector('[data-horario-confirm-ok]')?.addEventListener('click', function () {
                if (pendingDeleteForm) pendingDeleteForm.submit();
            });
            confirmModal?.addEventListener('click', function (e) {
                if (e.target === confirmModal) closeConfirm();
            });
        })();
    </script>

    @php $ok = session('horario_ok'); @endphp
    @if (is_array($ok) && ! empty($ok['accion']))
        @php
            $accion = $ok['accion'] ?? '';
            $accionLabel = match ($accion) {
                'creado' => 'Horario creado',
                'eliminado' => 'Horario eliminado',
                default => 'Horario actualizado',
            };
            $detalle = match ($accion) {
                'eliminado' => 'El horario se quitó del panel y del registro presencial.',
                default => ! empty($ok['activo'])
                    ? 'Ya queda listo para la sala y, si está activo, en el registro presencial.'
                    : 'Quedó guardado. Actívalo cuando quieras mostrarlo en el registro presencial.',
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
