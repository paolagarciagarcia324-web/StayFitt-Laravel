@extends('layouts.admin')

@section('title', 'Dashboard')
@section('subtitle', 'Métricas operativas de StayFit')

@section('content')
    <div class="ad-grid">
        <article class="ad-metric">
            <span>Clientes activos</span>
            <strong>{{ $metricas->total_clientes_activos ?? 0 }}</strong>
        </article>
        <article class="ad-metric">
            <span>Solicitudes pendientes</span>
            <strong>{{ $metricas->solicitudes_pendientes ?? 0 }}</strong>
        </article>
        <article class="ad-metric">
            <span>Pagos pendientes</span>
            <strong>{{ $metricas->pagos_pendientes ?? 0 }}</strong>
        </article>
        <article class="ad-metric">
            <span>Ingreso del mes</span>
            <strong>${{ number_format((float) ($metricas->ingreso_mes_actual ?? 0), 0, ',', '.') }}</strong>
        </article>
    </div>

    <div class="ad-grid" style="grid-template-columns: repeat(3, 1fr);">
        <article class="ad-metric">
            <span>Virtuales</span>
            <strong>{{ $metricas->clientes_virtuales ?? 0 }}</strong>
        </article>
        <article class="ad-metric">
            <span>Presenciales</span>
            <strong>{{ $metricas->clientes_presenciales ?? 0 }}</strong>
        </article>
        <article class="ad-metric">
            <span>Coaches activos</span>
            <strong>{{ $metricas->coaches_activos ?? 0 }}</strong>
        </article>
    </div>

    <section class="ad-card">
        <h2>Solicitudes por validar</h2>
        @if ($solicitudes->isEmpty())
            <p class="ad-empty">No hay solicitudes pendientes.</p>
        @else
            <table class="ad-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Solicitante</th>
                        <th>Plan</th>
                        <th>Pago</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($solicitudes as $s)
                        <tr>
                            <td>{{ $s->codigo_solicitud }}</td>
                            <td>
                                {{ $s->nombre_solicitante }}<br>
                                <small style="color:var(--muted)">{{ $s->correo }}</small>
                            </td>
                            <td>{{ $s->plan_solicitado }}</td>
                            <td>
                                ${{ number_format((float) $s->monto, 0, ',', '.') }}
                                <span class="ad-badge ad-badge-pendiente">{{ $s->estado_pago }}</span>
                            </td>
                            <td><a class="ad-btn ad-btn-ghost" href="{{ route('admin.solicitudes.show', $s->id_solicitud) }}">Revisar</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>
@endsection
