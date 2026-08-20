@extends('layouts.admin')

@section('title', 'Solicitudes')
@section('subtitle', 'Validación de compras')

@section('content')
    <section class="ad-card ad-users">
        <div class="ad-users-bar">
            <h2 style="margin:0;font-size:15px">Pendientes de validación</h2>
            <span class="ad-pill-days">{{ $pendientes->count() }} pendientes</span>
        </div>

        <table class="ad-table ad-users-table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Cliente</th>
                    <th>Plan</th>
                    <th>Monto</th>
                    <th>Alta</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pendientes as $s)
                    <tr>
                        <td><strong>{{ $s->codigo_solicitud }}</strong></td>
                        <td>
                            <strong>{{ $s->nombres }} {{ $s->apellidos }}</strong>
                            <em class="ad-user-mail">{{ $s->correo }}</em>
                        </td>
                        <td>{{ $s->plan?->nombre ?: 'Servicio presencial' }}</td>
                        <td>${{ number_format((float) ($s->total ?? $s->pago?->monto), 0, ',', '.') }}</td>
                        <td>{{ $s->creado_en ? \Illuminate\Support\Carbon::parse($s->creado_en)->format('d/m/Y H:i') : '—' }}</td>
                        <td><a class="ad-btn" href="{{ route('admin.solicitudes.show', $s->id_solicitud) }}">Validar</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="ad-empty">Sin solicitudes pendientes.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <section class="ad-card ad-users" style="margin-top:16px">
        <div class="ad-users-bar">
            <h2 style="margin:0;font-size:15px">Historial reciente</h2>
        </div>
        <table class="ad-table ad-users-table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Plan</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($historial as $h)
                    <tr>
                        <td><strong>{{ $h->codigo_solicitud }}</strong></td>
                        <td>{{ $h->nombres }} {{ $h->apellidos }}</td>
                        <td>{{ $h->plan?->nombre ?: '—' }}</td>
                        <td>
                            <span class="ad-badge {{ $h->estado_solicitud === 'VALIDADA' ? 'ad-badge-ok' : 'ad-badge-bad' }}">
                                {{ $h->estado_solicitud }}
                            </span>
                        </td>
                        <td><a class="ad-btn ad-btn-ghost" href="{{ route('admin.solicitudes.show', $h->id_solicitud) }}">Ver</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="ad-empty">Sin historial.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
