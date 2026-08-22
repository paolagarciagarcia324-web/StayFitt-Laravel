@extends('layouts.admin')

@section('title', 'Pagos')
@section('subtitle')
    <span class="ad-page-intro">
        <span class="ad-page-intro-lead">Registro de pagos y vencimientos del club.</span>
        <span class="ad-page-intro-note">Consulta referencias, comprobantes y estados de cobro en un solo lugar.</span>
    </span>
@endsection

@section('content')
    <section class="ad-card ad-users">
        <div class="ad-users-bar">
            @include('partials.admin-search', [
                'action' => route('admin.pagos.index'),
                'value' => $q,
                'placeholder' => 'Referencia o correo',
            ])
        </div>

        <table class="ad-table ad-users-table">
            <thead>
                <tr>
                    <th>Referencia</th>
                    <th>Solicitud</th>
                    <th>Correo</th>
                    <th>Monto</th>
                    <th>Método</th>
                    <th>Comprobante</th>
                    <th>Estado</th>
                    <th>Fecha pago</th>
                    <th>Vence</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pagos as $p)
                    @php
                        $metodo = $p->metodo_pago ?: $p->metodo ?: '—';
                        $pas = strtoupper((string) ($p->pasarela ?: ''));
                        $esPasarela = ($pas !== '' && ! in_array($pas, ['NINGUNA', 'PENDIENTE'], true))
                            || str_contains(strtoupper((string) $metodo), 'PASARELA');
                        $comp = $p->comprobanteSrc();
                        $fechaPago = $p->fechaPagoEfectiva();
                        $fechaVence = $p->fechaVencimiento();
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $p->referencia ?: $p->referencia_pago ?: '—' }}</strong>
                            <em class="ad-user-mail">#{{ $p->id_pago }}</em>
                        </td>
                        <td>
                            @if ($p->id_solicitud)
                                <a class="ad-link" href="{{ route('admin.solicitudes.show', $p->id_solicitud) }}">
                                    {{ $p->solicitud?->codigo_solicitud ?: '#'.$p->id_solicitud }}
                                </a>
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
                            @if ($comp)
                                @include('partials.admin-media-thumb', ['src' => $comp, 'alt' => 'Comprobante', 'size' => 'sm', 'icon' => 'pagos'])
                            @else
                                <span class="ad-user-mail">Sin comprobante</span>
                            @endif
                        </td>
                        <td>
                            <span class="ad-badge {{ $p->estado_pago === 'VALIDADO' ? 'ad-badge-ok' : ($p->estado_pago === 'PENDIENTE' ? 'ad-badge-pendiente' : 'ad-badge-bad') }}">
                                {{ $p->estado_pago }}
                            </span>
                        </td>
                        <td>{{ $fechaPago ? $fechaPago->format('d/m/Y H:i') : '—' }}</td>
                        <td>{{ $fechaVence ? $fechaVence->format('d/m/Y') : '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="ad-empty">No hay pagos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="ad-pagination">{{ $pagos->links() }}</div>
    </section>
@endsection
