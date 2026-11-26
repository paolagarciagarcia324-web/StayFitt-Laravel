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
    <button class="ad-pend-btn is-fucsia ad-evt-create-btn" type="button" data-open-evento-form>+ Crear evento</button>
@endsection

@section('content')
    @php
        $stats = $stats ?? ['en_curso' => 0, 'proximos' => 0, 'cerrados' => 0];
        $semana = $semana ?? collect();
        $diasConEvento = $diasConEvento ?? [];
        $eventosPorDia = $eventosPorDia ?? [];
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
        $hoy = $nowRef->copy()->startOfDay();
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
                    @php $chipPast = $dia->copy()->startOfDay()->lt($hoy); @endphp
                    <div class="ad-date-chip {{ $dia->isToday() ? 'is-on' : '' }} {{ $chipPast ? 'is-past' : '' }}">
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
                        } elseif ($fin && $fin->lt($nowRef)) {
                            $grupo = 'cerrados';
                        } else {
                            $grupo = 'en_curso';
                        }
                        $cardPast = $grupo === 'cerrados';
                    @endphp
                    <article
                        id="evt-{{ $evento->id_evento }}"
                        class="ad-evt-card {{ $tone }} {{ $cardPast ? 'is-past' : '' }}"
                        data-evt-card
                        data-evt-id="{{ $evento->id_evento }}"
                        data-grupo="{{ $grupo }}"
                    >
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
                <div class="ad-tf-mini" data-evt-cal>
                    @foreach (['L','M','X','J','V','S','D'] as $d)
                        <em>{{ $d }}</em>
                    @endforeach
                    @php
                        $cursor = $mesCarbon->copy()->startOfWeek(\Illuminate\Support\Carbon::MONDAY);
                        $finMesCal = $mesCarbon->copy()->endOfMonth()->endOfWeek(\Illuminate\Support\Carbon::SUNDAY);
                    @endphp
                    @while ($cursor->lte($finMesCal))
                        @php
                            $key = $cursor->format('Y-m-d');
                            $enMesSel = $cursor->month === $mesCarbon->month;
                            $diaItems = $eventosPorDia[$key] ?? [];
                            $has = $enMesSel && count($diaItems) > 0;
                            $allPast = $has && collect($diaItems)->every(fn ($row) => ! empty($row['vencido']));
                            $dayPast = $enMesSel && $cursor->copy()->startOfDay()->lt($hoy);
                            $jumpId = $has ? ($diaItems[0]['id'] ?? null) : null;
                        @endphp
                        @if ($enMesSel)
                            <button
                                type="button"
                                class="{{ $cursor->isToday() ? 'is-today' : '' }} {{ $dayPast ? 'is-past' : '' }} {{ $has ? 'has-events' : '' }} {{ $allPast ? 'is-evt-past' : '' }}"
                                title="{{ $has ? ($allPast ? 'Evento vencido · ver detalle' : 'Ir al evento') : '' }}"
                                @if ($jumpId) data-evt-jump="{{ $jumpId }}" @endif
                            >{{ $cursor->day }}</button>
                        @else
                            <span class="ad-tf-mini-empty" aria-hidden="true"></span>
                        @endif
                        @php $cursor->addDay(); @endphp
                    @endwhile
                </div>
            </section>
            <section class="ad-glass-card ad-evt-legend">
                <h3>Leyenda</h3>
                <div class="ad-evt-legend-list">
                    @foreach ($tiposLegend as $cls => $label)
                        <span class="ad-evt-legend-chip {{ $cls }}">{{ $label }}</span>
                    @endforeach
                </div>
            </section>
        </aside>
    </div>

    <div class="ad-modal ad-evt-form-modal" data-evento-modal hidden>
        <div class="ad-modal-card ad-evt-form-card">
            <div class="ad-evt-form-glow" aria-hidden="true"></div>
            <header class="ad-modal-head ad-evt-form-head">
                <div>
                    <p class="ad-evt-form-kicker">Operación · Eventos</p>
                    <h2>Nuevo evento</h2>
                </div>
                <button type="button" class="ad-modal-close" data-close-evento-form aria-label="Cerrar">×</button>
            </header>
            <form class="ad-form ad-evt-form" method="POST" action="{{ route('admin.eventos.store') }}" enctype="multipart/form-data">
                @csrf
                @include('admin.eventos._fields', ['evento' => null])
                <div class="ad-modal-actions ad-evt-form-actions">
                    <button class="ad-pend-btn is-menta" type="button" data-close-evento-form>Cancelar</button>
                    <button class="ad-pend-btn is-fucsia" type="submit">Crear</button>
                </div>
            </form>
        </div>
    </div>

    <div class="ad-modal ad-evt-form-modal" data-evento-edit-modal hidden>
        <div class="ad-modal-card ad-evt-form-card">
            <div class="ad-evt-form-glow" aria-hidden="true"></div>
            <header class="ad-modal-head ad-evt-form-head">
                <div>
                    <p class="ad-evt-form-kicker">Operación · Eventos</p>
                    <h2>Editar evento</h2>
                </div>
                <button type="button" class="ad-modal-close" data-close-evento-edit aria-label="Cerrar">×</button>
            </header>
            <form class="ad-form ad-evt-form" method="POST" data-evento-edit-form enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('admin.eventos._fields', ['evento' => null, 'withDataFields' => true])
                <div class="ad-modal-actions ad-evt-form-actions">
                    <button class="ad-pend-btn is-menta" type="button" data-close-evento-edit>Cancelar</button>
                    <button class="ad-pend-btn is-fucsia" type="submit">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="ad-modal ad-horario-del-modal" data-evento-confirm-modal hidden>
        <div class="ad-modal-card ad-horario-del-card" role="dialog" aria-modal="true" aria-labelledby="ad-evento-del-title">
            <div class="ad-horario-del-glow" aria-hidden="true"></div>
            <header class="ad-modal-head ad-horario-del-head">
                <div class="ad-horario-del-who">
                    <span class="ad-horario-del-orb" aria-hidden="true">@include('partials.admin-icon', ['icon' => 'trash'])</span>
                    <div>
                        <p class="ad-horario-del-kicker">Acción permanente</p>
                        <h2 id="ad-evento-del-title">Eliminar evento</h2>
                        <p>Esta acción no se puede deshacer.</p>
                    </div>
                </div>
                <button type="button" class="ad-modal-close" data-evento-confirm-cancel aria-label="Cerrar">×</button>
            </header>
            <div class="ad-modal-actions ad-horario-del-actions">
                <button type="button" class="ad-btn ad-btn-ghost" data-evento-confirm-cancel>Cancelar</button>
                <button type="button" class="ad-pend-btn is-menta" data-evento-confirm-ok>Sí, eliminar</button>
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

            function closeAllSfSelect(except) {
                document.querySelectorAll('[data-sf-select]').forEach(function (root) {
                    if (except && root === except) return;
                    var p = root.querySelector('[data-sf-select-panel]');
                    if (p) p.hidden = true;
                    root.classList.remove('is-open');
                });
            }
            function closeAllSfDt(except) {
                document.querySelectorAll('[data-sf-dt]').forEach(function (root) {
                    if (except && root === except) return;
                    var p = root.querySelector('[data-sf-dt-panel]');
                    if (p) p.hidden = true;
                    root.classList.remove('is-open');
                });
            }
            function wireSfSelect(root) {
                var hidden = root.querySelector('input[type="hidden"]');
                var display = root.querySelector('[data-sf-select-display]');
                var trigger = root.querySelector('[data-sf-select-trigger]');
                var panel = root.querySelector('[data-sf-select-panel]');
                function setVal(v) {
                    hidden.value = v || '';
                    display.textContent = v || '—';
                    root.querySelectorAll('[data-sf-select-opt]').forEach(function (opt) {
                        opt.classList.toggle('is-on', opt.getAttribute('data-value') === v);
                    });
                }
                root._setSelect = setVal;
                trigger.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var willOpen = panel.hidden;
                    closeAllSfSelect();
                    closeAllSfDt();
                    if (willOpen) {
                        panel.hidden = false;
                        root.classList.add('is-open');
                    }
                });
                root.querySelectorAll('[data-sf-select-opt]').forEach(function (opt) {
                    opt.addEventListener('click', function () {
                        setVal(opt.getAttribute('data-value'));
                        panel.hidden = true;
                        root.classList.remove('is-open');
                    });
                });
                setVal(hidden.value);
            }
            function pad2(n) { return String(n).padStart(2, '0'); }
            function parseDt(raw) {
                var s = String(raw || '').replace(' ', 'T');
                var m = s.match(/^(\d{4})-(\d{2})-(\d{2})T?(\d{2})?:?(\d{2})?/);
                var now = new Date();
                if (!m) {
                    return { y: now.getFullYear(), mo: now.getMonth(), d: now.getDate(), h12: 12, mi: 0, ap: 'am' };
                }
                var h24 = m[4] != null ? Number(m[4]) : 12;
                var mi = m[5] != null ? Number(m[5]) : 0;
                mi = Math.round(mi / 5) * 5;
                if (mi >= 60) mi = 55;
                var ap = h24 >= 12 ? 'pm' : 'am';
                var h12 = h24 % 12;
                if (h12 === 0) h12 = 12;
                return { y: Number(m[1]), mo: Number(m[2]) - 1, d: Number(m[3]), h12: h12, mi: mi, ap: ap };
            }
            function partsToLocal(p) {
                var h = p.h12 % 12;
                if (p.ap === 'pm') h += 12;
                return p.y + '-' + pad2(p.mo + 1) + '-' + pad2(p.d) + 'T' + pad2(h) + ':' + pad2(p.mi);
            }
            function formatDtDisplay(p) {
                var h = p.h12 % 12;
                if (p.ap === 'pm') h += 12;
                var hh12 = p.h12;
                return pad2(p.d) + '/' + pad2(p.mo + 1) + '/' + p.y + ' ' + pad2(hh12) + ':' + pad2(p.mi) + ' ' + (p.ap === 'pm' ? 'p. m.' : 'a. m.');
            }
            var monthNames = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
            function wireSfDt(root) {
                var hidden = root.querySelector('input[type="hidden"]');
                var display = root.querySelector('[data-sf-dt-display]');
                var trigger = root.querySelector('[data-sf-dt-trigger]');
                var panel = root.querySelector('[data-sf-dt-panel]');
                var grid = root.querySelector('[data-sf-dt-grid]');
                var monthLabel = root.querySelector('[data-sf-dt-month]');
                var colH = root.querySelector('[data-sf-dt-col="h"]');
                var colM = root.querySelector('[data-sf-dt-col="m"]');
                var colAp = root.querySelector('[data-sf-dt-col="ap"]');
                var state = parseDt(hidden.value || '');
                var viewY = state.y;
                var viewMo = state.mo;
                var timeBuilt = false;

                function sync() {
                    hidden.value = partsToLocal(state);
                    display.textContent = formatDtDisplay(state);
                }
                function markTime() {
                    colH.querySelectorAll('[data-val]').forEach(function (b) {
                        b.classList.toggle('is-on', Number(b.getAttribute('data-val')) === state.h12);
                    });
                    colM.querySelectorAll('[data-val]').forEach(function (b) {
                        b.classList.toggle('is-on', Number(b.getAttribute('data-val')) === state.mi);
                    });
                    colAp.querySelectorAll('[data-val]').forEach(function (b) {
                        b.classList.toggle('is-on', b.getAttribute('data-val') === state.ap);
                    });
                }
                function fillTime() {
                    if (timeBuilt) { markTime(); return; }
                    colH.innerHTML = '';
                    colM.innerHTML = '';
                    colAp.innerHTML = '';
                    for (var i = 1; i <= 12; i++) {
                        (function (v) {
                            var b = document.createElement('button');
                            b.type = 'button';
                            b.className = 'ad-sf-dt-opt';
                            b.textContent = pad2(v);
                            b.setAttribute('data-val', v);
                            b.addEventListener('click', function () { state.h12 = v; sync(); markTime(); });
                            colH.appendChild(b);
                        })(i);
                    }
                    for (var j = 0; j < 60; j += 5) {
                        (function (v) {
                            var b = document.createElement('button');
                            b.type = 'button';
                            b.className = 'ad-sf-dt-opt';
                            b.textContent = pad2(v);
                            b.setAttribute('data-val', v);
                            b.addEventListener('click', function () { state.mi = v; sync(); markTime(); });
                            colM.appendChild(b);
                        })(j);
                    }
                    [['am','a. m.'],['pm','p. m.']].forEach(function (pair) {
                        var b = document.createElement('button');
                        b.type = 'button';
                        b.className = 'ad-sf-dt-opt';
                        b.textContent = pair[1];
                        b.setAttribute('data-val', pair[0]);
                        b.addEventListener('click', function () { state.ap = pair[0]; sync(); markTime(); });
                        colAp.appendChild(b);
                    });
                    timeBuilt = true;
                    markTime();
                }
                function renderCal() {
                    monthLabel.textContent = monthNames[viewMo] + ' de ' + viewY;
                    grid.innerHTML = '';
                    var first = new Date(viewY, viewMo, 1);
                    var startDow = (first.getDay() + 6) % 7;
                    var daysIn = new Date(viewY, viewMo + 1, 0).getDate();
                    var prevDays = new Date(viewY, viewMo, 0).getDate();
                    for (var i = 0; i < 42; i++) {
                        var btn = document.createElement('button');
                        btn.type = 'button';
                        var dayNum, y = viewY, mo = viewMo, muted = false;
                        if (i < startDow) {
                            dayNum = prevDays - startDow + i + 1;
                            mo = viewMo - 1;
                            if (mo < 0) { mo = 11; y -= 1; }
                            muted = true;
                        } else if (i >= startDow + daysIn) {
                            dayNum = i - startDow - daysIn + 1;
                            mo = viewMo + 1;
                            if (mo > 11) { mo = 0; y += 1; }
                            muted = true;
                        } else {
                            dayNum = i - startDow + 1;
                        }
                        btn.textContent = dayNum;
                        if (muted) btn.classList.add('is-muted');
                        if (!muted && y === state.y && mo === state.mo && dayNum === state.d) btn.classList.add('is-on');
                        (function (yy, mm, dd, isMuted) {
                            btn.addEventListener('click', function () {
                                if (isMuted) {
                                    viewY = yy; viewMo = mm;
                                }
                                state.y = yy; state.mo = mm; state.d = dd;
                                viewY = yy; viewMo = mm;
                                sync();
                                renderCal();
                            });
                        })(y, mo, dayNum, muted);
                        grid.appendChild(btn);
                    }
                }
                function openPanel() {
                    fillTime();
                    viewY = state.y; viewMo = state.mo;
                    renderCal();
                    panel.hidden = false;
                    root.classList.add('is-open');
                }
                function closePanel() {
                    panel.hidden = true;
                    root.classList.remove('is-open');
                }
                root._setDt = function (raw) {
                    state = parseDt(raw || '');
                    viewY = state.y; viewMo = state.mo;
                    sync();
                    if (timeBuilt) markTime();
                };
                root._closeDt = closePanel;
                trigger.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var willOpen = panel.hidden;
                    closeAllSfDt();
                    closeAllSfSelect();
                    if (willOpen) openPanel();
                    else closePanel();
                });
                root.querySelector('[data-sf-dt-prev]')?.addEventListener('click', function () {
                    viewMo -= 1;
                    if (viewMo < 0) { viewMo = 11; viewY -= 1; }
                    renderCal();
                });
                root.querySelector('[data-sf-dt-next]')?.addEventListener('click', function () {
                    viewMo += 1;
                    if (viewMo > 11) { viewMo = 0; viewY += 1; }
                    renderCal();
                });
                root.querySelector('[data-sf-dt-today]')?.addEventListener('click', function () {
                    var n = new Date();
                    state.y = n.getFullYear(); state.mo = n.getMonth(); state.d = n.getDate();
                    viewY = state.y; viewMo = state.mo;
                    sync(); renderCal();
                });
                root.querySelector('[data-sf-dt-clear]')?.addEventListener('click', function () {
                    hidden.value = '';
                    display.textContent = '— / — / — —:—';
                });
                if (hidden.value) sync();
                else {
                    state = parseDt('');
                    sync();
                }
            }

            document.querySelectorAll('[data-sf-select]').forEach(wireSfSelect);
            document.querySelectorAll('[data-sf-dt]').forEach(wireSfDt);
            document.addEventListener('mousedown', function (e) {
                if (!e.target.closest('[data-sf-select]')) closeAllSfSelect();
                if (!e.target.closest('[data-sf-dt]')) closeAllSfDt();
            });

            function open() { if (createModal) { createModal.hidden = false; createModal.classList.add('is-open'); syncCoverRequired(createModal); } }
            function close() {
                if (createModal) { createModal.hidden = true; createModal.classList.remove('is-open'); }
                closeAllSfSelect(); closeAllSfDt();
            }
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
                editForm.querySelectorAll('[data-sf-select]').forEach(function (root) {
                    var hidden = root.querySelector('input[type="hidden"]');
                    if (root._setSelect && hidden) root._setSelect(hidden.value);
                });
                editForm.querySelectorAll('[data-sf-dt]').forEach(function (root) {
                    var hidden = root.querySelector('input[type="hidden"]');
                    if (root._setDt && hidden) root._setDt(hidden.value);
                });
                if (editShow) editShow(btn.getAttribute('data-imagen_src') || '');
                var file = editForm.querySelector('[data-field-preview-file]');
                if (file) file.value = '';
                editModal.hidden = false;
                editModal.classList.add('is-open');
                syncCoverRequired(editModal);
            }
            function closeEdit() {
                if (editModal) { editModal.hidden = true; editModal.classList.remove('is-open'); }
                closeAllSfSelect(); closeAllSfDt();
            }
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

            (function wireEvtCalJump() {
                var cal = document.querySelector('[data-evt-cal]');
                if (!cal) return;
                cal.addEventListener('click', function (e) {
                    var btn = e.target.closest('[data-evt-jump]');
                    if (!btn || !cal.contains(btn)) return;
                    var id = btn.getAttribute('data-evt-jump');
                    if (!id) return;
                    var card = document.getElementById('evt-' + id);
                    if (!card) return;
                    card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    card.classList.add('is-jump');
                    window.setTimeout(function () { card.classList.remove('is-jump'); }, 1600);
                });
            })();
        })();
    </script>
@endsection
