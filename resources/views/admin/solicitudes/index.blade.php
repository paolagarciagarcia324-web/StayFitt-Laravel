@extends('layouts.admin')

@section('title', 'Solicitudes')
@section('subtitle', 'Cola humana: caja, transferencia e incidencias')

@php
    $pendTotal = (int) ($pendientesTotal ?? $pendientes->count());
    $histTotal = (int) ($historialTotal ?? $historial->total());
    $okTotal = (int) ($validadasTotal ?? 0);
    $badTotal = (int) ($rechazadasTotal ?? max(0, $histTotal - $okTotal));
    $colaPct = min(100, max(8, $pendTotal * 12));
    $okPct = $histTotal > 0 ? (int) round(($okTotal / $histTotal) * 100) : 0;
    $badPct = max(0, 100 - $okPct);
    $pendList = $pendientes->values();
    $histList = collect($historial->items());
@endphp

@section('content')
    <div class="ad-sol-ops ad-stagger" data-sol-page>
        <section class="ad-dash-hero ad-wx ad-sol-ops-hero">
            <div>
                <p class="ad-dash-kicker">Operación · validación</p>
                <h2>Revisa lo que la pasarela no cerró</h2>
                <p>Si el cobro ya se confirmó, el cliente entra solo. Aquí llegan caja, transferencia o incidencias.</p>
                <div class="ad-dash-hero-metrics">
                    <a class="ad-dash-metric is-btn" href="#sol-pendientes">
                        <strong data-count="{{ $pendTotal }}">{{ $pendTotal }}</strong>
                        <span>Por revisar</span>
                    </a>
                    <a class="ad-dash-metric is-btn" href="#sol-historial">
                        <strong data-count="{{ $histTotal }}">{{ $histTotal }}</strong>
                        <span>Historial</span>
                    </a>
                </div>
            </div>
        </section>

        <article class="ad-wx ad-sol-chart">
            <header class="ad-wx-head">
                <h3>Cola</h3>
                <span class="ad-wx-chip is-live">Live</span>
            </header>
            @include('partials.admin-donut', [
                'gid' => 'sol-cola',
                'size' => 140,
                'label' => (string) $pendTotal,
                'sub' => 'pendientes',
                'count' => $pendTotal,
                'segments' => [
                    ['label' => 'Pendientes', 'pct' => max(1, $colaPct), 'color' => '#2ef5e6', 'tip' => $pendTotal.' en cola · por revisar'],
                    ['label' => 'Capacidad', 'pct' => max(1, 100 - $colaPct), 'color' => 'rgba(255,255,255,.14)', 'tip' => 'Espacio libre en cola'],
                ],
            ])
        </article>

        <article class="ad-wx ad-sol-chart">
            <header class="ad-wx-head">
                <h3>Cerradas</h3>
                <span class="ad-wx-chip">Mes</span>
            </header>
            @include('partials.admin-donut', [
                'gid' => 'sol-cerradas',
                'size' => 140,
                'label' => (string) $histTotal,
                'sub' => 'en historial',
                'count' => $histTotal,
                'segments' => [
                    ['label' => 'Validadas', 'pct' => max(1, $okPct ?: 1), 'color' => '#ff2d78', 'tip' => $okTotal.' validadas · '.$okPct.'%'],
                    ['label' => 'Rechazadas', 'pct' => max(1, $badPct ?: 1), 'color' => '#2ef5e6', 'tip' => $badTotal.' rechazadas/canceladas · '.$badPct.'%'],
                ],
            ])
        </article>
    </div>

    <section class="ad-sol-block" id="sol-pendientes" data-pend-rail>
        <div class="ad-sol-block-head">
            <h3>Pendientes</h3>
            @if ($pendList->isNotEmpty())
                <div class="ad-order-pager" style="margin:0">
                    <span class="ad-order-pager-meta">{{ $pendList->count() }} en cola</span>
                    <div class="ad-order-pager-actions">
                        <button type="button" class="ad-order-nav" data-pend-scroll="-1" aria-label="Anterior">
                            @include('partials.admin-icon', ['icon' => 'chevron-left'])
                        </button>
                        <button type="button" class="ad-order-nav" data-pend-scroll="1" aria-label="Siguiente">
                            @include('partials.admin-icon', ['icon' => 'chevron-right'])
                        </button>
                    </div>
                </div>
            @endif
        </div>

        @if ($pendList->isEmpty())
            <p class="ad-empty">Nada en cola. El flujo automático está al día.</p>
        @else
            <div class="ad-pend-rail-shell">
                <div class="ad-pend-rail" data-pend-track>
                    @foreach ($pendList as $s)
                        @php
                            $monto = number_format((float) ($s->total ?? $s->pago?->monto), 0, ',', '.');
                            $plan = $s->plan?->nombre ?: 'Servicio presencial';
                            $fecha = $s->creado_en ? \Illuminate\Support\Carbon::parse($s->creado_en)->format('d/m/Y H:i') : '—';
                            $pasarela = strtoupper((string) ($s->pago?->pasarela ?: 'CAJA / TRANSFERENCIA'));
                        @endphp
                        <article class="ad-pend-card" tabindex="0">
                            <div class="ad-pend-card-face">
                                <header>
                                    @include('partials.admin-solicitud-codigo', ['codigo' => $s->codigo_solicitud])
                                    <span class="ad-badge ad-badge-pendiente">PENDIENTE</span>
                                </header>
                                <strong class="ad-pend-name">{{ $s->nombres }} {{ $s->apellidos }}</strong>
                                <em class="ad-user-mail">{{ $s->correo }}</em>
                                <div class="ad-legend-row">
                                    <span>{{ $plan }}</span>
                                    <strong>${{ $monto }}</strong>
                                </div>
                                <span class="ad-user-mail">{{ $fecha }}</span>
                                <a class="ad-pend-btn is-fucsia" href="{{ route('admin.solicitudes.show', $s->id_solicitud) }}">Revisar y decidir</a>
                                <div class="ad-pend-card-detail">
                                    <strong>{{ $s->nombres }} {{ $s->apellidos }}</strong>
                                    <dl>
                                        <div><dt>Código</dt><dd>{{ $s->codigo_solicitud }}</dd></div>
                                        <div><dt>Plan</dt><dd>{{ $plan }}</dd></div>
                                        <div><dt>Monto</dt><dd>${{ $monto }}</dd></div>
                                        <div><dt>Canal</dt><dd>{{ $pasarela }}</dd></div>
                                        <div><dt>Ingreso</dt><dd>{{ $fecha }}</dd></div>
                                        <div><dt>Correo</dt><dd>{{ $s->correo }}</dd></div>
                                    </dl>
                                    <a class="ad-pend-btn is-menta" href="{{ route('admin.solicitudes.show', $s->id_solicitud) }}">Abrir solicitud</a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="ad-pend-rail-dots" data-pend-dots aria-hidden="true"></div>
            </div>
        @endif
    </section>

    <section class="ad-sol-block" id="sol-historial" data-hist-flow>
        <div class="ad-sol-block-head">
            <h3>Historial</h3>
            <span class="ad-sol-block-note">{{ $histTotal }} registros</span>
        </div>

        @if ($histList->isEmpty())
            <p class="ad-empty">Sin historial todavía.</p>
        @else
            <div class="ad-hist-flow">
                <button type="button" class="ad-hist-flow-nav is-prev" data-hist-prev aria-label="Anterior">
                    @include('partials.admin-icon', ['icon' => 'chevron-left'])
                </button>

                <div class="ad-hist-flow-stage">
                    <div class="ad-hist-compose">
                        <div class="ad-hist-stack" data-hist-stack>
                            @foreach ($histList as $i => $h)
                                @php
                                    $ok = $h->estado_solicitud === 'VALIDADA';
                                    $iniciales = strtoupper(mb_substr($h->nombres ?? 'S', 0, 1).mb_substr($h->apellidos ?? 'F', 0, 1));
                                    $plan = $h->plan?->nombre ?: '—';
                                    $fecha = $h->fecha_revision
                                        ? \Illuminate\Support\Carbon::parse($h->fecha_revision)->format('d/m/Y H:i')
                                        : '—';
                                @endphp
                                <button
                                    type="button"
                                    class="ad-hist-poster {{ $i === 0 ? 'is-active' : '' }} {{ $ok ? 'is-ok' : 'is-bad' }}"
                                    data-hist-item
                                    data-index="{{ $i }}"
                                    data-name="{{ $h->nombres }} {{ $h->apellidos }}"
                                    data-code="{{ $h->codigo_solicitud }}"
                                    data-plan="{{ $plan }}"
                                    data-status="{{ $h->estado_solicitud }}"
                                    data-fecha="{{ $fecha }}"
                                    data-url="{{ route('admin.solicitudes.show', $h->id_solicitud) }}"
                                    aria-pressed="{{ $i === 0 ? 'true' : 'false' }}"
                                >
                                    <span class="ad-hist-poster-avatar">{{ $iniciales }}</span>
                                    <span class="ad-hist-poster-name">{{ $h->nombres }} {{ $h->apellidos }}</span>
                                    <span class="ad-hist-poster-code">{{ $h->codigo_solicitud }}</span>
                                    <span class="ad-hist-poster-status">{{ $h->estado_solicitud }}</span>
                                </button>
                            @endforeach
                        </div>

                        <aside class="ad-hist-panel" data-hist-panel>
                            @php $first = $histList->first(); $okFirst = $first && $first->estado_solicitud === 'VALIDADA'; @endphp
                            <p class="ad-hist-panel-kicker">Detalle</p>
                            <h4 data-hist-name>{{ $first?->nombres }} {{ $first?->apellidos }}</h4>
                            <p class="ad-hist-panel-copy">
                                Solicitud <strong data-hist-code>{{ $first?->codigo_solicitud }}</strong>
                                · plan <strong data-hist-plan>{{ $first?->plan?->nombre ?: '—' }}</strong>.
                                Revisada el <strong data-hist-fecha>{{ $first?->fecha_revision ? \Illuminate\Support\Carbon::parse($first->fecha_revision)->format('d/m/Y H:i') : '—' }}</strong>.
                            </p>
                            <div class="ad-hist-panel-score {{ $okFirst ? 'is-ok' : 'is-bad' }}">
                                <span data-hist-status>{{ $first?->estado_solicitud }}</span>
                            </div>
                            <a class="ad-pend-btn is-menta" data-hist-link href="{{ $first ? route('admin.solicitudes.show', $first->id_solicitud) : '#' }}">Ver solicitud</a>
                        </aside>
                    </div>
                </div>

                <button type="button" class="ad-hist-flow-nav is-next" data-hist-next aria-label="Siguiente">
                    @include('partials.admin-icon', ['icon' => 'chevron-right'])
                </button>
            </div>
            <p class="ad-hist-flow-meta"><span data-hist-meta>1 / {{ $histList->count() }}</span></p>
            <div class="ad-pagination">{{ $historial->fragment('sol-historial')->links() }}</div>
        @endif
    </section>
