@extends('layouts.admin')

@section('title', 'Estadísticas')
@section('subtitle', 'Dashboard analítico · '.(is_array($dashboard) ? ($dashboard['periodo'] ?? '') : ''))

@section('actions')
    <a class="ad-btn ad-btn-ghost" href="{{ route('admin.reportes.index') }}">Ir a reportes</a>
@endsection

@php
    $k = $dashboard['kpis'] ?? [];
    $s = $dashboard['series'] ?? [];
    $d = $dashboard['distribuciones'] ?? [];
    $t = $dashboard['tablas'] ?? [];
    $modalidad = collect($d['modalidad'] ?? []);
    $virtuales = (int) ($modalidad->get('VIRTUAL') ?? $k['clientes_virtuales'] ?? 0);
    $presenciales = (int) ($modalidad->get('PRESENCIAL') ?? $k['clientes_presenciales'] ?? 0);
    $mix = max(1, $virtuales + $presenciales);
    $pctPresencial = (int) round(($presenciales / $mix) * 100);
    $wa = $d['whatsapp'] ?? ['aceptadas' => 0, 'pendientes' => 0, 'cerradas' => 0];
    $solicitudesEstado = collect($d['solicitudes_estado'] ?? []);
    $pasarelas = collect($d['pasarelas'] ?? []);
    $solLabels = $solicitudesEstado->keys()->values();
    $solValues = $solicitudesEstado->values();
    $pasLabels = $pasarelas->pluck('pasarela');
    $pasValues = $pasarelas->pluck('total');
@endphp

