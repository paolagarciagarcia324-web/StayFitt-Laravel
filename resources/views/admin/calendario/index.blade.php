@extends('layouts.admin')

@section('title', 'Calendario')
@section('subtitle')
    <span class="ad-page-intro">
        <span class="ad-page-intro-lead">Agenda operativa de eventos y sesiones.</span>
        <span class="ad-page-intro-note">Fija fechas, evita solapes de coach y recibe recordatorios de lo que viene.</span>
    </span>
@endsection

@section('flash')
@endsection

@section('actions')
    <div class="ad-cal-nav">
        <a class="ad-btn ad-btn-ghost" href="{{ route('admin.calendario.index', ['mes' => $mesAnterior]) }}" aria-label="Mes anterior">‹</a>
        <span class="ad-cal-nav-label">{{ $mes->locale('es')->translatedFormat('F Y') }}</span>
        <a class="ad-btn ad-btn-ghost" href="{{ route('admin.calendario.index', ['mes' => $mesSiguiente]) }}" aria-label="Mes siguiente">›</a>
    </div>
    <button class="ad-btn ad-btn-add" type="button" data-open-sesion-form>+ Añadir sesión</button>
    <a class="ad-btn ad-btn-ghost" href="{{ route('admin.eventos.index') }}">Eventos</a>
@endsection

