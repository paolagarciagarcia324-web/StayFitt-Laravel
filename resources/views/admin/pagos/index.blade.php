@extends('layouts.admin')

@section('title', 'Pagos')
@section('subtitle', 'Cobros del club: pasarela, caja y vencimientos')

@php
    $resumen = $resumen ?? ['total' => 0, 'validados' => 0, 'pendientes' => 0, 'monto_mes' => 0, 'otros' => 0];
    $mes = $mes ?? now()->format('Y-m');
    $meses = $meses ?? collect();
    $mesAnterior = $mesAnterior ?? now()->subMonth()->format('Y-m');
    $mesSiguiente = $mesSiguiente ?? now()->addMonth()->format('Y-m');
    $mesLabel = $mesLabel ?? now()->locale('es')->translatedFormat('F Y');
    $calendario = $calendario ?? [];
    $dia = $dia ?? null;
    $total = max(0, (int) $resumen['total']);
    $ok = max(0, (int) $resumen['validados']);
    $pend = max(0, (int) $resumen['pendientes']);
    $otros = max(0, (int) ($resumen['otros'] ?? max(0, $total - $ok - $pend)));
    $pctOk = $total > 0 ? (int) round(($ok / $total) * 100) : 0;
    $pctPend = $total > 0 ? (int) round(($pend / $total) * 100) : 0;
    $pctOtros = max(0, 100 - $pctOk - $pctPend);
@endphp