@endsection

@push('scripts')
<script>
(function () {
    var tip = document.createElement('div');
    tip.className = 'ad-chart-tip';
    tip.hidden = true;
    document.body.appendChild(tip);

    function showTip(el, evt) {
        var text = el.getAttribute('data-tip');
        if (!text) return;
        tip.textContent = text;
        tip.style.setProperty('--tip-color', el.getAttribute('data-tip-color') || '#2ef5e6');
        tip.hidden = false;
        tip.classList.add('is-on');
        var x = evt.clientX + 14;
        var y = evt.clientY + 14;
        var rect = tip.getBoundingClientRect();
        if (x + rect.width > window.innerWidth - 12) x = evt.clientX - rect.width - 12;
        if (y + rect.height > window.innerHeight - 12) y = evt.clientY - rect.height - 12;
        tip.style.transform = 'translate(' + x + 'px,' + y + 'px)';
        el.classList.add('is-hot');
    }
    function hideTip(el) {
        tip.hidden = true;
        tip.classList.remove('is-on');
        if (el) el.classList.remove('is-hot');
        document.querySelectorAll('.is-hot').forEach(function (n) { n.classList.remove('is-hot'); });
    }
    document.addEventListener('pointerover', function (e) {
        var el = e.target.closest('.is-tip');
        if (!el) return;
        showTip(el, e);
    });
    document.addEventListener('pointermove', function (e) {
        var el = e.target.closest('.is-tip');
        if (!el || tip.hidden) return;
        showTip(el, e);
    });
    document.addEventListener('pointerout', function (e) {
        var el = e.target.closest('.is-tip');
        if (!el) return;
        var to = e.relatedTarget && e.relatedTarget.closest ? e.relatedTarget.closest('.is-tip') : null;
        if (to === el) return;
        hideTip(el);
    });

    /* Pendientes: fila horizontal */
    var rail = document.querySelector('[data-pend-rail]');
    if (rail) {
        var track = rail.querySelector('[data-pend-track]');
        var dots = rail.querySelector('[data-pend-dots]');
        if (track) {
            rail.querySelectorAll('[data-pend-scroll]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var dir = parseInt(btn.getAttribute('data-pend-scroll') || '1', 10);
                    track.scrollBy({ left: dir * Math.max(280, track.clientWidth * 0.72), behavior: 'smooth' });
                });
            });
            if (dots) {
                var cards = track.querySelectorAll('.ad-pend-card');
                var pages = Math.max(1, Math.ceil(cards.length / 3));
                for (var i = 0; i < pages; i++) {
                    var d = document.createElement('i');
                    if (i === 0) d.classList.add('is-on');
                    dots.appendChild(d);
                }
                track.addEventListener('scroll', function () {
                    var max = Math.max(1, track.scrollWidth - track.clientWidth);
                    var ratio = track.scrollLeft / max;
                    var idx = Math.min(pages - 1, Math.round(ratio * (pages - 1)));
                    dots.querySelectorAll('i').forEach(function (el, n) {
                        el.classList.toggle('is-on', n === idx);
                    });
                }, { passive: true });
            }
        }
    }

    /* Historial: cola restante a la izq (borrosa); las ya vistas desaparecen; panel a la der */
    var flow = document.querySelector('[data-hist-flow]');
    if (flow) {
        var items = Array.prototype.slice.call(flow.querySelectorAll('[data-hist-item]'));
        var panel = flow.querySelector('[data-hist-panel]');
        var link = flow.querySelector('[data-hist-link]');
        var meta = document.querySelector('[data-hist-meta]');
        var idx = 0;

        function paint() {
            items.forEach(function (item, i) {
                var offset = i - idx; // pasado < 0, restante > 0
                var behind = offset > 0 ? offset : 0; // cola visible solo a la izquierda
                var gone = offset < 0 || behind > 5; // las que avanzan desaparecen
                item.classList.toggle('is-active', i === idx);
                item.classList.toggle('is-gone', gone);
                item.setAttribute('aria-pressed', i === idx ? 'true' : 'false');
                item.toggleAttribute('hidden', gone);
                if (gone) {
                    item.style.pointerEvents = 'none';
                    return;
                }
                // Restantes apiladas a la izquierda, medio borrosas
                var x = -behind * 56;
                var y = behind ? behind * 2 : -6;
                var scale = Math.max(0.66, 1 - behind * 0.085);
                var rot = behind * 9;
                var blur = behind * 2.4;
                item.style.zIndex = String(40 - behind);
                item.style.opacity = String(Math.max(0.34, 1 - behind * 0.13));
                item.style.filter = behind ? 'blur(' + blur + 'px) saturate(' + (1 - behind * 0.08) + ')' : 'none';
                item.style.transform = 'translate(-50%, -50%) translate(' + x + 'px,' + y + 'px) scale(' + scale + ') rotateY(' + rot + 'deg)';
                item.style.pointerEvents = behind > 4 ? 'none' : 'auto';
            });

            var cur = items[idx];
            if (cur && panel) {
                var set = function (sel, val) {
                    var el = panel.querySelector(sel);
                    if (el) el.textContent = val || '—';
                };
                set('[data-hist-name]', cur.getAttribute('data-name'));
                set('[data-hist-code]', cur.getAttribute('data-code'));
                set('[data-hist-plan]', cur.getAttribute('data-plan'));
                set('[data-hist-fecha]', cur.getAttribute('data-fecha'));
                set('[data-hist-status]', cur.getAttribute('data-status'));
                if (link) link.href = cur.getAttribute('data-url') || '#';
                var score = panel.querySelector('.ad-hist-panel-score');
                if (score) {
                    var ok = (cur.getAttribute('data-status') || '') === 'VALIDADA';
                    score.classList.toggle('is-ok', ok);
                    score.classList.toggle('is-bad', !ok);
                }
            }
            if (meta) meta.textContent = (idx + 1) + ' / ' + items.length;
        }

        items.forEach(function (item, i) {
            item.addEventListener('click', function () { idx = i; paint(); });
        });
        var prev = flow.querySelector('[data-hist-prev]');
        var next = flow.querySelector('[data-hist-next]');
        if (prev) prev.addEventListener('click', function () {
            idx = Math.max(0, idx - 1);
            paint();
        });
        if (next) next.addEventListener('click', function () {
            idx = Math.min(items.length - 1, idx + 1);
            paint();
        });
        paint();
    }
})();
</script>
@endpush
