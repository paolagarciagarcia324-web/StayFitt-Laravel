@extends('layouts.admin')

@section('title', 'Pagos')
@section('subtitle', 'Registro de pagos del sistema')

@section('content')
    <section class="ad-card">
        <table class="ad-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Solicitud</th>
                    <th>Monto</th>
                    <th>Método</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pagos as $p)
                    <tr>
                        <td>#{{ $p->id_pago }}</td>
                        <td>{{ $p->solicitud?->codigo_solicitud ?: '—' }}</td>
                        <td>${{ number_format((float)$p->monto, 0, ',', '.') }} {{ $p->moneda }}</td>
                        <td>{{ $p->metodo_pago }}</td>
                        <td>
                            <span class="ad-badge {{ $p->estado_pago === 'VALIDADO' ? 'ad-badge-ok' : ($p->estado_pago === 'PENDIENTE' ? 'ad-badge-pendiente' : 'ad-badge-bad') }}">
                                {{ $p->estado_pago }}
                            </span>
                        </td>
                        <td>{{ optional($p->creado_en)->format('Y-m-d H:i') ?? $p->creado_en }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="ad-pagination">{{ $pagos->links() }}</div>
    </section>
@endsection
