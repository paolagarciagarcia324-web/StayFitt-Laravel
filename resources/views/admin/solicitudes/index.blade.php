@extends('layouts.admin')

@section('title', 'Solicitudes')
@section('subtitle', 'Validación de compras y pre-registros')

@section('content')
    <section class="ad-card">
        <h2>Pendientes</h2>
        @if ($pendientes->isEmpty())
            <p class="ad-empty">Sin solicitudes pendientes.</p>
        @else
            <table class="ad-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Cliente</th>
                        <th>Plan</th>
                        <th>Días</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pendientes as $s)
                        <tr>
                            <td>{{ $s->codigo_solicitud }}</td>
                            <td>{{ $s->nombre_solicitante }}<br><small style="color:var(--muted)">{{ $s->correo }}</small></td>
                            <td>{{ $s->plan_solicitado }} · ${{ number_format((float)$s->precio,0,',','.') }}</td>
                            <td>{{ $s->dias_en_espera }}</td>
                            <td><a class="ad-btn" href="{{ route('admin.solicitudes.show', $s->id_solicitud) }}">Validar</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>

    <section class="ad-card">
        <h2>Historial reciente</h2>
        <table class="ad-table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Plan</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($historial as $h)
                    <tr>
                        <td>{{ $h->codigo_solicitud }}</td>
                        <td>{{ $h->nombres }} {{ $h->apellidos }}</td>
                        <td>{{ $h->plan?->nombre }}</td>
                        <td>
                            <span class="ad-badge {{ $h->estado_solicitud === 'VALIDADA' ? 'ad-badge-ok' : 'ad-badge-bad' }}">
                                {{ $h->estado_solicitud }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="ad-empty">Sin historial.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
