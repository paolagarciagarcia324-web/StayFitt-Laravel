@extends('layouts.cliente')

@section('title', 'Mis solicitudes')

@section('content')
    @php
        $estadoBadge = fn (string $estado) => match (true) {
            in_array(strtoupper($estado), ['APROBADA', 'APROBADO', 'ACTIVA', 'COMPLETADA', 'PAGADA'], true) => 'is-ok',
            in_array(strtoupper($estado), ['PENDIENTE', 'EN_REVISION', 'EN PROCESO'], true) => 'is-cyan',
            in_array(strtoupper($estado), ['RECHAZADA', 'RECHAZADO', 'CANCELADA', 'CANCELADO'], true) => 'is-pink',
            default => '',
        };
    @endphp

    <x-cliente.page-head
        kicker="Comercial"
        title="Mis solicitudes"
        subtitle="Seguimiento de compras, renovaciones y solicitudes asociadas a tu cuenta."
    >
        <x-slot:actions>
            <a class="cv-btn cv-btn-ghost cv-btn-sm" href="{{ route('cliente.comercial.planes') }}">Ver planes</a>
            <a class="cv-btn cv-btn-sm" href="{{ route('cliente.comercial.cupones') }}">Cupones</a>
        </x-slot:actions>
    </x-cliente.page-head>

    <x-cliente.card title="Historial de solicitudes" subtitle="{{ $solicitudes->total() }} registro(s)">
        @if ($solicitudes->isEmpty())
            <div class="cv-empty">No hay solicitudes asociadas a tu correo o documento.</div>
        @else
            <div class="cv-table-wrap" style="margin:-6px -18px 0">
                <table class="cv-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Plan</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($solicitudes as $sol)
                            <tr>
                                <td><strong>{{ $sol->codigo_solicitud }}</strong></td>
                                <td>{{ $sol->plan?->nombre ?? '—' }}</td>
                                <td>${{ number_format((float) $sol->total, 0, ',', '.') }}</td>
                                <td>
                                    <span class="cv-badge {{ $estadoBadge((string) $sol->estado_solicitud) }}">
                                        {{ $sol->estado_solicitud }}
                                    </span>
                                </td>
                                <td>{{ optional($sol->creado_en)->format('d/m/Y') ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($solicitudes->hasPages())
                <div style="margin-top:16px">{{ $solicitudes->links() }}</div>
            @endif
        @endif
    </x-cliente.card>
@endsection
