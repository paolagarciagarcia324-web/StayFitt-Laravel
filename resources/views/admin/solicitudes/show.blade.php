@extends('layouts.admin')

@section('title', 'Revisar solicitud')
@section('subtitle', $solicitud->codigo_solicitud)

@section('content')
    <section class="ad-card">
        <h2>{{ $solicitud->nombres }} {{ $solicitud->apellidos }}</h2>
        <table class="ad-table">
            <tr><th>Correo</th><td>{{ $solicitud->correo }}</td></tr>
            <tr><th>Documento</th><td>{{ $solicitud->documento_identidad }}</td></tr>
            <tr><th>Teléfono</th><td>{{ $solicitud->telefono ?: '—' }}</td></tr>
            <tr><th>Modalidad</th><td>{{ $solicitud->modalidad }}</td></tr>
            <tr><th>Plan</th><td>{{ $solicitud->plan?->nombre }} · {{ $solicitud->plan?->precioFormateado() }}</td></tr>
            <tr><th>Estado</th><td><span class="ad-badge ad-badge-pendiente">{{ $solicitud->estado_solicitud }}</span></td></tr>
            <tr><th>Pago</th><td>{{ $solicitud->pago?->metodo_pago }} · Ref: {{ $solicitud->pago?->referencia_pago }} · {{ $solicitud->pago?->estado_pago }}</td></tr>
            <tr>
                <th>Comprobante</th>
                <td>
                    @if ($solicitud->pago?->comprobante_url)
                        <a class="ad-link" href="{{ $solicitud->pago->comprobante_url }}" target="_blank">Ver archivo</a>
                    @else
                        —
                    @endif
                </td>
            </tr>
        </table>

        @if ($solicitud->estado_solicitud === 'PENDIENTE')
            <form method="POST" action="{{ route('admin.solicitudes.aprobar', $solicitud->id_solicitud) }}" class="ad-form" style="margin-top:16px">
                @csrf
                <label>
                    <span>Observación (opcional)</span>
                    <textarea name="observacion_admin" rows="3"></textarea>
                </label>
                <div class="ad-actions">
                    <button class="ad-btn" type="submit">Aprobar y crear cliente</button>
                    <button class="ad-btn ad-btn-ghost" formaction="{{ route('admin.solicitudes.rechazar', $solicitud->id_solicitud) }}" formmethod="post" type="submit">Rechazar</button>
                </div>
            </form>
        @endif

        <p style="margin-top:16px"><a class="ad-link" href="{{ route('admin.solicitudes.index') }}">← Volver</a></p>
    </section>
@endsection