@section('content')
    <section class="ad-cal-layout">
        <section class="ad-card ad-users">
            <div class="ad-cal-legend">
                <span><i class="is-evento"></i> Evento</span>
                <span><i class="is-sesion"></i> Sesión</span>
                <em>Clic en un día para fijar una sesión en esa fecha</em>
            </div>
            <div class="ad-cal">
                @foreach (['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $dow)
                    <div class="ad-cal-dow">{{ $dow }}</div>
                @endforeach

                @foreach ($celdas as $celda)
                    @php
                        /** @var \Illuminate\Support\Carbon $fecha */
                        $fecha = $celda['fecha'];
                        $evs = $celda['eventos'];
                        $sess = $celda['sesiones'];
                    @endphp
                    <button
                        type="button"
                        class="ad-cal-cell {{ $celda['enMes'] ? '' : 'is-muted' }} {{ $celda['hoy'] ? 'is-today' : '' }} {{ ! empty($celda['hasItems']) ? 'has-events' : '' }}"
                        data-open-sesion-day
                        data-fecha="{{ $fecha->toDateString() }}"
                        title="Añadir sesión el {{ $fecha->format('d/m/Y') }}"
                    >
                        <span class="ad-cal-day">{{ $fecha->day }}</span>
                        @foreach ($evs->take(2) as $evento)
                            @php
                                $evHora = $evento->fecha_inicio
                                    ? \App\Models\HorarioPresencial::formatoHora12($evento->fecha_inicio->format('H:i'))
                                    : '';
                            @endphp
                            <span class="ad-cal-pill is-evento" title="Evento: {{ $evento->nombre }}">
                                <i class="ad-cal-pill-bar" aria-hidden="true"></i>
                                <span class="ad-cal-pill-body">
                                    @if ($evHora !== '')
                                        <b>{{ $evHora }}</b>
                                    @endif
                                    <em>{{ $evento->nombre }}</em>
                                </span>
                            </span>
                        @endforeach
                        @foreach ($sess->take(2) as $sesion)
                            <span class="ad-cal-pill is-sesion" title="Sesión: {{ $sesion->titulo }} · {{ $sesion->rangoHora12() }}">
                                <i class="ad-cal-pill-bar" aria-hidden="true"></i>
                                <span class="ad-cal-pill-body">
                                    <b>{{ $sesion->horaInicioCorta() }}</b>
                                    <em>{{ $sesion->titulo }}</em>
                                </span>
                            </span>
                        @endforeach
                        @if ($evs->count() + $sess->count() > 4)
                            <span class="ad-cal-more">+{{ $evs->count() + $sess->count() - 4 }}</span>
                        @endif
                    </button>
                @endforeach
            </div>

            @if ($eventos->isEmpty() && $sesiones->isEmpty())
                <div class="ad-empty-hero">
                    <span class="ad-empty-orb">@include('partials.admin-icon', ['icon' => 'calendario'])</span>
                    <strong>Sin actividad este mes</strong>
                    <button class="ad-btn" type="button" data-open-sesion-form>Añadir sesión</button>
                </div>
            @endif
        </section>

        <aside class="ad-card ad-cal-reminders" data-cal-reminders>
            <button type="button" class="ad-cal-rem-toggle" data-cal-rem-toggle aria-expanded="false">
                <span class="ad-cal-rem-toggle-main">
                    <strong>Recordatorios</strong>
                    <small>Próximos 21 días</small>
                </span>
                @if (($recordatoriosCount ?? 0) > 0)
                    <span class="ad-cal-rem-count" aria-label="{{ $recordatoriosCount }} próximos">
                        <svg viewBox="0 0 36 36" width="34" height="34" aria-hidden="true">
                            <circle cx="18" cy="18" r="15.5" fill="none" stroke="rgba(46,245,230,0.22)" stroke-width="2"/>
                            <circle cx="18" cy="18" r="15.5" fill="none" stroke="url(#adRemGrad)" stroke-width="2.2" stroke-linecap="round"
                                stroke-dasharray="97" stroke-dashoffset="{{ max(8, 97 - min(97, (int) $recordatoriosCount * 12)) }}"
                                transform="rotate(-90 18 18)"/>
                            <defs>
                                <linearGradient id="adRemGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#2ef5e6"/>
                                    <stop offset="100%" stop-color="#ff2d78"/>
                                </linearGradient>
                            </defs>
                        </svg>
                        <strong>{{ $recordatoriosCount }}</strong>
                    </span>
                @endif
                <span class="ad-cal-rem-chevron" aria-hidden="true">▾</span>
            </button>
            <div class="ad-cal-rem-panel" data-cal-rem-panel hidden>
                @forelse ($recordatorios as $item)
                    <article class="ad-cal-rem {{ $item['urgencia'] === 'alta' ? 'is-alta' : ($item['urgencia'] === 'media' ? 'is-media' : '') }}">
                        <span class="ad-cal-rem-tag">{{ $item['tipo'] === 'evento' ? 'Evento' : 'Sesión' }}</span>
                        <strong>{{ $item['titulo'] }}</strong>
                        <em>{{ $item['cuando'] }}</em>
                        <p>{{ $item['detalle'] }}</p>
                        @if (! empty($item['href']))
                            <a href="{{ $item['href'] }}">Ver en eventos</a>
                        @endif
                    </article>
                @empty
                    <div class="ad-empty-hero" style="padding:20px">
                        <strong>Sin recordatorios próximos</strong>
                        <p class="ad-page-intro-note">Cuando haya eventos o sesiones en los próximos días, aparecerán aquí. Al cumplirse la fecha salen solos.</p>
                    </div>
                @endforelse
            </div>
        </aside>
    </section>

    <section class="ad-card ad-users ad-cal-archive" style="margin-top:16px">
        <div class="ad-card-head">
            <h2>Archivo de sesiones</h2>
            <small>Organizadas por mes</small>
        </div>

        <div class="ad-cal-archive-scroll">
        @forelse ($sesionesPorMes as $folder)
            <details class="ad-cal-folder {{ ! empty($folder['is_current']) ? 'is-current' : '' }}" @if (! empty($folder['is_current'])) open @endif>
                <summary class="ad-cal-folder-head">
                    <span class="ad-cal-folder-ico" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 8.5A2.5 2.5 0 0 1 5.5 6H9l2 2h7.5A2.5 2.5 0 0 1 21 10.5v7A2.5 2.5 0 0 1 18.5 20h-13A2.5 2.5 0 0 1 3 17.5v-9Z"/>
                        </svg>
                    </span>
                    <span class="ad-cal-folder-title">
                        <strong>{{ $folder['label'] }}</strong>
                        @if (! empty($folder['is_current']))
                            <em>Mes actual</em>
                        @endif
                    </span>
                    <span class="ad-cal-folder-meta">
                        <span class="ad-cal-folder-count">{{ $folder['count'] }}</span>
                        <span class="ad-cal-folder-chevron" aria-hidden="true">▾</span>
                    </span>
                </summary>
                <div class="ad-cal-folder-body">
                    @forelse ($folder['sesiones'] as $sesion)
                        @php
                            $fechaVal = optional($sesion->fecha)->format('Y-m-d') ?: (string) $sesion->fecha;
                            $hi = substr((string) $sesion->hora_inicio, 0, 5);
                            $hf = substr((string) $sesion->hora_fin, 0, 5);
                            $coachNombre = $sesion->coach?->usuario?->name
                                ?? $sesion->coach?->usuario?->nombres
                                ?? ('Coach #'.$sesion->id_coach);
                        @endphp
                        <article class="ad-cal-session-row">
                            <div class="ad-cal-session-main">
                                <strong>{{ $sesion->titulo }}</strong>
                                <em>{{ optional($sesion->fecha)->format('d/m/Y') }} · {{ $sesion->rangoHora12() }}</em>
                                <span>{{ $sesion->tipo }} · {{ $coachNombre }} · {{ $sesion->modalidad }}</span>
                            </div>
                            <span class="ad-badge ad-badge-ok">{{ $sesion->estado }}</span>
                            <div class="ad-row-actions">
                                <button class="ad-icon-btn" type="button"
                                    title="Editar"
                                    aria-label="Editar sesión"
                                    data-open-sesion-edit
                                    data-id="{{ $sesion->id_sesion }}"
                                    data-titulo="{{ $sesion->titulo }}"
                                    data-id_coach="{{ $sesion->id_coach }}"
                                    data-tipo="{{ $sesion->tipo }}"
                                    data-fecha="{{ $fechaVal }}"
                                    data-hora_inicio="{{ $hi }}"
                                    data-hora_fin="{{ $hf }}"
                                    data-modalidad="{{ $sesion->modalidad }}"
                                    data-cupo_maximo="{{ $sesion->cupo_maximo }}"
                                    data-estado="{{ $sesion->estado }}">
                                    @include('partials.admin-icon', ['icon' => 'edit'])
                                </button>
                                <form method="POST" action="{{ route('admin.calendario.sesiones.destroy', $sesion->id_sesion) }}" class="ad-inline-form" data-sesion-delete-form>
                                    @csrf
                                    @method('DELETE')
                                    <button class="ad-icon-btn is-danger" type="button" title="Eliminar" aria-label="Eliminar sesión" data-sesion-delete>
                                        @include('partials.admin-icon', ['icon' => 'trash'])
                                    </button>
                                </form>
                            </div>
                        </article>
                    @empty
                        <p class="ad-page-intro-note" style="margin:0;padding:8px 4px">Sin sesiones en este mes.</p>
                    @endforelse
                </div>
            </details>
        @empty
            <div class="ad-empty-hero" style="padding:24px">
                <span class="ad-empty-orb">@include('partials.admin-icon', ['icon' => 'horarios'])</span>
                <strong>Sin sesiones</strong>
                <button class="ad-btn" type="button" data-open-sesion-form>Añadir sesión</button>
            </div>
        @endforelse
        </div>
    </section>

    @php
        $tipos = ['INDIVIDUAL', 'GRUPAL'];
        $modalidades = ['PRESENCIAL', 'VIRTUAL'];
        $estados = ['PROGRAMADA', 'EN_CURSO', 'COMPLETADA', 'CANCELADA'];
    @endphp

    <div class="ad-modal" data-sesion-modal hidden>
        <div class="ad-modal-card ad-ml-modal">
            <header class="ad-modal-head">
                <div>
                    <h2>Nueva sesión</h2>
                    <p>Completa los datos para fijarla en el calendario.</p>
                </div>
                <button type="button" class="ad-modal-close" data-close-sesion-form aria-label="Cerrar">×</button>
            </header>
            <form class="ad-form" method="POST" action="{{ route('admin.calendario.sesiones.store') }}" data-sesion-create-form>
                @csrf
                @include('admin.calendario._fields', compact('coaches', 'tipos', 'modalidades', 'estados'))
                <div class="ad-modal-actions">
                    <button class="ad-btn ad-btn-ghost" type="button" data-close-sesion-form>Cancelar</button>
                    <button class="ad-btn" type="submit">Crear sesión</button>
                </div>
            </form>
        </div>
    </div>

    <div class="ad-modal" data-sesion-edit-modal hidden>
        <div class="ad-modal-card ad-ml-modal">
            <header class="ad-modal-head">
                <div><h2>Editar sesión</h2></div>
                <button type="button" class="ad-modal-close" data-close-sesion-edit aria-label="Cerrar">×</button>
            </header>
            <form class="ad-form" method="POST" data-sesion-edit-form>
                @csrf
                @method('PUT')
                @include('admin.calendario._fields', ['coaches' => $coaches, 'tipos' => $tipos, 'modalidades' => $modalidades, 'estados' => $estados, 'withDataFields' => true])
                <div class="ad-modal-actions">
                    <button class="ad-btn ad-btn-ghost" type="button" data-close-sesion-edit>Cancelar</button>
                    <button class="ad-btn" type="submit">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="ad-modal" data-sesion-confirm-modal hidden>
        <div class="ad-modal-card" role="dialog" aria-modal="true" aria-labelledby="ad-sesion-del-title">
            <header class="ad-modal-head">
                <div>
                    <h2 id="ad-sesion-del-title">Eliminar sesión</h2>
                    <p>Esta acción no se puede deshacer.</p>
                </div>
                <button type="button" class="ad-modal-close" data-sesion-confirm-cancel aria-label="Cerrar">×</button>
            </header>
            <div class="ad-modal-actions">
                <button type="button" class="ad-btn ad-btn-ghost" data-sesion-confirm-cancel>Cancelar</button>
                <button type="button" class="ad-btn is-danger" data-sesion-confirm-ok>Sí, eliminar</button>
            </div>
        </div>
    </div>

    @php $ok = session('sesion_ok'); @endphp
    @if (is_array($ok) && ! empty($ok['accion']))
        @php
            $accion = $ok['accion'];
            $accionLabel = match ($accion) {
                'creada' => 'Sesión creada',
                'actualizada' => 'Sesión actualizada',
                'eliminada' => 'Sesión eliminada',
                default => 'Acción completada',
            };
            $detalle = match ($accion) {
                'creada' => 'Ya queda reflejada en el calendario y en el listado.',
                'actualizada' => 'Los cambios se guardaron correctamente.',
                'eliminada' => 'La sesión se eliminó del calendario.',
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

    @if (isset($errors) && $errors->any())
        <div class="ad-modal ad-warn-modal is-open" data-warn-modal role="dialog" aria-modal="true" aria-labelledby="ad-warn-title">
            <div class="ad-modal-card ad-warn-card">
                <div class="ad-warn-x" aria-hidden="true">
                    <svg viewBox="0 0 52 52" width="56" height="56">
                        <circle cx="26" cy="26" r="24" fill="none"/>
                        <path fill="none" d="M18 18l16 16M34 18 18 34"/>
                    </svg>
                </div>
                <h2 id="ad-warn-title">Revisa los datos</h2>
                <p class="ad-warn-lead">Hay algo que corregir antes de continuar.</p>
                <ul class="ad-warn-list">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="ad-btn is-danger" data-close-warn>Corregir</button>
            </div>
        </div>
    @endif

    <script>
        (function () {
            var createModal = document.querySelector('[data-sesion-modal]');
            var createForm = document.querySelector('[data-sesion-create-form]');
            var editModal = document.querySelector('[data-sesion-edit-modal]');
            var editForm = document.querySelector('[data-sesion-edit-form]');
            var confirmModal = document.querySelector('[data-sesion-confirm-modal]');
            var pendingDeleteForm = null;

            function setFecha(form, fecha) {
                var el = form && form.querySelector('[name="fecha"]');
                if (el && fecha) el.value = fecha;
            }

            function openCreate(fecha) {
                if (!createModal || !createForm) return;
                createForm.reset();
                if (fecha) setFecha(createForm, fecha);
                createModal.hidden = false;
                createModal.classList.add('is-open');
                document.body.style.overflow = 'hidden';
            }
            function closeCreate() {
                if (!createModal) return;
                createModal.hidden = true;
                createModal.classList.remove('is-open');
                document.body.style.overflow = '';
            }

            document.querySelectorAll('[data-open-sesion-form]').forEach(function (b) {
                b.addEventListener('click', function () { openCreate(''); });
            });
            document.querySelectorAll('[data-open-sesion-day]').forEach(function (cell) {
                cell.addEventListener('click', function () {
                    openCreate(cell.getAttribute('data-fecha') || '');
                });
            });
            document.querySelectorAll('[data-close-sesion-form]').forEach(function (b) {
                b.addEventListener('click', closeCreate);
            });
            createModal?.addEventListener('click', function (e) {
                if (e.target === createModal) closeCreate();
            });

            function openEdit(btn) {
                if (!editModal || !editForm) return;
                editForm.action = '{{ url('admin/calendario/sesiones') }}/' + btn.getAttribute('data-id');
                ['titulo','id_coach','tipo','fecha','hora_inicio','hora_fin','modalidad','cupo_maximo','estado'].forEach(function (f) {
                    var el = editForm.querySelector('[data-field="'+f+'"]');
                    if (el) el.value = btn.getAttribute('data-'+f) || '';
                });
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
            document.querySelectorAll('[data-open-sesion-edit]').forEach(function (b) {
                b.addEventListener('click', function () { openEdit(b); });
            });
            document.querySelectorAll('[data-close-sesion-edit]').forEach(function (b) {
                b.addEventListener('click', closeEdit);
            });
            editModal?.addEventListener('click', function (e) {
                if (e.target === editModal) closeEdit();
            });

            function openConfirm(form) {
                pendingDeleteForm = form;
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
            document.querySelectorAll('[data-sesion-delete]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var form = btn.closest('[data-sesion-delete-form]');
                    if (form) openConfirm(form);
                });
            });
            document.querySelectorAll('[data-sesion-confirm-cancel]').forEach(function (b) {
                b.addEventListener('click', closeConfirm);
            });
            confirmModal?.querySelector('[data-sesion-confirm-ok]')?.addEventListener('click', function () {
                if (pendingDeleteForm) pendingDeleteForm.submit();
            });
            confirmModal?.addEventListener('click', function (e) {
                if (e.target === confirmModal) closeConfirm();
            });

            function wireFeedbackModal(sel, closeSel) {
                var box = document.querySelector(sel);
                if (!box) return;
                document.body.style.overflow = 'hidden';
                function closeBox() {
                    box.hidden = true;
                    box.classList.remove('is-open');
                    document.body.style.overflow = '';
                }
                box.querySelectorAll(closeSel).forEach(function (b) {
                    b.addEventListener('click', closeBox);
                });
                box.addEventListener('click', function (e) {
                    if (e.target === box) closeBox();
                });
                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') closeBox();
                });
            }
            wireFeedbackModal('[data-ok-modal]', '[data-close-ok]');
            wireFeedbackModal('[data-warn-modal]', '[data-close-warn]');

            var remBox = document.querySelector('[data-cal-reminders]');
            var remToggle = document.querySelector('[data-cal-rem-toggle]');
            var remPanel = document.querySelector('[data-cal-rem-panel]');
            remToggle?.addEventListener('click', function () {
                if (!remBox || !remPanel) return;
                var open = remBox.classList.toggle('is-open');
                remToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                remPanel.hidden = !open;
            });
        })();
    </script>
@endsection
