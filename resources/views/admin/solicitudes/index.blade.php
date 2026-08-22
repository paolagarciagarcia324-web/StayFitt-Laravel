@extends('layouts.admin')

@section('title', 'Solicitudes')
@section('subtitle')
    <span class="ad-page-intro">
        <span class="ad-page-intro-lead">Novedades de pago que requieren validación del admin.</span>
        <span class="ad-page-intro-note">La activación normal es automática; aquí solo llegan incidencias a revisar.</span>
    </span>
@endsection

@section('content')
    <section class="ad-card ad-users">
        <div class="ad-users-bar">
            <h2 style="margin:0;font-size:15px">Novedades / en revisión</h2>
            <span class="ad-pill-days">{{ $pendientes->total() }} novedades</span>
        </div>
        <p class="ad-page-intro-note">
            El acceso del cliente se activa solo cuando la pasarela confirma el pago.
            Esta cola no es el flujo normal: solo aparecen casos con novedad (fraude, pago no reflejado, disputa u otra incidencia) que el admin debe validar.
        </p>

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
                        <td>@include('partials.admin-solicitud-codigo', ['codigo' => $s->codigo_solicitud])</td>
                        <td>
                            <strong>{{ $s->nombres }} {{ $s->apellidos }}</strong>
                            <em class="ad-user-mail">{{ $s->correo }}</em>
                        </td>
                        <td>{{ $s->plan?->nombre ?: 'Servicio presencial' }}</td>
                        <td>${{ number_format((float) ($s->total ?? $s->pago?->monto), 0, ',', '.') }}</td>
                        <td>{{ $s->creado_en ? \Illuminate\Support\Carbon::parse($s->creado_en)->format('d/m/Y H:i') : '—' }}</td>
                        <td><a class="ad-btn" href="{{ route('admin.solicitudes.show', $s->id_solicitud) }}">Revisar</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="ad-empty">Sin novedades por revisar.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="ad-pagination">{{ $pendientes->links() }}</div>
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
                        <td>@include('partials.admin-solicitud-codigo', ['codigo' => $h->codigo_solicitud])</td>
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
        <div class="ad-pagination">{{ $historial->links() }}</div>
    </section>
@endsection
