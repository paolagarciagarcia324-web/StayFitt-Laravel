@extends('layouts.admin')

@section('title', 'Pagos')
@section('subtitle', 'Registro de pagos')

@section('content')
    <section class="ad-card ad-users">
        <div class="ad-users-bar">
            <form class="ad-users-search" method="GET" action="{{ route('admin.pagos.index') }}">
                <span>⌕</span>
                <input type="search" name="q" value="{{ $q }}" placeholder="Referencia o correo">
            </form>
        </div>

        <table class="ad-table ad-users-table">
            <thead>
                <tr>
                    <th>Referencia</th>
                    <th>Solicitud</th>
                    <th>Correo</th>
                    <th>Monto</th>
                    <th>Método</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pagos as $p)
                    @php
                        $metodo = $p->metodo_pago ?: $p->metodo ?: '—';
                        $esPasarela = filled($p->pasarela) || str_contains(strtoupper((string) $metodo), 'PASARELA');
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $p->referencia ?: $p->referencia_pago ?: '—' }}</strong>
                            <em class="ad-user-mail">#{{ $p->id_pago }}</em>
                        </td>
                        <td>
                            @if ($p->id_solicitud)
                                <a class="ad-link" href="{{ route('admin.solicitudes.show', $p->id_solicitud) }}">{{ $p->solicitud?->codigo_solicitud ?: '#'.$p->id_solicitud }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $p->solicitud?->correo ?: '—' }}</td>
                        <td>${{ number_format((float) $p->monto, 0, ',', '.') }} {{ $p->moneda }}</td>
                        <td>
                            <span class="ad-badge {{ $esPasarela ? 'ad-badge-ok' : 'ad-badge-pendiente' }}">{{ $esPasarela ? 'Pasarela' : 'Comprobante' }}</span>
                            <em class="ad-user-mail">{{ $metodo }}</em>
                        </td>
                        <td>
                            <span class="ad-badge {{ $p->estado_pago === 'VALIDADO' ? 'ad-badge-ok' : ($p->estado_pago === 'PENDIENTE' ? 'ad-badge-pendiente' : 'ad-badge-bad') }}">
                                {{ $p->estado_pago }}
                            </span>
                        </td>
                        <td>{{ optional($p->creado_en)->format('d/m/Y H:i') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="ad-empty">No hay pagos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="ad-pagination">{{ $pagos->links() }}</div>
    </section>
@endsection
