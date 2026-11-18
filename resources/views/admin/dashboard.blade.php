@extends('layouts.admin')

@section('title', 'Dashboard')
@section('subtitle', 'Pulso en vivo del estudio FigueFit')

@php
    // Datos reales
    $virtuales = (int) ($metricas->clientes_virtuales ?? 0);
    $presenciales = (int) ($metricas->clientes_presenciales ?? 0);
    $ocupados = (int) $horarios->sum('cupo_ocupado');
    $cupos = max(1, (int) $horarios->sum('cupo_maximo'));
    $pctCupo = min(100, (int) round(($ocupados / $cupos) * 100));
    $hora = (int) now()->format('G');
    $saludo = $hora < 12 ? 'Buenos días' : ($hora < 19 ? 'Buenas tardes' : 'Buenas noches');
    $ingreso = (float) ($metricas->ingreso_mes_actual ?? 0);
    $sparkIngreso = array_values($sparks['ingresos'] ?? []);
    $sparkClientes = array_values($sparks['clientes'] ?? []);
    $sparkPlanes = array_values($sparks['planes'] ?? []);
    $activos = (int) ($metricas->total_clientes_activos ?? 0);
    $dias = ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'];
    $planesN = (int) ($planesVendidos ?? 0);
    $tiendaN = (int) ($productosVendidos ?? 0);
    $solicitudesPendientes = (int) ($solicitudesPendientes ?? 0);
    $propuestasPendientes = (int) ($propuestasPendientes ?? 0);

    // Demo live: cubre cada proceso para ver gráficas/animación con vida
    $demo = true;
    if ($demo) {
        $activos = 48;
        $virtuales = 31;
        $presenciales = 17;
        $solicitudesPendientes = 5;
        $propuestasPendientes = 3;
        $ocupados = 54;
        $cupos = 90;
        $pctCupo = 60;
        $ingreso = 12845000;
        $planesN = 27;
        $tiendaN = 19;
        $sparkIngreso = [980000, 1250000, 870000, 1620000, 1430000, 1780000, 2100000];
        $sparkClientes = [6, 9, 7, 12, 10, 14, 11];
        $sparkPlanes = [2, 4, 3, 5, 4, 6, 5];
        $weekBars = [48, 62, 71, 58, 84, 46, 33];
        $clienteIds = \App\Models\Cliente::query()->orderBy('id_cliente')->limit(12)->pluck('id_cliente')->values()->all();
        $ordenesDemo = [
            ['numero_orden' => 'ORD-1042', 'cliente' => 'Valentina Gómez', 'cliente_id' => $clienteIds[0] ?? null, 'producto' => 'TRX suspension', 'categoria' => 'Equipamiento', 'qty' => 1, 'estado' => 'Enviado', 'total' => 189000, 'img' => 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?w=600&q=80'],
            ['numero_orden' => 'ORD-1041', 'cliente' => 'Andrés Pérez', 'cliente_id' => $clienteIds[1] ?? null, 'producto' => 'Proteína whey 1kg', 'categoria' => 'Suplementos', 'qty' => 2, 'estado' => 'Pagado', 'total' => 258000, 'img' => 'https://images.unsplash.com/photo-1593095948071-474c5cc2989d?w=600&q=80'],
            ['numero_orden' => 'ORD-1040', 'cliente' => 'Sofía Castro', 'cliente_id' => $clienteIds[2] ?? null, 'producto' => 'Top deportivo mujer', 'categoria' => 'Ropa', 'qty' => 1, 'estado' => 'Preparando', 'total' => 98000, 'img' => 'https://images.unsplash.com/photo-1518310383802-640c2de311b2?w=600&q=80'],
            ['numero_orden' => 'ORD-1039', 'cliente' => 'Julián Rojas', 'cliente_id' => $clienteIds[3] ?? null, 'producto' => 'Shaker Pro 600ml', 'categoria' => 'Hidratación', 'qty' => 3, 'estado' => 'Entregado', 'total' => 105000, 'img' => 'https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?w=600&q=80'],
            ['numero_orden' => 'ORD-1038', 'cliente' => 'Mariana López', 'cliente_id' => $clienteIds[4] ?? null, 'producto' => 'Creatina 300g', 'categoria' => 'Suplementos', 'qty' => 1, 'estado' => 'Pagado', 'total' => 79000, 'img' => 'https://images.unsplash.com/photo-1579722820308-d74e571900a9?w=600&q=80'],
            ['numero_orden' => 'ORD-1037', 'cliente' => 'Felipe Nieto', 'cliente_id' => $clienteIds[5] ?? null, 'producto' => 'Bandas elásticas set', 'categoria' => 'Accesorios', 'qty' => 2, 'estado' => 'Enviado', 'total' => 110000, 'img' => 'https://images.unsplash.com/photo-1598289431512-b97b0917affc?w=600&q=80'],
            ['numero_orden' => 'ORD-1036', 'cliente' => 'Diana Suárez', 'cliente_id' => $clienteIds[6] ?? null, 'producto' => 'Hoodie StayFit', 'categoria' => 'Ropa', 'qty' => 1, 'estado' => 'Preparando', 'total' => 120000, 'img' => 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=600&q=80'],
            ['numero_orden' => 'ORD-1035', 'cliente' => 'Laura Méndez', 'cliente_id' => $clienteIds[7] ?? null, 'producto' => 'Foam roller', 'categoria' => 'Accesorios', 'qty' => 1, 'estado' => 'Entregado', 'total' => 65000, 'img' => 'https://images.unsplash.com/photo-1599058945522-28d584b6f14f?w=600&q=80'],
            ['numero_orden' => 'ORD-1034', 'cliente' => 'Carlos Ruiz', 'cliente_id' => $clienteIds[8] ?? null, 'producto' => 'Kettlebell 12kg', 'categoria' => 'Equipamiento', 'qty' => 1, 'estado' => 'Pagado', 'total' => 135000, 'img' => 'https://images.unsplash.com/photo-1599058917212-d750089bc07e?w=600&q=80'],
            ['numero_orden' => 'ORD-1033', 'cliente' => 'Paula Vargas', 'cliente_id' => $clienteIds[9] ?? null, 'producto' => 'Botella térmica 750ml', 'categoria' => 'Hidratación', 'qty' => 2, 'estado' => 'Enviado', 'total' => 90000, 'img' => 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=600&q=80'],
            ['numero_orden' => 'ORD-1032', 'cliente' => 'Santiago Gil', 'cliente_id' => $clienteIds[10] ?? null, 'producto' => 'Pre-workout FigueFit', 'categoria' => 'Suplementos', 'qty' => 1, 'estado' => 'Preparando', 'total' => 89000, 'img' => 'https://images.unsplash.com/photo-1594381898411-846e7d193883?w=600&q=80'],
            ['numero_orden' => 'ORD-1031', 'cliente' => 'Camila Hoyos', 'cliente_id' => $clienteIds[11] ?? null, 'producto' => 'Mat yoga premium', 'categoria' => 'Equipamiento', 'qty' => 1, 'estado' => 'Entregado', 'total' => 89000, 'img' => 'https://images.unsplash.com/photo-1601925260368-ae2f83cf8b7f?w=600&q=80'],
        ];
        $pagosDemo = [
            'Lun · Valentina Gómez · plan virtual',
            'Mar · Andrés Pérez · proteína whey',
            'Mié · Sofía Castro · plan presencial',
            'Jue · Julián Rojas · TRX + shaker',
            'Vie · Mariana López · renovación plan',
            'Sáb · Felipe Nieto · kit bienvenida',
            'Dom · Diana Suárez · plan institucional',
        ];
        $eventosDemo = [
            ['nombre' => 'Open day FigueFit', 'fecha' => now()->addDays(2)->format('d/m')],
            ['nombre' => 'Reto 21 días · Kickoff', 'fecha' => now()->addDays(5)->format('d/m')],
            ['nombre' => 'Clase VIP movilidad', 'fecha' => now()->addDays(8)->format('d/m')],
            ['nombre' => 'Workshop nutrición', 'fecha' => now()->addDays(12)->format('d/m')],
        ];
    } else {
        $pagosDemo = [];
        $weekBars = [];
        foreach ($horarios->take(7) as $horario) {
            $max = max(1, (int) $horario->cupo_maximo);
            $weekBars[] = min(100, (int) round(((int) $horario->cupo_ocupado / $max) * 100));
        }
        if ($weekBars === []) {
            foreach ($sparkClientes as $v) {
                $weekBars[] = max(12, (int) round(((float) $v / max(1, max($sparkClientes ?: [1]))) * 100));
            }
        }
        $ordenesDemo = null;
        $eventosDemo = null;
    }

    $mix = max(1, $virtuales + $presenciales);
    $pctVirtual = (int) round(($virtuales / $mix) * 100);
    $pctPresencial = 100 - $pctVirtual;
    $mixVentas = max(1, $planesN + $tiendaN);
    $pctPlanes = (int) round(($planesN / $mixVentas) * 100);
    $pctTienda = 100 - $pctPlanes;
    $ingresoLabel = $ingreso >= 1000 ? number_format($ingreso / 1000, 0, ',', '.').'k' : number_format($ingreso, 0, ',', '.');
    $estadoOcup = $pctCupo >= 80 ? 'Alta' : ($pctCupo >= 45 ? 'Óptima' : 'Holgada');
    $estadoSol = $solicitudesPendientes > 0 ? 'Revisar' : 'Al día';
    $estadoAct = $activos > 0 ? 'Estable' : 'Sin base';
@endphp

@section('content')
    <div class="ad-dash" data-dash-live="1">
        <div class="ad-k-row ad-stagger">
            <section class="ad-dash-hero ad-wx">
                <div>
                    <p class="ad-dash-kicker">{{ $saludo }}, admin</p>
                    <h2>Operación StayFit</h2>
                    <p>Clientes, sala y cobros del día. Lo que hay que mover ahora mismo.</p>
                    <div class="ad-mini-stats">
                        <div class="ad-mini">
                            <span class="ad-mini-ico is-pink">@include('partials.admin-icon', ['icon' => 'fit-users'])</span>
                            <div>
                                <em>Activos</em>
                                <strong data-count="{{ $activos }}">{{ $activos }}</strong>
                                <small class="is-ok">{{ $estadoAct }}</small>
                            </div>
                        </div>
                        <div class="ad-mini">
                            <span class="ad-mini-ico is-cyan">@include('partials.admin-icon', ['icon' => 'fit-inbox'])</span>
                            <div>
                                <em>Solicitudes</em>
                                <strong data-count="{{ $solicitudesPendientes }}">{{ $solicitudesPendientes }}</strong>
                                <small class="{{ $solicitudesPendientes ? 'is-warn' : 'is-ok' }}">{{ $estadoSol }}</small>
                            </div>
                        </div>
                        <div class="ad-mini">
                            <span class="ad-mini-ico is-mix">@include('partials.admin-icon', ['icon' => 'fit-dumbbell'])</span>
                            <div>
                                <em>Ocupación</em>
                                <strong data-count="{{ $pctCupo }}" data-suffix="%">{{ $pctCupo }}%</strong>
                                <small class="is-ok">{{ $estadoOcup }}</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="ad-dash-figure">
                    <img src="{{ asset('images/welcome/registro.jpg') }}" alt="Entrenamiento StayFit">
                </div>
            </section>

            <article class="ad-wx">
                <header class="ad-wx-head">
                    <h3>Pulso de sala</h3>
                    <span class="ad-wx-chip is-live">Live</span>
                </header>
                @include('partials.admin-orbs', [
                    'gid' => 'sala',
                    'items' => [
                        ['pct' => $pctCupo, 'label' => 'Cupo', 'value' => $ocupados.'/'.$cupos, 'color' => '#ff2d78', 'tip' => 'Cupo '.$ocupados.'/'.$cupos.' · '.$pctCupo.'% sala'],
                        ['pct' => min(100, (int) round(($activos / 60) * 100)), 'label' => 'Activos', 'value' => $activos, 'color' => '#2ef5e6', 'tip' => $activos.' clientes activos hoy'],
                        ['pct' => min(100, $solicitudesPendientes * 14), 'label' => 'Cola', 'value' => $solicitudesPendientes, 'color' => '#a78bfa', 'tip' => $solicitudesPendientes.' en cola humana'],
                    ],
                ])
            </article>

            @php
                $mixPesoPlanes = max(1, $planesN);
                $mixPesoTienda = max(1, $tiendaN);
                $mixPesoPres = max(1, (int) round($presenciales * 0.9));
                $mixPesoTotal = $mixPesoPlanes + $mixPesoTienda + $mixPesoPres;
                $mixPctPlanes = (int) round(($mixPesoPlanes / $mixPesoTotal) * 100);
                $mixPctTienda = (int) round(($mixPesoTienda / $mixPesoTotal) * 100);
                $mixPctPres = max(0, 100 - $mixPctPlanes - $mixPctTienda);
            @endphp
            <article class="ad-wx" data-chart-tip-root>
                <header class="ad-wx-head">
                    <h3>Mix comercial</h3>
                    <span class="ad-wx-chip">Mes</span>
                </header>
                @include('partials.admin-donut', [
                    'gid' => 'dash-mix',
                    'size' => 150,
                    'label' => $ingresoLabel,
                    'sub' => 'COP mes',
                    'count' => (int) $ingreso,
                    'prefix' => '$',
                    'segments' => [
                        ['label' => 'Planes', 'pct' => $mixPctPlanes, 'color' => '#ff2d78', 'tip' => 'Planes '.$mixPctPlanes.'% · '.$planesN.' ventas'],
                        ['label' => 'Tienda', 'pct' => $mixPctTienda, 'color' => '#2ef5e6', 'tip' => 'Tienda '.$mixPctTienda.'% · '.$tiendaN.' ítems'],
                        ['label' => 'Presencial', 'pct' => $mixPctPres, 'color' => '#a78bfa', 'tip' => 'Presencial '.$mixPctPres.'% · '.$presenciales.' clientes'],
                    ],
                ])
                <ul class="ad-macro">
                    <li class="is-tip" data-tip="Planes {{ $mixPctPlanes }}% · {{ $planesN }} ventas" data-tip-color="#ff2d78"><i class="is-pink"></i><span>Planes</span><strong>{{ $mixPctPlanes }}%</strong><em>{{ $planesN }}</em></li>
                    <li class="is-tip" data-tip="Tienda {{ $mixPctTienda }}% · {{ $tiendaN }} ítems" data-tip-color="#2ef5e6"><i class="is-cyan"></i><span>Tienda</span><strong>{{ $mixPctTienda }}%</strong><em>{{ $tiendaN }}</em></li>
                    <li class="is-tip" data-tip="Presencial {{ $mixPctPres }}% · {{ $presenciales }} clientes" data-tip-color="#a78bfa"><i class="is-lilac"></i><span>Presencial</span><strong>{{ $mixPctPres }}%</strong><em>{{ $presenciales }}</em></li>
                </ul>
            </article>
        </div>

        <div class="ad-k-mid ad-stagger">
            <article class="ad-wx">
                <header class="ad-wx-head">
                    <h3>Cupo semanal</h3>
                    <span class="ad-wx-chip">{{ $estadoOcup }}</span>
                </header>
                <div class="ad-wx-hero-num">
                    <strong data-count="{{ $pctCupo }}" data-suffix="%">{{ $pctCupo }}%</strong>
                    <em>ocupación sala</em>
                </div>
                <div class="ad-week-bars" data-chart-tip-root>
                    @foreach ($dias as $i => $dia)
                        @php $h = max(12, (int) ($weekBars[$i] ?? 18)); @endphp
                        <div class="ad-week-col is-tip" data-tip="{{ $dia }} · {{ $h }}% ocupación sala" data-tip-color="#2ef5e6">
                            <div class="ad-week-track">
                                <i style="height: {{ $h }}%"></i>
                            </div>
                            <span>{{ $dia }}</span>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="ad-wx">
                <header class="ad-wx-head">
                    <h3>Ingresos 7 días</h3>
                    <span class="ad-wx-chip is-live">Live</span>
                </header>
                <div class="ad-wx-hero-num">
                    <strong data-count="{{ (int) $ingreso }}" data-prefix="$">{{ number_format($ingreso, 0, ',', '.') }}</strong>
                    <em>validados este mes</em>
                </div>
                @php
                    $tipsIngreso = [];
                    foreach ($dias as $i => $dia) {
                        $monto = number_format((float) ($sparkIngreso[$i] ?? 0), 0, ',', '.');
                        $quien = $pagosDemo[$i] ?? ($dia.' · pago validado');
                        $tipsIngreso[] = $quien.' · $'.$monto;
                    }
                @endphp
                <div class="ad-line-wrap" data-live-line data-vals="{{ implode(',', $sparkIngreso) }}">
                    @include('partials.admin-line', [
                        'vals' => $sparkIngreso,
                        'gid' => 'dash-ln',
                        'labels' => $dias,
                        'tips' => $tipsIngreso,
                    ])
                </div>
                <div class="ad-legend-row">
                    @foreach ($dias as $d)
                        <span>{{ $d }}</span>
                    @endforeach
                </div>
            </article>

            <article class="ad-wx">
                <header class="ad-wx-head">
                    <h3>Agenda próxima</h3>
                    <a href="{{ route('admin.eventos.index') }}">Ver</a>
                </header>
                <div class="ad-up-list">
                    @if ($eventosDemo)
                        @foreach ($eventosDemo as $evento)
                            <div class="ad-up-item">
                                <span class="ad-up-dot"></span>
                                <span>{{ $evento['nombre'] }}</span>
                                <em>{{ $evento['fecha'] }}</em>
                            </div>
                        @endforeach
                    @else
                        @forelse ($proximosEventos as $evento)
                            <div class="ad-up-item">
                                <span class="ad-up-dot"></span>
                                <span>{{ $evento->nombre }}</span>
                                <em>{{ optional($evento->fecha_inicio)->format('d/m') }}</em>
                            </div>
                        @empty
                            <p class="ad-empty">No hay eventos en puerta.</p>
                        @endforelse
                    @endif
                </div>
            </article>
        </div>

        <div class="ad-k-bot ad-stagger">
            <article class="ad-wx ad-wx-wide">
                <header class="ad-wx-head">
                    <h3>Cobro del mes</h3>
                    <span class="ad-wx-chip">COP</span>
                </header>
                <div class="ad-steps">
                    <div>
                        <strong class="ad-mega" data-count="{{ (int) $ingreso }}" data-prefix="$">{{ number_format($ingreso, 0, ',', '.') }}</strong>
                        <span class="ad-user-mail">Planes {{ $planesN }} · Tienda {{ $tiendaN }}</span>
                    </div>
                    @include('partials.admin-spark', ['vals' => $sparkPlanes ?: $sparkClientes, 'tone' => 'mix'])
                </div>
                <div class="ad-steps-bar"><i style="--w: {{ min(100, max(18, (int) round(($ingreso / 15000000) * 100))) }}%"></i></div>
            </article>

            <a class="ad-banner" href="{{ route('admin.solicitudes.index') }}">
                <span class="ad-banner-media" aria-hidden="true">
                    <img src="{{ asset('images/admin/banner-coach-card.png') }}?v={{ @filemtime(public_path('images/admin/banner-coach-card.png')) ?: time() }}" alt="">
                </span>
                <h3>Más fuerte cada día.<br>Mejor cada cliente.</h3>
                <p>{{ $solicitudesPendientes }} solicitudes y {{ $propuestasPendientes }} propuestas de coach por revisar.</p>
            </a>
        </div>

        @php
            $listaOrdenes = collect($ordenesDemo ?? []);
            if ($listaOrdenes->isEmpty()) {
                $listaOrdenes = collect($ordenes ?? [])->map(function ($orden) {
                    $detalle = $orden->detalles?->first();
                    $prod = $detalle?->producto;
                    return [
                        'numero_orden' => $orden->numero_orden,
                        'cliente' => trim(($orden->cliente?->usuario?->nombres ?? '').' '.($orden->cliente?->usuario?->apellidos ?? '')) ?: 'Cliente',
                        'cliente_id' => $orden->cliente?->id_cliente,
                        'producto' => $prod?->nombre ?? $orden->numero_orden,
                        'categoria' => 'Pedido',
                        'qty' => (int) ($detalle?->cantidad ?? 1),
                        'estado' => $orden->estado_orden ?? 'Activo',
                        'total' => (float) $orden->total,
                        'img' => $prod?->imagen_url,
                    ];
                });
            }
            $paginasOrdenes = $listaOrdenes->chunk(6)->values();
            $totalPaginas = max(1, $paginasOrdenes->count());
        @endphp
        <article class="ad-wx" data-order-pager>
            <header class="ad-wx-head">
                <h3>Pedidos de tienda</h3>
                <a href="{{ route('admin.tienda.index') }}">Abrir tienda</a>
            </header>
            @if ($listaOrdenes->isEmpty())
                <p class="ad-empty">Sin pedidos.</p>
            @else
                <div class="ad-order-pager">
                    <span class="ad-order-pager-meta" data-order-meta>1 / {{ $totalPaginas }} · {{ $listaOrdenes->count() }} pedidos</span>
                    <div class="ad-order-pager-actions">
                        <button type="button" class="ad-order-nav" data-order-prev aria-label="Anterior" disabled>
                            @include('partials.admin-icon', ['icon' => 'chevron-left'])
                        </button>
                        <button type="button" class="ad-order-nav" data-order-next aria-label="Siguiente" @disabled($totalPaginas < 2)>
                            @include('partials.admin-icon', ['icon' => 'chevron-right'])
                        </button>
                    </div>
                </div>
                <div class="ad-order-pages">
                    @foreach ($paginasOrdenes as $pageIndex => $page)
                        <div class="ad-order-grid {{ $pageIndex === 0 ? 'is-active' : '' }}" data-order-page="{{ $pageIndex }}">
                            @foreach ($page as $orden)
                                @php
                                    $href = !empty($orden['cliente_id'])
                                        ? route('admin.clientes.show', $orden['cliente_id'])
                                        : route('admin.clientes.index');
                                @endphp
                                <a class="ad-order-card" href="{{ $href }}">
                                    <div class="ad-order-media">
                                        @if (!empty($orden['img']))
                                            <img src="{{ $orden['img'] }}" alt="{{ $orden['producto'] }}" loading="lazy">
                                        @else
                                            <span class="ad-order-fallback">@include('partials.admin-icon', ['icon' => 'tienda'])</span>
                                        @endif
                                    </div>
                                    <div class="ad-order-body">
                                        <div class="ad-order-top">
                                            <span>{{ $orden['categoria'] }}</span>
                                            <small>{{ $orden['estado'] }}</small>
                                        </div>
                                        <h4>{{ $orden['cliente'] }}</h4>
                                        <strong class="ad-order-price">${{ number_format((float) $orden['total'], 0, ',', '.') }}</strong>
                                        <p>{{ $orden['producto'] }}</p>
                                        <div class="ad-order-meta">
                                            <em>{{ $orden['numero_orden'] }}</em>
                                            <span>x{{ $orden['qty'] }}</span>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @endif
        </article>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    var root = document.querySelector('[data-dash-live]');
    if (!root) return;

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
        root.querySelectorAll('.is-hot').forEach(function (n) { n.classList.remove('is-hot'); });
    }

    root.addEventListener('pointerover', function (e) {
        var el = e.target.closest('.is-tip');
        if (!el || !root.contains(el)) return;
        showTip(el, e);
    });
    root.addEventListener('pointermove', function (e) {
        var el = e.target.closest('.is-tip');
        if (!el || tip.hidden) return;
        showTip(el, e);
    });
    root.addEventListener('pointerout', function (e) {
        var el = e.target.closest('.is-tip');
        if (!el) return;
        var to = e.relatedTarget && e.relatedTarget.closest ? e.relatedTarget.closest('.is-tip') : null;
        if (to === el) return;
        hideTip(el);
    });

    function rebuildLine(wrap, vals) {
        var max = Math.max.apply(null, vals.concat([1]));
        var n = Math.max(1, vals.length - 1);
        var pts = [];
        var area = ['0,100'];
        var dots = [];
        vals.forEach(function (v, i) {
            var x = ((i / n) * 100);
            var y = (100 - ((v / max) * 86) - 6);
            pts.push(x.toFixed(2) + ',' + y.toFixed(2));
            area.push(x.toFixed(2) + ',' + y.toFixed(2));
            dots.push({ x: x, y: y });
        });
        area.push('100,100');
        var poly = wrap.querySelector('.ad-line-stroke');
        var fill = wrap.querySelector('.ad-line-fill');
        if (poly) poly.setAttribute('points', pts.join(' '));
        if (fill) fill.setAttribute('points', area.join(' '));
        wrap.querySelectorAll('.ad-line-dot').forEach(function (dot, i) {
            if (!dots[i]) return;
            dot.setAttribute('cx', dots[i].x.toFixed(2));
            dot.setAttribute('cy', dots[i].y.toFixed(2));
        });
    }

    // No live SVG pulsing: constant polyline/orb updates blank the compositor in Brave/Wasmer.

    var pager = root.querySelector('[data-order-pager]');
    if (pager) {
        var pages = Array.prototype.slice.call(pager.querySelectorAll('[data-order-page]'));
        var prev = pager.querySelector('[data-order-prev]');
        var next = pager.querySelector('[data-order-next]');
        var meta = pager.querySelector('[data-order-meta]');
        var idx = 0;
        function renderPager() {
            pages.forEach(function (page, i) {
                page.classList.toggle('is-active', i === idx);
            });
            if (prev) prev.disabled = idx <= 0;
            if (next) next.disabled = idx >= pages.length - 1;
            if (meta) {
                var total = pages.length;
                var count = pages.reduce(function (n, page) { return n + page.querySelectorAll('.ad-order-card').length; }, 0);
                meta.textContent = (idx + 1) + ' / ' + total + ' · ' + count + ' pedidos';
            }
        }
        if (prev) prev.addEventListener('click', function () {
            if (idx <= 0) return;
            idx -= 1;
            renderPager();
        });
        if (next) next.addEventListener('click', function () {
            if (idx >= pages.length - 1) return;
            idx += 1;
            renderPager();
        });
        renderPager();
    }
})();
</script>
@endpush