@section('content')
    <form class="ad-toolbar ad-glass-card" method="GET" action="{{ route('admin.estadisticas.index') }}" style="margin-bottom:16px">
        <label><span>Fecha inicio</span><input type="date" name="fecha_inicio" value="{{ $filtro->desde->toDateString() }}"></label>
        <label><span>Fecha fin</span><input type="date" name="fecha_fin" value="{{ $filtro->hasta->toDateString() }}"></label>
        <label><span>Modalidad</span>
            <select name="modalidad">
                <option value="">Todas</option>
                <option value="VIRTUAL" @selected($filtro->modalidad === 'VIRTUAL')>Virtual</option>
                <option value="PRESENCIAL" @selected($filtro->modalidad === 'PRESENCIAL')>Presencial</option>
            </select>
        </label>
        <label><span>Pasarela</span>
            <input type="text" name="pasarela" value="{{ $filtro->pasarela }}" placeholder="WOMPI / NINGUNA">
        </label>
        <div class="ad-toolbar__actions">
            <button class="ad-btn" type="submit">Aplicar</button>
            <a class="ad-btn ad-btn-ghost" href="{{ route('admin.estadisticas.index') }}">Limpiar</a>
        </div>
    </form>

    <div class="ad-tabs" data-ad-tabs>
        <button type="button" class="is-active" data-tab="resumen">Resumen</button>
        <button type="button" data-tab="comercial">Comercial</button>
        <button type="button" data-tab="comunicacion">Comunicación</button>
        <button type="button" data-tab="detalle">Detalle</button>
    </div>

    <div class="ad-tab-panels">
        <div data-panel="resumen">
            <div class="ad-status-rail" style="margin-bottom:16px">
                <x-admin.stat label="Usuarios activos" :value="$k['usuarios_activos']" tone="cyan" hint="Cuentas en el periodo" />
                <x-admin.stat label="Clientes activos" :value="$k['clientes_activos']" tone="pink" hint="Con plan vigente" />
                <x-admin.stat label="Ingresos periodo" :value="'$'.number_format($k['ingresos_periodo'], 0, ',', '.')" tone="cyan" hint="Pagos validados" />
                <x-admin.stat label="Mensajes periodo" :value="$k['mensajes_periodo']" tone="pink" hint="Comunidad" />
            </div>

            <div class="ad-grid-2">
                <x-admin.panel title="Tendencia de ingresos">
                    <canvas id="chartIngresos" height="120"></canvas>
                </x-admin.panel>
                <x-admin.panel title="Modalidad clientes">
                    @include('partials.admin-ring', [
                        'pct' => $pctPresencial,
                        'label' => (string) ($virtuales + $presenciales),
                        'sub' => 'clientes',
                        'gid' => 'est-mix',
                        'size' => 160,
                    ])
                    <div class="ad-legend" style="justify-content:center;margin-top:12px">
                        <span><i style="background:var(--cyan)"></i> Virtual {{ $virtuales }}</span>
                        <span><i style="background:var(--pink)"></i> Presencial {{ $presenciales }}</span>
                    </div>
                </x-admin.panel>
            </div>
        </div>

        <div data-panel="comercial" hidden>
            <div class="ad-status-rail" style="margin-bottom:16px">
                <x-admin.stat label="Planes vendidos" :value="$k['planes_vendidos']" />
                <x-admin.stat label="Solicitudes pend." :value="$k['solicitudes_pendientes']" tone="pink" />
                <x-admin.stat label="Cupones usados" :value="$k['cupones_usados']" tone="cyan" />
                <x-admin.stat label="Pedidos tienda" :value="$k['pedidos_tienda']" />
            </div>
            <div class="ad-grid-2">
                <x-admin.panel title="Solicitudes por estado">
                    <canvas id="chartSolicitudes" height="140"></canvas>
                </x-admin.panel>
                <x-admin.panel title="Ingresos por pasarela">
                    <canvas id="chartPasarelas" height="140"></canvas>
                    @if ($pasarelas->isEmpty())
                        <p class="ad-empty">Sin ingresos validados en el periodo.</p>
                    @endif
                </x-admin.panel>
            </div>
            <div class="ad-grid-2" style="margin-top:16px">
                <x-admin.panel title="Cupos por plan">
                    @forelse (collect($t['cupos'] ?? []) as $plan)
                        @php
                            $max = max(1, (int) ($plan->cupos_totales ?? 0));
                            $ocup = (int) ($plan->cupos_ocupados ?? 0);
                            $pct = min(100, round(($ocup / $max) * 100));
                        @endphp
                        <div class="ad-chart-row">
                            <div>
                                <strong>{{ $plan->nombre }}</strong>
                                <em>{{ $ocup }}/{{ $plan->cupos_totales ?? 0 }}</em>
                            </div>
                            <div class="ad-bar"><span style="width: {{ $pct }}%"></span></div>
                        </div>
                    @empty
                        <p class="ad-empty">Sin planes.</p>
                    @endforelse
                </x-admin.panel>
                <x-admin.panel title="Top cupones">
                    @forelse (collect($t['cupones'] ?? []) as $uso)
                        <div class="ad-list-row">
                            <div>
                                <strong>{{ $uso->descuento?->codigo ?: ('#'.$uso->id_descuento) }}</strong>
                                <em>{{ $uso->descuento?->tipo_descuento }}</em>
                            </div>
                            <span>{{ $uso->usos }} usos</span>
                        </div>
                    @empty
                        <p class="ad-empty">Sin usos de cupón en el periodo.</p>
                    @endforelse
                </x-admin.panel>
            </div>
        </div>

        <div data-panel="comunicacion" hidden>
            <div class="ad-status-rail" style="margin-bottom:16px">
                <x-admin.stat label="Chats activos" :value="$k['chats_activos']" tone="cyan" />
                <x-admin.stat label="Mensajes" :value="$k['mensajes_periodo']" tone="pink" />
                <x-admin.stat label="WA pendientes" :value="$k['invitaciones_wa_pendientes']" />
                <x-admin.stat label="Notificaciones" :value="$k['notificaciones_periodo']" />
            </div>
            <div class="ad-grid-2">
                <x-admin.panel title="Mensajes (comunidad)">
                    <canvas id="chartMensajes" height="120"></canvas>
                </x-admin.panel>
                <x-admin.panel title="Invitaciones WhatsApp">
                    <canvas id="chartWa" height="140"></canvas>
                    <div class="ad-legend" style="margin-top:10px;justify-content:center">
                        <span>Aceptadas {{ $wa['aceptadas'] }}</span>
                        <span>Pendientes {{ $wa['pendientes'] }}</span>
                        <span>Cerradas {{ $wa['cerradas'] }}</span>
                    </div>
                </x-admin.panel>
            </div>
            <x-admin.panel title="Chats activos" style="margin-top:16px">
                @forelse (collect($t['chats'] ?? []) as $chat)
                    <div class="ad-list-row">
                        <div>
                            <strong>{{ $chat->nombre ?: ('Chat #'.$chat->id_chat) }}</strong>
                            <em>{{ $chat->tipo }}</em>
                        </div>
                        <span>{{ $chat->mensajes_count }} msg</span>
                    </div>
                @empty
                    <p class="ad-empty">Sin chats activos.</p>
                @endforelse
            </x-admin.panel>
        </div>

        <div data-panel="detalle" hidden>
            <div class="ad-grid-2">
                <x-admin.panel title="Pagos por estado" flush>
                    <div class="ad-table-wrap">
                        <table class="ad-table ad-users-table ad-table-compact">
                            <thead><tr><th>Estado</th><th>Cantidad</th><th>Monto</th></tr></thead>
                            <tbody>
                                @forelse (collect($d['pagos_estado'] ?? []) as $row)
                                    <tr>
                                        <td>{{ $row->estado_pago }}</td>
                                        <td>{{ $row->total }}</td>
                                        <td>${{ number_format((float) $row->monto, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="ad-empty">Sin pagos en el periodo.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-admin.panel>
                <x-admin.panel title="Propuestas por estado" flush>
                    <div class="ad-table-wrap">
                        <table class="ad-table ad-users-table ad-table-compact">
                            <thead><tr><th>Estado</th><th>Cantidad</th></tr></thead>
                            <tbody>
                                @forelse (collect($d['propuestas_estado'] ?? []) as $estado => $total)
                                    <tr><td>{{ $estado }}</td><td>{{ $total }}</td></tr>
                                @empty
                                    <tr><td colspan="2" class="ad-empty">Sin propuestas en el periodo.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-admin.panel>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    var tabs = document.querySelector('[data-ad-tabs]');
    if (tabs) {
        tabs.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-tab]');
            if (!btn) return;
            var id = btn.getAttribute('data-tab');
            tabs.querySelectorAll('[data-tab]').forEach(function (b) { b.classList.toggle('is-active', b === btn); });
            document.querySelectorAll('.ad-tab-panels > [data-panel]').forEach(function (p) {
                p.toggleAttribute('hidden', p.getAttribute('data-panel') !== id);
            });
        });
    }

    if (typeof Chart === 'undefined') return;
    var pink = '#ff2d78';
    var cyan = '#2ef5e6';
    var grid = 'rgba(255,255,255,.08)';
    Chart.defaults.animation.duration = 1400;
    Chart.defaults.animation.easing = 'easeOutQuart';
    Chart.defaults.font.family = 'Montserrat, system-ui, sans-serif';

    function lineChart(id, labels, values, color) {
        var el = document.getElementById(id);
        if (!el) return;
        new Chart(el, {
            type: 'line',
            data: { labels: labels, datasets: [{ data: values, borderColor: color, backgroundColor: color + '33', fill: true, tension: 0.45, pointRadius: 3, pointHoverRadius: 6, borderWidth: 3 }] },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: '#9aa' }, grid: { color: grid } },
                    y: { ticks: { color: '#9aa' }, grid: { color: grid }, beginAtZero: true }
                }
            }
        });
    }
    function barChart(id, labels, values, color) {
        var el = document.getElementById(id);
        if (!el) return;
        new Chart(el, {
            type: 'bar',
            data: { labels: labels, datasets: [{ data: values, backgroundColor: color, borderRadius: 8, borderSkipped: false }] },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: '#9aa' }, grid: { display: false } },
                    y: { ticks: { color: '#9aa' }, grid: { color: grid }, beginAtZero: true }
                }
            }
        });
    }
    function doughnut(id, labels, values, colors) {
        var el = document.getElementById(id);
        if (!el) return;
        new Chart(el, {
            type: 'doughnut',
            data: { labels: labels, datasets: [{ data: values, backgroundColor: colors, borderWidth: 0, hoverOffset: 8 }] },
            options: {
                cutout: '68%',
                plugins: { legend: { labels: { color: '#ccc', padding: 14 } } }
            }
        });
    }

    lineChart('chartIngresos', @json($s['ingresos']['labels']), @json($s['ingresos']['values']), cyan);
    lineChart('chartMensajes', @json($s['mensajes']['labels']), @json($s['mensajes']['values']), pink);
    barChart('chartSolicitudes', @json($solLabels), @json($solValues), pink);
    doughnut('chartPasarelas', @json($pasLabels), @json($pasValues), [pink, cyan, '#a78bfa', '#fbbf24', '#34d399']);
    doughnut('chartWa', ['Aceptadas','Pendientes','Cerradas'], [@json($wa['aceptadas']), @json($wa['pendientes']), @json($wa['cerradas'])], [cyan, pink, '#888']);
})();
</script>
@endpush
