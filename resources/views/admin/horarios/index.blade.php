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
    @php
        $hActivos = $horarios->where('activo', true)->count();
        $hCupo = (int) $horarios->sum('cupo_ocupado');
        $hMax = max(1, (int) $horarios->sum('cupo_maximo'));
        $hPct = (int) round(($hCupo / $hMax) * 100);
    @endphp
    <div class="ad-horario-kpis ad-stagger">
        <article class="ad-horario-kpi is-cyan">
            <div class="ad-horario-kpi-orb" aria-hidden="true">
                <i>@include('partials.admin-icon', ['icon' => 'horarios'])</i>
            </div>
            <div>
                <strong data-count="{{ $horarios->count() }}">{{ $horarios->count() }}</strong>
                <span>Horarios</span>
                <em>Franjas L–V</em>
            </div>
        </article>
        <article class="ad-horario-kpi is-mint">
            <div class="ad-horario-kpi-orb" aria-hidden="true">
                <i>@include('partials.admin-icon', ['icon' => 'clientes'])</i>
            </div>
            <div>
                <strong data-count="{{ $hActivos }}">{{ $hActivos }}</strong>
                <span>Activos en registro</span>
                <em>Visibles al público</em>
            </div>
        </article>
        <article class="ad-horario-kpi is-pink">
            <div class="ad-horario-kpi-orb" aria-hidden="true">
                <i>@include('partials.admin-icon', ['icon' => 'cupo'])</i>
            </div>
            <div>
                <strong>{{ $hCupo }}/{{ $hMax }}</strong>
                <span>Cupo ocupado</span>
                <em>{{ $hPct }}% de la sala</em>
            </div>
            <div class="ad-horario-kpi-bar" aria-hidden="true"><i style="width: {{ $hPct }}%"></i></div>
        </article>
        <article class="ad-horario-kpi is-violet">
            <div class="ad-horario-kpi-orb" aria-hidden="true">
                <i>@include('partials.admin-icon', ['icon' => 'pagos'])</i>
            </div>
            <div>
                <strong data-count="{{ $hPct }}" data-suffix="%">{{ $hPct }}%</strong>
                <span>Ocupación sala</span>
                <em>En vivo</em>
            </div>
            <svg class="ad-horario-kpi-ring" viewBox="0 0 36 36" aria-hidden="true">
                <circle cx="18" cy="18" r="15" pathLength="100"/>
                <circle cx="18" cy="18" r="15" pathLength="100" style="--p: {{ max(2, $hPct) }}"/>
            </svg>
        </article>
    </div>

    @forelse ($horarios->chunk(10) as $bloqueIdx => $bloque)
        @php
            $from = ($bloqueIdx * 10) + 1;
            $to = $bloqueIdx * 10 + $bloque->count();
        @endphp
        <section class="ad-horario-block">
            <header class="ad-horario-block-head">
                <span class="ad-horario-block-badge">Bloque {{ $bloqueIdx + 1 }}</span>
                <div>
                    <strong>Horarios {{ $from }}–{{ $to }}</strong>
                    <em>{{ $bloque->count() }} de 10 por bloque</em>
                </div>
            </header>
            <div class="ad-horario-board ad-stagger">
                @foreach ($bloque as $horario)
                    @php
                        $grupoVinculado = $horario->grupoWhatsappVinculado();
                        $grupoWa = $horario->nombreGrupoWhatsapp();
                        $urlWa = $horario->urlGrupoWhatsapp();
                        $cupoMax = max(1, (int) $horario->cupo_maximo);
                        $cupoPct = min(100, (int) round(((int) $horario->cupo_ocupado / $cupoMax) * 100));
                        $horaHero = \App\Models\HorarioPresencial::formatoHora12((string) $horario->hora_inicio);
                        $localIdx = $loop->index;
                    @endphp
                    <article class="ad-horario-card {{ $horario->activo ? 'is-on' : 'is-off' }}" style="--d: {{ $localIdx * 70 }}ms">
                        <div class="ad-horario-card-shine" aria-hidden="true"></div>
                        <div class="ad-horario-card-nav" aria-hidden="true">
                            <span class="ad-horario-card-orb">@include('partials.admin-icon', ['icon' => 'horarios'])</span>
                        </div>
                        <span class="ad-horario-card-badge {{ $horario->activo ? 'is-on' : 'is-off' }}">
                            {{ $horario->activo ? 'Horario activo' : 'Horario inactivo' }}
                        </span>
                        <h3 class="ad-horario-card-title">{{ $horaHero }}</h3>
                        <p class="ad-horario-card-sub">{{ $horario->nombre }} · hasta {{ \App\Models\HorarioPresencial::formatoHora12((string) $horario->hora_fin) }}</p>

                        <ul class="ad-horario-card-list">
                            <li><span>Código</span><strong>{{ $horario->codigo }}</strong></li>
                            <li><span>Días</span><strong>{{ $horario->dias }}</strong></li>
                            <li><span>Cupo</span><strong>{{ $horario->cupo_ocupado }}/{{ $horario->cupo_maximo }}</strong></li>
                            <li>
                                <span>Ocupación</span>
                                <strong>{{ $cupoPct }}%</strong>
                            </li>
                        </ul>
                        <div class="ad-horario-cupo-bar" role="presentation">
                            <i style="width: {{ $cupoPct }}%"></i>
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
                                data-grupo-id="{{ $grupoVinculado?->id_grupo_wa ?? '' }}"
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
                @endforeach
            </div>
        </section>
    @empty
        <div class="ad-empty-hero ad-horario-empty">
            <span class="ad-empty-orb">@include('partials.admin-icon', ['icon' => 'horarios'])</span>
            <strong>Sin horarios</strong>
            <button class="ad-btn" type="button" data-open-horario-create>Agregar el primero</button>
        </div>
    @endforelse

    <div class="ad-modal ad-horario-modal" data-horario-modal hidden>
        <div class="ad-modal-card ad-horario-modal-card">
            <div class="ad-horario-modal-glow" aria-hidden="true"></div>
            <header class="ad-modal-head">
                <div>
                    <p class="ad-horario-modal-kicker">Sala presencial</p>
                    <h2 data-horario-title>Editar horario</h2>
                </div>
                <button type="button" class="ad-modal-close" data-close-horario aria-label="Cerrar">×</button>
            </header>
            <form class="ad-form ad-horario-form" method="POST" data-horario-form>
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
                        <div class="ad-sf-time" data-sf-time data-sf-for="hora_inicio">
                            <span class="ad-sf-time-label">Hora inicio *</span>
                            <input type="hidden" name="hora_inicio" data-field="hora_inicio" required>
                            <button type="button" class="ad-sf-time-trigger" data-sf-time-trigger aria-haspopup="dialog">
                                <strong data-sf-time-display>—</strong>
                                <i aria-hidden="true">@include('partials.admin-icon', ['icon' => 'horarios'])</i>
                            </button>
                            <div class="ad-sf-time-panel" data-sf-time-panel hidden>
                                <div class="ad-sf-time-cols">
                                    <div class="ad-sf-time-col" data-sf-col="h"></div>
                                    <div class="ad-sf-time-col" data-sf-col="m"></div>
                                    <div class="ad-sf-time-col" data-sf-col="ap"></div>
                                </div>
                            </div>
                        </div>
                        <div class="ad-sf-time" data-sf-time data-sf-for="hora_fin">
                            <span class="ad-sf-time-label">Hora fin *</span>
                            <input type="hidden" name="hora_fin" data-field="hora_fin" required>
                            <button type="button" class="ad-sf-time-trigger" data-sf-time-trigger aria-haspopup="dialog">
                                <strong data-sf-time-display>—</strong>
                                <i aria-hidden="true">@include('partials.admin-icon', ['icon' => 'horarios'])</i>
                            </button>
                            <div class="ad-sf-time-panel" data-sf-time-panel hidden>
                                <div class="ad-sf-time-cols">
                                    <div class="ad-sf-time-col" data-sf-col="h"></div>
                                    <div class="ad-sf-time-col" data-sf-col="m"></div>
                                    <div class="ad-sf-time-col" data-sf-col="ap"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ad-wa-pick" data-wa-pick>
                    <span class="ad-sf-time-label">Grupo WhatsApp *</span>
                    <input type="hidden" name="id_grupo_wa" data-field="id_grupo_wa" required>
                    <button type="button" class="ad-wa-pick-trigger" data-wa-pick-trigger>
                        <span class="ad-wa-pick-ico" aria-hidden="true">@include('partials.admin-icon', ['icon' => 'whatsapp'])</span>
                        <span class="ad-wa-pick-copy">
                            <strong data-wa-pick-name>Elegir grupo real</strong>
                            <em data-wa-pick-meta>Solo grupos activos del canal Business</em>
                        </span>
                        <span class="ad-wa-pick-chev" aria-hidden="true">▾</span>
                    </button>
                    <div class="ad-wa-pick-panel" data-wa-pick-panel hidden>
                        <header class="ad-wa-pick-head">
                            <strong>Grupos WhatsApp</strong>
                            <a href="{{ route('admin.whatsapp.index') }}" target="_blank" rel="noopener">Gestionar en Chats</a>
                        </header>
                        <input type="search" class="ad-wa-pick-search" data-wa-pick-search placeholder="Buscar grupo…" autocomplete="off">
                        <div class="ad-wa-pick-list" data-wa-pick-list>
                            @forelse ($gruposWhatsapp as $g)
                                <button type="button"
                                    class="ad-wa-pick-item"
                                    data-wa-item
                                    data-id="{{ $g->id_grupo_wa }}"
                                    data-nombre="{{ $g->nombre }}"
                                    data-meta="{{ $g->tipo }}{{ $g->criterio ? ' · '.$g->criterio : '' }}"
                                    data-link="{{ $g->link_invitacion }}"
                                    data-search="{{ mb_strtolower($g->nombre.' '.$g->tipo.' '.$g->criterio) }}">
                                    <strong>{{ $g->nombre }}</strong>
                                    <em>{{ $g->tipo }}{{ $g->criterio ? ' · '.$g->criterio : '' }}</em>
                                </button>
                            @empty
                                <p class="ad-wa-pick-empty">No hay grupos activos. Créalos primero en Chats → WhatsApp.</p>
                            @endforelse
                        </div>
                    </div>
                    <p class="ad-wa-pick-hint">El vínculo se toma de los grupos registrados en el canal WhatsApp Business de StayFit (no se escribe a mano).</p>
                </div>

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
                <div class="ad-modal-actions ad-horario-modal-actions">
                    <button class="ad-pend-btn is-menta" type="button" data-close-horario>Cancelar</button>
                    <button class="ad-pend-btn is-fucsia" type="submit" data-horario-submit>Guardar</button>
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
            var grupoIdInput = form && form.querySelector('[data-field="id_grupo_wa"]');
            var waPick = form && form.querySelector('[data-wa-pick]');
            var waName = waPick && waPick.querySelector('[data-wa-pick-name]');
            var waMeta = waPick && waPick.querySelector('[data-wa-pick-meta]');
            var waPanel = waPick && waPick.querySelector('[data-wa-pick-panel]');
            var waSearch = waPick && waPick.querySelector('[data-wa-pick-search]');

            function pad(n) { return String(n).padStart(2, '0'); }

            function formatDisplay(hhmm) {
                if (!hhmm || hhmm.indexOf(':') < 0) return '—';
                var p = hhmm.split(':');
                var h = parseInt(p[0], 10);
                var m = parseInt(p[1], 10);
                if (isNaN(h) || isNaN(m)) return '—';
                var ap = h >= 12 ? 'p. m.' : 'a. m.';
                var h12 = h % 12;
                if (h12 === 0) h12 = 12;
                return pad(h12) + ':' + pad(m) + ' ' + ap;
            }

            function snapMinute(m) {
                m = Math.round(m / 5) * 5;
                if (m < 0) m = 0;
                if (m > 55) m = 55;
                return m;
            }

            function parseToParts(hhmm) {
                var h = 5, m = 30;
                if (hhmm && hhmm.indexOf(':') >= 0) {
                    var p = hhmm.split(':');
                    h = parseInt(p[0], 10); m = parseInt(p[1], 10);
                    if (isNaN(h)) h = 5;
                    if (isNaN(m)) m = 30;
                }
                var ap = h >= 12 ? 'pm' : 'am';
                var h12 = h % 12;
                if (h12 === 0) h12 = 12;
                return { h12: h12, m: snapMinute(m), ap: ap };
            }

            function partsToHhmm(parts) {
                var h = parts.h12 % 12;
                if (parts.ap === 'pm') h += 12;
                return pad(h) + ':' + pad(parts.m);
            }

            function closeAllTimePanels(except) {
                form.querySelectorAll('[data-sf-time]').forEach(function (root) {
                    var p = root.querySelector('[data-sf-time-panel]');
                    if (except && p === except) return;
                    if (p) {
                        p.hidden = true;
                        p.style.position = '';
                        p.style.left = '';
                        p.style.top = '';
                        p.style.bottom = '';
                        p.style.width = '';
                        p.style.right = '';
                    }
                    root.classList.remove('is-open');
                });
            }

            function buildTimePicker(root) {
                var hidden = root.querySelector('input[type="hidden"]');
                var display = root.querySelector('[data-sf-time-display]');
                var trigger = root.querySelector('[data-sf-time-trigger]');
                var panel = root.querySelector('[data-sf-time-panel]');
                var colH = root.querySelector('[data-sf-col="h"]');
                var colM = root.querySelector('[data-sf-col="m"]');
                var colAp = root.querySelector('[data-sf-col="ap"]');
                var state = parseToParts(hidden.value || '05:30');
                var built = false;

                function syncHidden() {
                    hidden.value = partsToHhmm(state);
                    display.textContent = formatDisplay(hidden.value);
                }

                function markActive() {
                    colH.querySelectorAll('[data-val]').forEach(function (btn) {
                        btn.classList.toggle('is-on', Number(btn.getAttribute('data-val')) === state.h12);
                    });
                    colM.querySelectorAll('[data-val]').forEach(function (btn) {
                        btn.classList.toggle('is-on', Number(btn.getAttribute('data-val')) === state.m);
                    });
                    colAp.querySelectorAll('[data-val]').forEach(function (btn) {
                        btn.classList.toggle('is-on', btn.getAttribute('data-val') === state.ap);
                    });
                }

                function fillCol(col, items, key) {
                    col.innerHTML = '';
                    var frag = document.createDocumentFragment();
                    items.forEach(function (item) {
                        var btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'ad-sf-time-opt';
                        btn.textContent = item.label;
                        btn.setAttribute('data-val', item.value);
                        btn.addEventListener('click', function () {
                            state[key] = item.value;
                            syncHidden();
                            markActive();
                        });
                        frag.appendChild(btn);
                    });
                    col.appendChild(frag);
                }

                function ensureBuilt() {
                    if (built) return;
                    var hours = [];
                    for (var i = 1; i <= 12; i++) hours.push({ value: i, label: pad(i) });
                    var mins = [];
                    for (var j = 0; j < 60; j += 5) mins.push({ value: j, label: pad(j) });
                    fillCol(colH, hours, 'h12');
                    fillCol(colM, mins, 'm');
                    fillCol(colAp, [
                        { value: 'am', label: 'a. m.' },
                        { value: 'pm', label: 'p. m.' }
                    ], 'ap');
                    built = true;
                }

                function placePanel() {
                    var r = trigger.getBoundingClientRect();
                    var panelH = 210;
                    var spaceBelow = window.innerHeight - r.bottom;
                    panel.style.position = 'fixed';
                    panel.style.left = Math.max(12, r.left) + 'px';
                    panel.style.width = Math.max(220, r.width) + 'px';
                    panel.style.right = 'auto';
                    panel.style.zIndex = '120';
                    if (spaceBelow < panelH + 16) {
                        panel.style.top = 'auto';
                        panel.style.bottom = Math.max(12, window.innerHeight - r.top + 8) + 'px';
                    } else {
                        panel.style.top = (r.bottom + 8) + 'px';
                        panel.style.bottom = 'auto';
                    }
                }

                function scrollActiveIntoView() {
                    [colH, colM].forEach(function (col) {
                        var on = col.querySelector('.is-on');
                        if (!on) return;
                        var top = on.offsetTop - (col.clientHeight / 2) + (on.offsetHeight / 2);
                        col.scrollTop = Math.max(0, top);
                    });
                }

                function openPanel() {
                    ensureBuilt();
                    markActive();
                    panel.hidden = false;
                    root.classList.add('is-open');
                    placePanel();
                    requestAnimationFrame(scrollActiveIntoView);
                }

                function closePanel() {
                    panel.hidden = true;
                    root.classList.remove('is-open');
                    panel.style.position = '';
                    panel.style.left = '';
                    panel.style.top = '';
                    panel.style.bottom = '';
                    panel.style.width = '';
                    panel.style.right = '';
                    panel.style.zIndex = '';
                }

                root._setTime = function (hhmm) {
                    state = parseToParts(hhmm || '05:30');
                    syncHidden();
                    if (built) markActive();
                };

                root._closeTime = closePanel;

                trigger.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var willOpen = panel.hidden;
                    closeAllTimePanels();
                    if (waPanel) waPanel.hidden = true;
                    waPick?.classList.remove('is-open');
                    if (willOpen) openPanel();
                    else closePanel();
                });

                window.addEventListener('resize', function () {
                    if (!panel.hidden) placePanel();
                });

                syncHidden();
            }

            form.querySelectorAll('[data-sf-time]').forEach(buildTimePicker);

            document.addEventListener('mousedown', function (e) {
                if (!e.target.closest('[data-sf-time]')) closeAllTimePanels();
                if (waPick && !e.target.closest('[data-wa-pick]')) {
                    if (waPanel) waPanel.hidden = true;
                    waPick.classList.remove('is-open');
                }
            });

            function setWaGroup(id, nombre, meta) {
                if (grupoIdInput) grupoIdInput.value = id || '';
                if (waName) waName.textContent = nombre || 'Elegir grupo real';
                if (waMeta) waMeta.textContent = meta || 'Solo grupos activos del canal Business';
                waPick?.classList.toggle('is-filled', !!id);
                waPick?.querySelectorAll('[data-wa-item]').forEach(function (item) {
                    item.classList.toggle('is-on', String(item.getAttribute('data-id')) === String(id));
                });
            }

            function clearWaGroup() {
                setWaGroup('', 'Elegir grupo real', 'Solo grupos activos del canal Business');
            }

            waPick?.querySelector('[data-wa-pick-trigger]')?.addEventListener('click', function () {
                closeAllTimePanels();
                if (!waPanel) return;
                waPanel.hidden = !waPanel.hidden;
                if (!waPanel.hidden && waSearch) {
                    waSearch.value = '';
                    waPick.querySelectorAll('[data-wa-item]').forEach(function (el) { el.hidden = false; });
                    waSearch.focus();
                }
            });

            waSearch?.addEventListener('input', function () {
                var q = (waSearch.value || '').toLowerCase().trim();
                waPick.querySelectorAll('[data-wa-item]').forEach(function (el) {
                    var hay = el.getAttribute('data-search') || '';
                    el.hidden = !!(q && hay.indexOf(q) === -1);
                });
            });

            waPick?.querySelectorAll('[data-wa-item]').forEach(function (item) {
                item.addEventListener('click', function () {
                    setWaGroup(
                        item.getAttribute('data-id'),
                        item.getAttribute('data-nombre'),
                        item.getAttribute('data-meta')
                    );
                    if (waPanel) waPanel.hidden = true;
                });
            });

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

            function setTimeFields(inicio, fin) {
                form.querySelectorAll('[data-sf-time]').forEach(function (root) {
                    var key = root.getAttribute('data-sf-for');
                    var val = key === 'hora_fin' ? fin : inicio;
                    if (typeof root._setTime === 'function') root._setTime(val);
                });
            }

            function openEdit(btn) {
                if (!modal || !form) return;
                setCreateMode(false);
                form.action = '{{ url('admin/horarios') }}/' + btn.getAttribute('data-id');
                if (methodInput) methodInput.value = 'PUT';
                title.textContent = 'Editar horario';
                form.querySelector('[data-field="nombre"]').value = btn.getAttribute('data-nombre') || '';
                form.querySelector('[data-field="dias"]').value = btn.getAttribute('data-dias') || '';
                setTimeFields(btn.getAttribute('data-hora-inicio') || '05:30', btn.getAttribute('data-hora-fin') || '06:30');
                form.querySelector('[data-field="cupo"]').value = btn.getAttribute('data-cupo') || '';
                var gid = btn.getAttribute('data-grupo-id') || '';
                if (gid) {
                    var item = waPick?.querySelector('[data-wa-item][data-id="' + gid + '"]');
                    if (item) setWaGroup(gid, item.getAttribute('data-nombre'), item.getAttribute('data-meta'));
                    else setWaGroup(gid, btn.getAttribute('data-link') || 'Grupo vinculado', 'Grupo del canal Business');
                } else {
                    var byName = btn.getAttribute('data-link') || '';
                    var match = null;
                    waPick?.querySelectorAll('[data-wa-item]').forEach(function (el) {
                        if (el.getAttribute('data-nombre') === byName) match = el;
                    });
                    if (match) setWaGroup(match.getAttribute('data-id'), match.getAttribute('data-nombre'), match.getAttribute('data-meta'));
                    else clearWaGroup();
                }
                if (activoInput) activoInput.checked = btn.getAttribute('data-activo') === '1';
                syncActivoUi();
                closeAllTimePanels();
                if (waPanel) waPanel.hidden = true;
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
                form.querySelector('[data-field="cupo"]').value = '18';
                if (activoInput) activoInput.checked = true;
                form.querySelector('[data-field="dias"]').value = 'Lun · Mar · Mié · Jue · Vie';
                setTimeFields('05:30', '06:30');
                clearWaGroup();
                syncActivoUi();
                closeAllTimePanels();
                if (waPanel) waPanel.hidden = true;
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
                closeAllTimePanels();
                if (waPanel) waPanel.hidden = true;
            }

            form.addEventListener('submit', function (e) {
                if (!grupoIdInput || !grupoIdInput.value) {
                    e.preventDefault();
                    waPick?.classList.add('is-error');
                    if (waPanel) waPanel.hidden = false;
                    return;
                }
                waPick?.classList.remove('is-error');
            });

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
