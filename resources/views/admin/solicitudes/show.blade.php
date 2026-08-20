@extends('layouts.admin')

@section('title', 'Revisar solicitud')
@section('subtitle', $solicitud->codigo_solicitud)

@section('content')
    <section class="ad-card ad-users">
        <div class="ad-users-bar">
            <div>
                <h2 style="margin:0;font-size:18px">{{ $solicitud->nombres }} {{ $solicitud->apellidos }}</h2>
                <em class="ad-user-mail">{{ $solicitud->correo }}</em>
            </div>
            <span class="ad-badge {{ $solicitud->estado_solicitud === 'PENDIENTE' ? 'ad-badge-pendiente' : ($solicitud->estado_solicitud === 'VALIDADA' ? 'ad-badge-ok' : 'ad-badge-bad') }}">
                {{ $solicitud->estado_solicitud }}
            </span>
        </div>

        <table class="ad-table ad-users-table">
            <tr><th>Documento</th><td>{{ $solicitud->documento_identidad ?: $solicitud->documento ?: '—' }}</td></tr>
            <tr><th>Teléfono</th><td>{{ $solicitud->telefono ?: '—' }}</td></tr>
            <tr><th>Modalidad</th><td>{{ $solicitud->modalidad }}</td></tr>
            <tr><th>Plan</th><td>{{ $solicitud->plan?->nombre }} · {{ $solicitud->plan?->precioFormateado() }}</td></tr>
            <tr>
                <th>Pago</th>
                <td>
                    {{ $solicitud->pago?->metodo_pago ?: $solicitud->pago?->metodo ?: '—' }}
                    · Ref: {{ $solicitud->pago?->referencia_pago ?: $solicitud->pago?->referencia ?: '—' }}
                    · <span class="ad-badge {{ $solicitud->pago?->estado_pago === 'VALIDADO' ? 'ad-badge-ok' : 'ad-badge-pendiente' }}">{{ $solicitud->pago?->estado_pago ?: '—' }}</span>
                </td>
            </tr>
            <tr>
                <th>Comprobante</th>
                <td>
                    @if ($solicitud->pago?->comprobante_url)
                        <a class="ad-link" href="{{ $solicitud->pago->comprobante_url }}" target="_blank" rel="noopener">Ver archivo</a>
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
                    <textarea name="observacion_admin" rows="3">{{ old('observacion_admin') }}</textarea>
                </label>
                <div class="ad-modal-actions">
                    <button class="ad-btn ad-btn-ghost" formaction="{{ route('admin.solicitudes.rechazar', $solicitud->id_solicitud) }}" formmethod="post" type="submit">Rechazar</button>
                    <button class="ad-btn" type="submit">Aprobar y crear cliente</button>
                </div>
            </form>
        @endif

        <p style="margin-top:16px"><a class="ad-btn ad-btn-ghost" href="{{ route('admin.solicitudes.index') }}">← Volver</a></p>
    </section>
@endsection
