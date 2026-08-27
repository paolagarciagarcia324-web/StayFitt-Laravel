@extends('layouts.cliente')

@section('title', 'Mis pagos')

@section('content')
    @php
        $estadoBadge = fn (string $estado) => match (true) {
            in_array(strtoupper($estado), ['VALIDADO', 'APROBADO', 'PAGADO'], true) => 'is-ok',
            strtoupper($estado) === 'PENDIENTE' => 'is-cyan',
            in_array(strtoupper($estado), ['RECHAZADO', 'FALLIDO', 'CANCELADO'], true) => 'is-pink',
            default => '',
        };
    @endphp

    <x-cliente.page-head
        kicker="Finanzas"
        title="Mis pagos"
        subtitle="Historial de cobros, estados y resumen de tu actividad de pago."
    />

    <div class="cv-metrics">
        <x-cliente.metric label="Pendientes" :value="$resumen['pendientes']" hint="Por confirmar" />
        <x-cliente.metric label="Validados" :value="$resumen['validados']" hint="Pagos confirmados" />
        <x-cliente.metric
            label="Total validado"
            :value="'$'.number_format($resumen['total'], 0, ',', '.')"
            hint="Acumulado histórico"
        />
    </div>

    <x-cliente.card title="Historial de pagos" subtitle="{{ $pagos->total() }} registro(s)">
        @if ($pagos->isEmpty())
            <div class="cv-empty">Aún no hay pagos registrados en tu cuenta.</div>
        @else
            <div class="cv-table-wrap" style="margin:-6px -18px 0">
                <table class="cv-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Plan / tipo</th>
                            <th>Monto</th>
                            <th>Método</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pagos as $pago)
                            <tr>
                                <td>{{ optional($pago->creado_en)->format('d/m/Y H:i') ?? '—' }}</td>
                                <td>{{ $pago->plan?->nombre ?? ($pago->tipo_pago ?: 'Pago') }}</td>
                                <td><strong>${{ number_format((float) $pago->monto, 0, ',', '.') }}</strong></td>
                                <td>{{ $pago->metodo_pago ?: $pago->metodo ?: '—' }}</td>
                                <td>
                                    <span class="cv-badge {{ $estadoBadge((string) $pago->estado_pago) }}">
                                        {{ $pago->estado_pago }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($pagos->hasPages())
                <div style="margin-top:16px">{{ $pagos->links() }}</div>
            @endif
        @endif
    </x-cliente.card>
@endsection
