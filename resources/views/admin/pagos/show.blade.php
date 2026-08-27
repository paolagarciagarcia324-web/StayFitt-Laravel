@extends('layouts.admin')

@section('title', 'Detalle de pago')
@section('subtitle', 'Pago #'.$pago->id_pago)

@section('content')
    @php
        $solicitud = $pago->solicitud;
        $comp = $pago->comprobanteSrc();
    @endphp

    <section class="ad-card ad-users">
        <div class="ad-card-head">
            <h2>Información del pago</h2>
            @if ($solicitud)
                <a class="ad-btn ad-btn-ghost" href="{{ route('admin.solicitudes.show', $solicitud->id_solicitud) }}">Ver solicitud</a>
            @endif
        </div>

        <div class="ad-sol-grid">
            <div class="ad-sol-field">
                <span>Referencia</span>
                <strong>{{ $pago->referencia_pago ?: $pago->referencia ?: '—' }}</strong>
            </div>
            <div class="ad-sol-field">
                <span>Monto</span>
                <strong>${{ number_format((float) $pago->monto, 0, ',', '.') }} {{ $pago->moneda }}</strong>
            </div>
            <div class="ad-sol-field">
                <span>Estado</span>
                <strong>
                    <span class="ad-badge {{ $pago->estado_pago === 'VALIDADO' ? 'ad-badge-ok' : ($pago->estado_pago === 'PENDIENTE' ? 'ad-badge-pendiente' : 'ad-badge-bad') }}">
                        {{ $pago->estado_pago }}
                    </span>
                </strong>
            </div>
            <div class="ad-sol-field">
                <span>Método / Pasarela</span>
                <strong>{{ $pago->metodo_pago ?: $pago->metodo ?: '—' }} · {{ $pago->pasarela ?: '—' }}</strong>
            </div>
            <div class="ad-sol-field">
                <span>Correo cliente</span>
                <strong>{{ $solicitud?->correo ?: '—' }}</strong>
            </div>
            <div class="ad-sol-field">
                <span>Plan</span>
                <strong>{{ $pago->plan?->nombre ?: '—' }}</strong>
            </div>
            <div class="ad-sol-field">
                <span>Fecha pago</span>
                <strong>{{ optional($pago->fechaPagoEfectiva())->format('d/m/Y H:i') ?: '—' }}</strong>
            </div>
            <div class="ad-sol-field">
                <span>Vencimiento plan</span>
                <strong>{{ optional($pago->fechaVencimiento())->format('d/m/Y') ?: '—' }}</strong>
            </div>
        </div>

        @if ($comp)
            <div style="margin-top:16px">
                <p class="ad-user-mail">Comprobante</p>
                @include('partials.admin-media-thumb', ['src' => $comp, 'alt' => 'Comprobante', 'size' => 'md', 'icon' => 'pagos'])
            </div>
        @endif

        @if ($pago->observacion_admin)
            <p class="ad-muted-note" style="margin-top:14px">{{ $pago->observacion_admin }}</p>
        @endif
    </section>

    <section class="ad-card ad-users" style="margin-top:16px">
        <h2>Actualizar estado</h2>
        <form class="ad-form" method="POST" action="{{ route('admin.pagos.update', $pago->id_pago) }}">
            @csrf
            @method('PUT')
            <div class="ad-form-row">
                <label>
                    <span>Estado *</span>
                    <select name="estado_pago" required>
                        @foreach (['PENDIENTE', 'VALIDADO', 'RECHAZADO'] as $estado)
                            <option value="{{ $estado }}" @selected(old('estado_pago', $pago->estado_pago) === $estado)>{{ $estado }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <label>
                <span>Observación admin</span>
                <textarea name="observacion_admin" rows="3">{{ old('observacion_admin', $pago->observacion_admin) }}</textarea>
            </label>
            <button class="ad-btn" type="submit">Guardar cambios</button>
        </form>
    </section>
@endsection