@section('content')
    <div class="ad-pay-hero ad-stagger">
        <article class="ad-pay-metric is-cyan">
            <div class="ad-pay-metric-ring" style="--p: {{ min(100, max(8, $total ? 100 : 8)) }}">
                <strong data-count="{{ $total }}">{{ $total }}</strong>
            </div>
            <div>
                <span class="ad-pay-metric-label">Total cobros</span>
                <em>Del mes filtrado</em>
            </div>
        </article>
        <article class="ad-pay-metric is-ok">
            <div class="ad-pay-metric-ring" style="--p: {{ max(8, $pctOk) }}">
                <strong data-count="{{ $ok }}">{{ $ok }}</strong>
            </div>
            <div>
                <span class="ad-pay-metric-label">Validados</span>
                <em>{{ $pctOk }}% del mes</em>
            </div>
        </article>
        <article class="ad-pay-metric is-warn">
            <div class="ad-pay-metric-ring" style="--p: {{ max(8, $pctPend ?: ($pend ? 12 : 8)) }}">
                <strong data-count="{{ $pend }}">{{ $pend }}</strong>
            </div>
            <div>
                <span class="ad-pay-metric-label">Pendientes</span>
                <em>Por revisar</em>
            </div>
        </article>
        @php
            $montoMes = (float) $resumen['monto_mes'];
            if ($montoMes >= 1000000) {
                $montoCorto = number_format($montoMes / 1000000, $montoMes >= 10000000 ? 0 : 1, ',', '').'M';
            } elseif ($montoMes >= 1000) {
                $montoCorto = number_format($montoMes / 1000, 0, ',', '').'K';
            } else {
                $montoCorto = number_format($montoMes, 0, ',', '.');
            }
        @endphp
        <article class="ad-pay-metric is-pink is-money">
            <div class="ad-pay-metric-ring" style="--p: {{ min(100, max(8, (int) ($montoMes > 0 ? 72 : 8))) }}">
                <strong>${{ $montoCorto }}</strong>
            </div>
            <div class="ad-pay-metric-copy">
                <strong class="ad-pay-metric-amount">${{ number_format($montoMes, 0, ',', '.') }}</strong>
                <span class="ad-pay-metric-label">Monto validado</span>
                <em>Solo VALIDADO</em>
            </div>
        </article>
    </div>

    <div class="ad-pay-top">
        <article class="ad-pay-rate ad-wx">
            <header class="ad-wx-head">
                <h3>Tasa de validación</h3>
                <span class="ad-wx-chip">Mes</span>
            </header>
            @php
                $pieSegs = [];
                if ($ok > 0) {
                    $pieSegs[] = ['label' => 'Validados', 'pct' => max(1, $pctOk), 'color' => '#ff2d78', 'tip' => $ok.' validados · '.$pctOk.'%'];
                }
                if ($pend > 0) {
                    $pieSegs[] = ['label' => 'Pendientes', 'pct' => max(1, $pctPend), 'color' => '#2ef5e6', 'tip' => $pend.' pendientes · '.$pctPend.'%'];
                }
                if ($otros > 0) {
                    $pieSegs[] = ['label' => 'Rechazados/anulados', 'pct' => max(1, $pctOtros), 'color' => 'rgba(255,255,255,.22)', 'tip' => $otros.' rechazados/anulados · '.$pctOtros.'%'];
                }
                if ($pieSegs === []) {
                    $pieSegs[] = ['label' => 'Sin datos', 'pct' => 100, 'color' => 'rgba(255,255,255,.16)', 'tip' => 'Sin pagos en este mes'];
                }
            @endphp
            @include('partials.admin-donut', [
                'gid' => 'pay-tasa',
                'size' => 168,
                'label' => $pctOk.'%',
                'sub' => 'validados',
                'count' => $pctOk,
                'suffix' => '%',
                'segments' => $pieSegs,
            ])
            <ul class="ad-pay-rate-legend">
                <li title="Pagos con estado VALIDADO"><i style="background:#ff2d78"></i> Validados <strong>{{ $ok }}</strong></li>
                <li title="Pagos con estado PENDIENTE"><i style="background:#2ef5e6"></i> Pendientes <strong>{{ $pend }}</strong></li>
                <li title="RECHAZADO, CANCELADO o ANULADO"><i style="background:rgba(255,255,255,.28)"></i> Rechazados / anulados <strong>{{ $otros }}</strong></li>
            </ul>
        </article>

        <article class="ad-pay-period">
            <div class="ad-pay-period-nav">
                <a class="ad-pay-period-btn" href="{{ route('admin.pagos.index', array_filter(['mes' => $mesAnterior, 'q' => $q ?: null])) }}" aria-label="Mes anterior">‹</a>
                <span class="ad-pay-period-ico" aria-hidden="true">@include('partials.admin-icon', ['icon' => 'calendario'])</span>
                <a class="ad-pay-period-btn" href="{{ route('admin.pagos.index', array_filter(['mes' => $mesSiguiente, 'q' => $q ?: null])) }}" aria-label="Mes siguiente">›</a>
            </div>
            <span class="ad-pay-period-badge">Período activo</span>
            <h3>{{ $mesLabel }}</h3>
            <p>{{ $total }} cobros registrados</p>
            <ul class="ad-pay-period-list">
                <li><span>Total cobros</span><strong>{{ $total }}</strong></li>
                <li><span>Validados</span><strong>{{ $ok }}</strong></li>
                <li><span>Pendientes</span><strong>{{ $pend }}</strong></li>
                <li><span>Rechazados / anulados</span><strong>{{ $otros }}</strong></li>
                <li><span>Monto validado</span><strong>${{ number_format($resumen['monto_mes'], 0, ',', '.') }}</strong></li>
            </ul>
        </article>

        <article class="ad-pay-mini-cal ad-wx">
            <header class="ad-wx-head">
                <h3>Días con pago</h3>
                <span class="ad-wx-chip">{{ $mesLabel }}</span>
            </header>
            <div class="ad-pay-cal-dows" aria-hidden="true">
                @foreach (['L', 'M', 'X', 'J', 'V', 'S', 'D'] as $dow)
                    <span>{{ $dow }}</span>
                @endforeach
            </div>
            <div class="ad-pay-cal-grid">
                @foreach ($calendario as $celda)
                    @if ($celda['enMes'])
                        <a
                            class="ad-pay-cal-day {{ $celda['hoy'] ? 'is-today' : '' }} {{ $celda['count'] > 0 ? 'has-pay' : '' }} {{ $celda['activo'] ? 'is-on' : '' }}"
                            href="{{ route('admin.pagos.index', array_filter(['mes' => $mes, 'dia' => $celda['fecha'], 'q' => $q ?: null])) }}#pay-detalle"
                            title="{{ $celda['count'] > 0 ? $celda['count'].' pago(s)' : 'Sin pagos' }}"
                        >
                            <strong>{{ $celda['dia'] }}</strong>
                            @if ($celda['count'] > 0)
                                <em>{{ $celda['count'] }}</em>
                            @endif
                        </a>
                    @else
                        <span class="ad-pay-cal-day is-muted">{{ $celda['dia'] }}</span>
                    @endif
                @endforeach
            </div>
            @if ($dia)
                <a class="ad-pay-cal-clear" href="{{ route('admin.pagos.index', array_filter(['mes' => $mes, 'q' => $q ?: null])) }}#pay-detalle">Quitar filtro de día</a>
            @endif
        </article>
    </div>

    <section class="ad-pay-detail" id="pay-detalle" data-pay-board>
        <div class="ad-pay-detail-bar">
            <div>
                <h3>Detalle de pago</h3>
                @if ($dia)
                    <span class="ad-pay-detail-note">Filtrado: {{ \Illuminate\Support\Carbon::parse($dia)->format('d/m/Y') }}</span>
                @endif
            </div>
            <div class="ad-pay-filters">
                @include('partials.admin-search', [
                    'action' => route('admin.pagos.index'),
                    'value' => $q,
                    'placeholder' => 'Persona, referencia o correo',
                    'live' => true,
                ])
            </div>
        </div>

        <div class="ad-pay-cards">
            @forelse ($pagos as $p)
                @php
                    $metodo = $p->metodo_pago ?: $p->metodo ?: '—';
                    $pas = strtoupper((string) ($p->pasarela ?: ''));
                    $esPasarela = ($pas !== '' && ! in_array($pas, ['NINGUNA', 'PENDIENTE'], true))
                        || str_contains(strtoupper((string) $metodo), 'PASARELA');
                    $canal = $esPasarela ? 'Pasarela' : 'Comprobante';
                    $ref = $p->referencia ?: $p->referencia_pago ?: '#'.$p->id_pago;
                    $user = $p->cliente?->usuario
                        ?: $p->planCliente?->cliente?->usuario;
                    $nombre = trim(($user?->nombres ?? '').' '.($user?->apellidos ?? ''));
                    if ($nombre === '') {
                        $nombre = trim(($p->solicitud?->nombres ?? '').' '.($p->solicitud?->apellidos ?? ''));
                    }
                    if ($nombre === '') {
                        $nombre = $user?->correo
                            ?: ($p->solicitud?->correo ?: 'Pago '.$ref);
                    }
                    $correo = $user?->correo ?: ($p->solicitud?->correo ?: '');
                    $estado = (string) $p->estado_pago;
                    $score = $estado === 'VALIDADO' ? 100 : ($estado === 'PENDIENTE' ? 48 : 12);
                    $tone = $estado === 'VALIDADO' ? 'is-ok' : ($estado === 'PENDIENTE' ? 'is-wait' : 'is-bad');
                    $fechaPago = $p->fechaPagoEfectiva();
                    $searchHay = trim($nombre.' '.$correo.' '.$ref.' '.$canal.' '.$metodo.' '.$estado);
                @endphp
                <a
                    class="ad-pay-card {{ $tone }}"
                    href="{{ route('admin.pagos.show', $p->id_pago) }}"
                    data-pay-card
                    data-search="{{ $searchHay }}"
                >
                    <header class="ad-pay-card-head">
                        @if ($user)
                            @include('partials.admin-avatar', ['user' => $user, 'size' => 'md'])
                        @else
                            @php
                                $iniciales = strtoupper(
                                    mb_substr((string) ($p->solicitud?->nombres ?: $nombre), 0, 1).
                                    mb_substr((string) ($p->solicitud?->apellidos ?: 'F'), 0, 1)
                                );
                            @endphp
                            <span class="ad-user-avatar is-initials">{{ $iniciales }}</span>
                        @endif
                        <div>
                            <strong class="ad-pay-card-name">{{ $nombre }}</strong>
                            @if ($correo !== '')
                                <em class="ad-user-mail">{{ $correo }}</em>
                            @endif
                        </div>
                    </header>
                    <dl class="ad-pay-card-meta">
                        <div><dt>REF</dt><dd>{{ $ref }}</dd></div>
                        <div><dt>MONTO</dt><dd>${{ number_format((float) $p->monto, 0, ',', '.') }}</dd></div>
                        <div><dt>CANAL</dt><dd>{{ $canal }} · {{ $metodo }}</dd></div>
                        <div><dt>FECHA</dt><dd>{{ $fechaPago ? $fechaPago->format('d/m/Y H:i') : '—' }}</dd></div>
                    </dl>
                    <footer class="ad-pay-card-foot">
                        <span class="ad-pay-card-seal" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12 2.2l2.4 4.9 5.4.8-3.9 3.8.9 5.4L12 14.6 7.2 17.1l.9-5.4L4.2 7.9l5.4-.8L12 2.2z"/></svg>
                        </span>
                        <strong>{{ $score }}%</strong>
                        <small>{{ $estado }}</small>
                    </footer>
                </a>
            @empty
                <p class="ad-empty ad-pay-cards-empty">No hay pagos para este filtro.</p>
            @endforelse
            <p class="ad-empty ad-pay-cards-empty" data-pay-search-empty hidden>Sin coincidencias en este bloque.</p>
        </div>
        <div class="ad-pagination">{{ $pagos->appends(array_filter(['mes' => $mes, 'q' => $q, 'dia' => $dia]))->links() }}</div>
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
})();
</script>
@endpush
