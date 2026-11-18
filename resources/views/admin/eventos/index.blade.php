@extends('layouts.admin')

@section('title', 'Eventos')
@section('subtitle')
    <span class="ad-page-intro">
        <span class="ad-page-intro-lead">Actividades especiales del club FigueFit.</span>
        <span class="ad-page-intro-note">Crea, publica y da seguimiento a talleres, retos y clases especiales.</span>
    </span>
@endsection

@section('flash')
@endsection

@section('actions')
    <button class="ad-btn ad-btn-add" type="button" data-open-evento-form>+ Crear evento</button>
@endsection

@section('content')
    @php
        $stats = $stats ?? ['en_curso' => 0, 'proximos' => 0, 'cerrados' => 0];
        $semana = $semana ?? collect();
        $diasConEvento = $diasConEvento ?? [];
        $mes = $mes ?? now()->format('Y-m');
        $mesAnterior = $mesAnterior ?? now()->subMonth()->format('Y-m');
        $mesSiguiente = $mesSiguiente ?? now()->addMonth()->format('Y-m');
        $mesLabel = $mesLabel ?? now()->locale('es')->translatedFormat('F Y');
        $mesCarbon = \Illuminate\Support\Carbon::createFromFormat('Y-m', $mes)->startOfMonth();
        $tiposLegend = [
            'is-pink' => 'Taller',
            'is-cyan' => 'Reto',
            'is-lilac' => 'Clase especial',
            'is-gold' => 'Comunidad',
        ];
        $toneForTipo = function (?string $tipo): string {
            $tipo = mb_strtolower(trim((string) $tipo));
            return match (true) {
                str_contains($tipo, 'taller') => 'is-pink',
                str_contains($tipo, 'reto') => 'is-cyan',
                str_contains($tipo, 'especial') || str_contains($tipo, 'webinar') => 'is-lilac',
                str_contains($tipo, 'comunidad') => 'is-gold',
                default => 'is-pink',
            };
        };
        $nowRef = now();
    @endphp
    <div class="ad-evt" data-evt-board>
        <div>
            <header class="ad-evt-month-head">
                <a class="ad-evt-month-nav" href="{{ route('admin.eventos.index', ['mes' => $mesAnterior]) }}" aria-label="Mes anterior">‹</a>
                <div class="ad-evt-month-label">
                    <em>Agenda</em>
                    <strong>{{ $mesLabel }}</strong>
                </div>
                <a class="ad-evt-month-nav" href="{{ route('admin.eventos.index', ['mes' => $mesSiguiente]) }}" aria-label="Mes siguiente">›</a>
            </header>
            <div class="ad-date-strip">
                @foreach ($semana as $dia)
                    <div class="ad-date-chip {{ $dia->isToday() ? 'is-on' : '' }}">
                        <strong>{{ $dia->format('d') }}</strong>
                        <span>{{ $dia->locale('es')->isoFormat('ddd') }}</span>
                    </div>
                @endforeach
            </div>
            <div class="ad-evt-grid ad-stagger" data-evt-grid>
                @forelse ($eventos as $evento)
                    @php
                        $tone = $toneForTipo($evento->tipo);
                        $inicio = $evento->fecha_inicio;
                        $fin = $evento->fecha_fin ?: $inicio;
                        $cardMes = $inicio ? strtoupper($inicio->locale('es')->shortMonthName) : '—';
                        $diaNum = $inicio ? $inicio->format('d') : '—';
                        $rango = $inicio
                            ? ($inicio->format('d M Y').($fin && ! $fin->isSameDay($inicio) ? ' – '.$fin->format('d M Y') : ''))
                            : 'Sin fecha';
                        $cupo = ((int) $evento->cupo_inscritos).'/'.($evento->cupo_maximo ?: '∞');
                        if ($inicio && $inicio->gt($nowRef)) {
                            $grupo = 'proximos';
                        } elseif ($evento->fecha_fin && $evento->fecha_fin->lt($nowRef)) {
                            $grupo = 'cerrados';
                        } else {
                            $grupo = 'en_curso';
                        }
                    @endphp
                    <article class="ad-evt-card {{ $tone }}" data-evt-card data-grupo="{{ $grupo }}">
                        <aside class="ad-evt-card-date" aria-hidden="true">
                            <em>{{ $cardMes }}</em>
                            <strong>{{ $diaNum }}</strong>
                            <span>{{ $rango }}</span>
                        </aside>
                        <div class="ad-evt-card-body">
                            <header class="ad-evt-card-head">
                                <h3>{{ $evento->nombre }}</h3>
                                <div class="ad-evt-card-actions">
                                    <button class="ad-icon-btn" type="button"
                                        title="Editar"
                                        aria-label="Editar evento"
                                        data-open-evento-edit
                                        data-id="{{ $evento->id_evento }}"
                                        data-nombre="{{ $evento->nombre }}"
                                        data-descripcion="{{ $evento->descripcion }}"
                                        data-tipo="{{ $evento->tipo }}"
                                        data-inicio="{{ optional($evento->fecha_inicio)->format('Y-m-d\\TH:i') }}"
                                        data-fin="{{ optional($evento->fecha_fin)->format('Y-m-d\\TH:i') }}"
                                        data-modalidad="{{ $evento->modalidad }}"
                                        data-cupo="{{ $evento->cupo_maximo }}"
                                        data-estado="{{ $evento->estado }}"
                                        data-imagen_url="{{ $evento->imagen_url }}"
                                        data-imagen_src="{{ $evento->imagenSrc() }}">
                                        @include('partials.admin-icon', ['icon' => 'edit'])
                                    </button>
                                    <form method="POST" action="{{ route('admin.eventos.destroy', $evento->id_evento) }}" class="ad-inline-form" data-evento-delete-form>
                                        @csrf
                                        @method('DELETE')
                                        <button class="ad-icon-btn is-danger" type="button" title="Eliminar" aria-label="Eliminar evento" data-evento-delete>
                                            @include('partials.admin-icon', ['icon' => 'trash'])
                                        </button>
                                    </form>
                                </div>
                            </header>
                            <span class="ad-evt-card-badge">{{ $evento->tipo ?: 'Evento' }}</span>
                            <p class="ad-evt-card-meta">
                                <span>{{ $evento->modalidad ?: '—' }}</span>
                                <span>Cupo {{ $cupo }}</span>
                            </p>
                        </div>
                    </article>
                @empty
                    <div class="ad-empty-hero ad-evt-empty">
                        <span class="ad-empty-orb">@include('partials.admin-icon', ['icon' => 'eventos'])</span>
                        <strong>Sin eventos</strong>
                        <button class="ad-btn" type="button" data-open-evento-form>Crear evento</button>
                    </div>
                @endforelse
            </div>
            <div class="ad-pagination">{{ $eventos->appends(['mes' => $mes])->links() }}</div>
        </div>
        <aside class="ad-evt-side">
            <div class="ad-stat-pills" data-evt-filters>
                <button type="button" class="is-mint" data-evt-filter="en_curso" aria-pressed="false">
                    <strong data-count="{{ $stats['en_curso'] }}">{{ $stats['en_curso'] }}</strong>
                    <span>En curso</span>
                </button>
                <button type="button" class="is-pink" data-evt-filter="proximos" aria-pressed="false">
                    <strong data-count="{{ $stats['proximos'] }}">{{ $stats['proximos'] }}</strong>
                    <span>Próximos</span>
                </button>
                <button type="button" class="is-lilac" data-evt-filter="cerrados" aria-pressed="false">
                    <strong data-count="{{ $stats['cerrados'] }}">{{ $stats['cerrados'] }}</strong>
                    <span>Cerrados</span>
                </button>
            </div>
            <section class="ad-glass-card ad-evt-cal">
                <h3>{{ $mesLabel }}</h3>
                <div class="ad-tf-mini">
                    @foreach (['L','M','X','J','V','S','D'] as $d)
                        <em>{{ $d }}</em>
                    @endforeach
                    @php
                        $cursor = $mesCarbon->copy()->startOfWeek();
                        $finMesCal = $mesCarbon->copy()->endOfMonth()->endOfWeek();
                    @endphp
                    @while ($cursor->lte($finMesCal))
                        @php
                            $key = $cursor->format('Y-m-d');
                            $has = in_array($key, $diasConEvento, true);
                            $enMesSel = $cursor->month === $mesCarbon->month;
                        @endphp
                        <button
                            type="button"
                            class="{{ $cursor->isToday() ? 'is-today' : '' }} {{ $enMesSel ? '' : 'is-muted' }} {{ $has ? 'has-events' : '' }}"
                            title="{{ $has ? 'Hay evento(s)' : '' }}"
                        >{{ $cursor->day }}</button>
                        @php $cursor->addDay(); @endphp
                    @endwhile
                </div>
            </section>
            <section class="ad-glass-card ad-evt-legend">
                <h3>Leyenda</h3>
                @foreach ($tiposLegend as $cls => $label)
                    <div class="ad-evt-legend-row">
                        <span class="ad-evt-legend-pill {{ $cls }}">{{ $label }}</span>
                    </div>
                @endforeach
            </section>
        </aside>
    </div>

    <div class="ad-modal" data-evento-modal hidden>
        <div class="ad-modal-card ad-ml-modal">
            <header class="ad-modal-head">
                <div><h2>Nuevo evento</h2></div>
                <button type="button" class="ad-modal-close" data-close-evento-form aria-label="Cerrar">×</button>
            </header>
            <form class="ad-form" method="POST" action="{{ route('admin.eventos.store') }}" enctype="multipart/form-data">
                @csrf
                @include('admin.eventos._fields', ['evento' => null])
                <div class="ad-modal-actions">
                    <button class="ad-btn ad-btn-ghost" type="button" data-close-evento-form>Cancelar</button>
                    <button class="ad-btn" type="submit">Crear</button>
                </div>
            </form>
        </div>
    </div>

    <div class="ad-modal" data-evento-edit-modal hidden>
        <div class="ad-modal-card ad-ml-modal">
            <header class="ad-modal-head">
                <div><h2>Editar evento</h2></div>
                <button type="button" class="ad-modal-close" data-close-evento-edit aria-label="Cerrar">×</button>
            </header>
            <form class="ad-form" method="POST" data-evento-edit-form enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('admin.eventos._fields', ['evento' => null, 'withDataFields' => true])
                <div class="ad-modal-actions">
                    <button class="ad-btn ad-btn-ghost" type="button" data-close-evento-edit>Cancelar</button>
                    <button class="ad-btn" type="submit">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="ad-modal" data-evento-confirm-modal hidden>
        <div class="ad-modal-card" role="dialog" aria-modal="true" aria-labelledby="ad-evento-del-title">
            <header class="ad-modal-head">
                <div>
                    <h2 id="ad-evento-del-title">Eliminar evento</h2>
                    <p>Esta acción no se puede deshacer.</p>
                </div>
                <button type="button" class="ad-modal-close" data-evento-confirm-cancel aria-label="Cerrar">×</button>
            </header>
            <div class="ad-modal-actions">
                <button type="button" class="ad-btn ad-btn-ghost" data-evento-confirm-cancel>Cancelar</button>
                <button type="button" class="ad-btn is-danger" data-evento-confirm-ok>Sí, eliminar</button>
            </div>
        </div>
    </div>

    @php $ok = session('evento_ok'); @endphp
    @if (is_array($ok) && ! empty($ok['accion']))
        @php
            $accion = $ok['accion'];
            $accionLabel = match ($accion) {
                'creado' => 'Evento creado',
                'actualizado' => 'Evento actualizado',
                'eliminado' => 'Evento eliminado',
                default => 'Acción completada',
            };
            $detalle = match ($accion) {
                'creado' => 'El evento ya está disponible en el listado.',
                'actualizado' => 'Los cambios se guardaron correctamente.',
                'eliminado' => 'El evento se eliminó del panel.',
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
            function wirePreview(root) {
                if (!root) return;
                var cover = root.querySelector('[data-evento-cover]');
                var file = root.querySelector('[data-field-preview-file]');
                var url = root.querySelector('[data-field-preview-url]');
                var img = root.querySelector('[data-field-preview-img]');
                var empty = root.querySelector('[data-field-preview-empty]');
                var preview = root.querySelector('[data-evento-preview]');
                function show(src) {
                    if (!img) return;
                    if (src) { img.src = src; img.hidden = false; if (empty) empty.hidden = true; }
                    else { img.hidden = true; if (empty) empty.hidden = false; }
                }
                function applyFile(f) {
                    if (!f || !f.type || f.type.indexOf('image/') !== 0) return;
                    if (file) {
                        try {
                            var dt = new DataTransfer();
                            dt.items.add(f);
                            file.files = dt.files;
                        } catch (err) { /* navegadores sin DataTransfer */ }
                    }
                    var reader = new FileReader();
                    reader.onload = function (e) { show(e.target.result); };
                    reader.readAsDataURL(f);
                }
                file?.addEventListener('change', function () {
                    var f = file.files && file.files[0];
                    if (!f) return;
                    applyFile(f);
                });
                url?.addEventListener('input', function () {
                    if (file && file.files && file.files.length) return;
                    show(url.value.trim());
                });
                preview?.addEventListener('click', function () {
                    if (img && !img.hidden) return;
                    file?.click();
                });
                if (cover) {
                    ['dragenter', 'dragover'].forEach(function (ev) {
                        cover.addEventListener(ev, function (e) {
                            e.preventDefault();
                            e.stopPropagation();
                            cover.classList.add('is-dragover');
                        });
                    });
                    ['dragleave', 'drop'].forEach(function (ev) {
                        cover.addEventListener(ev, function (e) {
                            e.preventDefault();
                            e.stopPropagation();
                            if (ev === 'drop') {
                                var f = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
                                if (f) applyFile(f);
                            }
                            cover.classList.remove('is-dragover');
                        });
                    });
                }
                return show;
            }

            var createModal = document.querySelector('[data-evento-modal]');
            wirePreview(createModal);
            function syncCoverRequired(root) {
                if (!root) return;
                var file = root.querySelector('[data-field-preview-file]');
                var url = root.querySelector('[data-cover-url]');
                var img = root.querySelector('[data-field-preview-img]');
                if (!url) return;
                var hasFile = !!(file && file.files && file.files.length);
                var hasUrl = !!(url.value && url.value.trim());
                var hasPreview = !!(img && !img.hidden && img.getAttribute('src'));
                if (hasFile || hasUrl || hasPreview) {
                    url.removeAttribute('required');
                    url.setCustomValidity('');
                } else {
                    url.setAttribute('required', 'required');
                }
            }
            function wireCoverRequired(root) {
                if (!root) return;
                var form = root.querySelector('form');
                var file = root.querySelector('[data-field-preview-file]');
                var url = root.querySelector('[data-cover-url]');
                file?.addEventListener('change', function () { syncCoverRequired(root); });
                url?.addEventListener('input', function () { syncCoverRequired(root); });
                form?.addEventListener('submit', function () { syncCoverRequired(root); });
                syncCoverRequired(root);
            }
            wireCoverRequired(createModal);
            function open() { if (createModal) { createModal.hidden = false; createModal.classList.add('is-open'); syncCoverRequired(createModal); } }
            function close() { if (createModal) { createModal.hidden = true; createModal.classList.remove('is-open'); } }
            document.querySelectorAll('[data-open-evento-form]').forEach(function (b) { b.addEventListener('click', open); });
            document.querySelectorAll('[data-close-evento-form]').forEach(function (b) { b.addEventListener('click', close); });
            createModal?.addEventListener('click', function (e) { if (e.target === createModal) close(); });

            var editModal = document.querySelector('[data-evento-edit-modal]');
            var editForm = document.querySelector('[data-evento-edit-form]');
            var editShow = wirePreview(editModal);
            wireCoverRequired(editModal);
            function openEdit(btn) {
                if (!editModal || !editForm) return;
                editForm.action = '{{ url('admin/eventos') }}/' + btn.getAttribute('data-id');
                ['nombre','descripcion','tipo','inicio','fin','modalidad','cupo','estado','imagen_url'].forEach(function (f) {
                    var el = editForm.querySelector('[data-field="'+f+'"]');
                    if (el) el.value = btn.getAttribute('data-'+f) || '';
                });
                if (editShow) editShow(btn.getAttribute('data-imagen_src') || '');
                var file = editForm.querySelector('[data-field-preview-file]');
                if (file) file.value = '';
                editModal.hidden = false;
                editModal.classList.add('is-open');
                syncCoverRequired(editModal);
            }
            function closeEdit() { if (editModal) { editModal.hidden = true; editModal.classList.remove('is-open'); } }
            document.querySelectorAll('[data-open-evento-edit]').forEach(function (b) { b.addEventListener('click', function () { openEdit(b); }); });
            document.querySelectorAll('[data-close-evento-edit]').forEach(function (b) { b.addEventListener('click', closeEdit); });
            editModal?.addEventListener('click', function (e) { if (e.target === editModal) closeEdit(); });

            var confirmModal = document.querySelector('[data-evento-confirm-modal]');
            var pendingDeleteForm = null;
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
            document.querySelectorAll('[data-evento-delete]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var form = btn.closest('[data-evento-delete-form]');
                    if (form) openConfirm(form);
                });
            });
            document.querySelectorAll('[data-evento-confirm-cancel]').forEach(function (b) {
                b.addEventListener('click', closeConfirm);
            });
            confirmModal?.querySelector('[data-evento-confirm-ok]')?.addEventListener('click', function () {
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

            (function wireEvtFilters() {
                var board = document.querySelector('[data-evt-board]');
                if (!board) return;
                var filters = board.querySelectorAll('[data-evt-filter]');
                var cards = board.querySelectorAll('[data-evt-card]');
                var active = null;

                function apply(grupo) {
                    active = grupo;
                    board.classList.toggle('is-filtering', !!grupo);
                    filters.forEach(function (btn) {
                        var on = !!grupo && btn.getAttribute('data-evt-filter') === grupo;
                        btn.classList.toggle('is-on', on);
                        btn.setAttribute('aria-pressed', on ? 'true' : 'false');
                    });
                    cards.forEach(function (card) {
                        var match = !!grupo && card.getAttribute('data-grupo') === grupo;
                        card.classList.toggle('is-lit', match);
                    });
                }

                filters.forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        var grupo = btn.getAttribute('data-evt-filter');
                        apply(active === grupo ? null : grupo);
                    });
                });
            })();
        })();
    </script>
@endsection
