@extends('layouts.admin')

@section('title', 'Detalle de pago')
@section('subtitle', 'Revisión del cobro')

@section('actions')
    <a class="ad-btn ad-btn-ghost" href="{{ route('admin.pagos.index') }}#pay-detalle">← Volver a Detalle de Pago</a>
@endsection

@section('flash')
@endsection

@section('content')
    @php
        $backUrl = route('admin.pagos.index').'#pay-detalle';
        $solicitud = $pago->solicitud;
        $user = $pago->cliente?->usuario
            ?: $pago->planCliente?->cliente?->usuario;
        $nombre = trim(($user?->nombres ?? '').' '.($user?->apellidos ?? ''));
        if ($nombre === '') {
            $nombre = trim(($solicitud?->nombres ?? '').' '.($solicitud?->apellidos ?? ''));
        }
        if ($nombre === '') {
            $nombre = $user?->correo ?: ($solicitud?->correo ?: 'Cliente');
        }
        $correo = $user?->correo ?: ($solicitud?->correo ?: '');
        $ref = $pago->referencia_pago ?: $pago->referencia ?: '#'.$pago->id_pago;
        $metodo = $pago->metodo_pago ?: $pago->metodo ?: '—';
        $pas = strtoupper((string) ($pago->pasarela ?: ''));
        $esPasarela = ($pas !== '' && ! in_array($pas, ['NINGUNA', 'PENDIENTE'], true))
            || str_contains(strtoupper((string) $metodo), 'PASARELA');
        $canal = $esPasarela ? 'Pasarela' : 'Comprobante';
        $estado = (string) $pago->estado_pago;
        $badgeClass = $estado === 'VALIDADO' ? 'ad-badge-ok' : ($estado === 'PENDIENTE' ? 'ad-badge-pendiente' : 'ad-badge-bad');
        $comp = $pago->comprobanteSrc();
        $planNombre = $pago->plan?->nombre ?: ($solicitud?->plan?->nombre ?: '—');
        $fechaPago = optional($pago->fechaPagoEfectiva())->format('d/m/Y H:i') ?: '—';
        $vence = optional($pago->fechaVencimiento())->format('d/m/Y') ?: '—';
        $validadoEn = optional($pago->fecha_validacion)->format('d/m/Y H:i');
    @endphp

    <p class="ad-pay-modal-hint">
        Revisa el cobro en el panel. Usa <strong>Volver a Detalle de Pago</strong> para regresar a las tarjetas.
    </p>

    <div
        class="ad-modal ad-pay-modal is-open"
        data-pay-modal
        role="dialog"
        aria-modal="true"
        aria-labelledby="ad-pay-modal-title"
    >
        <div class="ad-modal-card ad-pay-modal-card">
            <header class="ad-modal-head ad-pay-modal-head">
                <div class="ad-pay-modal-who">
                    @if ($user)
                        @include('partials.admin-avatar', ['user' => $user, 'size' => 'sm'])
                    @else
                        @php
                            $iniciales = strtoupper(
                                mb_substr((string) ($solicitud?->nombres ?: $nombre), 0, 1).
                                mb_substr((string) ($solicitud?->apellidos ?: 'F'), 0, 1)
                            );
                        @endphp
                        <span class="ad-user-avatar is-initials is-sm">{{ $iniciales }}</span>
                    @endif
                    <div>
                        <h2 id="ad-pay-modal-title">{{ $nombre }}</h2>
                        <p>{{ $correo !== '' ? $correo : 'Sin correo registrado' }}</p>
                    </div>
                </div>
                <div class="ad-pay-modal-sum">
                    <span class="ad-badge {{ $badgeClass }}">{{ $estado }}</span>
                    <strong>${{ number_format((float) $pago->monto, 0, ',', '.') }} <small>{{ $pago->moneda ?: 'COP' }}</small></strong>
                    <em>{{ $ref }}</em>
                </div>
                <a class="ad-modal-close" href="{{ $backUrl }}" aria-label="Cerrar y volver">×</a>
            </header>

            @if (session('success'))
                <div class="ad-pay-modal-flash">{{ session('success') }}</div>
            @endif

            <div class="ad-pay-modal-body">
                <section class="ad-pay-modal-block">
                    <h3>Información del pago</h3>
                    <dl class="ad-pay-modal-facts">
                        <div>
                            <dt>Referencia</dt>
                            <dd>{{ $ref }}</dd>
                        </div>
                        <div>
                            <dt>Canal / método</dt>
                            <dd>{{ $canal }} · {{ $metodo }}{{ $pas !== '' && $pas !== 'NINGUNA' ? ' · '.$pas : '' }}</dd>
                        </div>
                        <div>
                            <dt>Plan</dt>
                            <dd>{{ $planNombre }}</dd>
                        </div>
                        <div>
                            <dt>Vencimiento</dt>
                            <dd>{{ $vence }}</dd>
                        </div>
                        <div>
                            <dt>Fecha pago</dt>
                            <dd>{{ $fechaPago }}</dd>
                        </div>
                        <div>
                            <dt>Validado el</dt>
                            <dd>{{ $validadoEn ?: '—' }}</dd>
                        </div>
                    </dl>
                </section>

                <section class="ad-pay-modal-block ad-pay-modal-comp">
                    <div class="ad-pay-modal-comp-head">
                        <h3>Comprobante</h3>
                        @if ($comp)
                            <a href="{{ $comp }}" target="_blank" rel="noopener">Abrir</a>
                        @endif
                    </div>
                    @if ($comp)
                        <a class="ad-pay-modal-thumb" href="{{ $comp }}" target="_blank" rel="noopener">
                            <img src="{{ $comp }}" alt="Comprobante {{ $ref }}">
                        </a>
                    @else
                        <p class="ad-pay-modal-empty">Sin comprobante adjunto{{ $esPasarela ? ' (pasarela)' : '' }}.</p>
                    @endif
                </section>

                <section class="ad-pay-modal-block">
                    <h3>Actualizar estado</h3>
                    <form class="ad-form ad-pay-modal-form" method="POST" action="{{ route('admin.pagos.update', $pago->id_pago) }}">
                        @csrf
                        @method('PUT')
                        <div class="ad-pay-modal-form-row">
                            <label>
                                <span>Estado *</span>
                                <select name="estado_pago" required>
                                    @foreach (['PENDIENTE' => 'Pendiente', 'VALIDADO' => 'Validado', 'RECHAZADO' => 'Rechazado'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('estado_pago', $pago->estado_pago) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="is-grow">
                                <span>Observación admin</span>
                                <textarea name="observacion_admin" rows="2" placeholder="Nota breve para el equipo">{{ old('observacion_admin', $pago->observacion_admin) }}</textarea>
                            </label>
                        </div>
                        <div class="ad-modal-actions ad-pay-modal-actions">
                            <a class="ad-btn ad-btn-ghost" href="{{ $backUrl }}">← Volver a Detalle de Pago</a>
                            @if ($solicitud)
                                <a class="ad-btn ad-btn-ghost" href="{{ route('admin.solicitudes.show', $solicitud->id_solicitud) }}">Solicitud</a>
                            @endif
                            <button class="ad-btn ad-pay-modal-save" type="submit">Guardar cambios</button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    var back = @json($backUrl);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') window.location.href = back;
    });
    var modal = document.querySelector('[data-pay-modal]');
    if (!modal) return;
    modal.addEventListener('click', function (e) {
        if (e.target === modal) window.location.href = back;
    });
})();
</script>
@endpush
