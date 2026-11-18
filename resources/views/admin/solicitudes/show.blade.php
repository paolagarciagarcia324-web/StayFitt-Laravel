@extends('layouts.admin')

@section('title', 'Revisar solicitud')
@section('subtitle', $solicitud->codigo_solicitud)

@section('content')
    @php
        $clienteActivo = \App\Models\User::query()
            ->where('correo', $solicitud->correo)
            ->whereHas('cliente', fn ($q) => $q->whereIn('estado_cliente', ['ACTIVO', 'ACTIVA']))
            ->exists();
        $pago = $solicitud->pago;
        $comp = $pago?->comprobanteSrc();
        $iniciales = mb_strtoupper(
            mb_substr(trim((string) $solicitud->nombres), 0, 1)
            .mb_substr(trim((string) $solicitud->apellidos), 0, 1)
        );
        $monto = (float) ($solicitud->total ?? $pago?->monto ?? 0);
        $pasarela = $pago?->pasarela ?: '—';
        $metodo = $pago?->metodo_pago ?: $pago?->metodo ?: '—';
        $refPago = $pago?->referencia_pago ?: $pago?->referencia ?: '—';
        $estadoPago = $pago?->estado_pago ?: '—';
        $fechaRev = $solicitud->fechaRevision();
        $esNovedad = $solicitud->estado_solicitud === 'PENDIENTE';
        $badgeLabel = $esNovedad ? 'NOVEDAD' : $solicitud->estado_solicitud;
    @endphp

    <article class="ad-sol-card">
        <header class="ad-sol-hero">
            <div class="ad-sol-hero-main">
                <span class="ad-sol-avatar" aria-hidden="true">{{ $iniciales !== '' ? $iniciales : 'FF' }}</span>
                <div>
                    <p class="ad-sol-kicker">{{ $esNovedad ? 'Novedad' : 'Solicitud' }} · {{ $solicitud->modalidad }}</p>
                    <h2>{{ $solicitud->nombres }} {{ $solicitud->apellidos }}</h2>
                    <p class="ad-sol-mail">{{ $solicitud->correo }}</p>
                </div>
            </div>
            <div class="ad-sol-hero-side">
                @include('partials.admin-solicitud-codigo', ['codigo' => $solicitud->codigo_solicitud])
                <span class="ad-badge {{ $esNovedad ? 'ad-badge-pendiente' : ($solicitud->estado_solicitud === 'VALIDADA' ? 'ad-badge-ok' : 'ad-badge-bad') }}">
                    {{ $badgeLabel }}
                </span>
            </div>
        </header>

        <p class="ad-muted-note">
            Si la pasarela simulada ya confirmó el pago, el cliente se activa solo. Si el cobro fue por caja,
            transferencia o la pasarela no reflejó el pago, <strong>aprueba aquí</strong> para crear o activar
            la cuenta y sus módulos.
        </p>

        <div class="ad-sol-grid">
            <div class="ad-sol-field">
                <span>Documento</span>
                <strong>{{ $solicitud->documento_identidad ?: $solicitud->documento ?: '—' }}</strong>
            </div>
            <div class="ad-sol-field">
                <span>Teléfono</span>
                <strong>{{ $solicitud->telefono ?: '—' }}</strong>
            </div>
            <div class="ad-sol-field">
                <span>Modalidad</span>
                <strong>{{ $solicitud->modalidad }}</strong>
            </div>
            <div class="ad-sol-field">
                <span>Plan</span>
                <strong>{{ $solicitud->plan?->nombre ?: '—' }}</strong>
            </div>
            <div class="ad-sol-field">
                <span>Monto</span>
                <strong>${{ number_format($monto, 0, ',', '.') }}</strong>
            </div>
            <div class="ad-sol-field">
                <span>Pasarela</span>
                <strong>{{ $pasarela }} · {{ $metodo }}</strong>
            </div>
            <div class="ad-sol-field">
                <span>Referencia pago</span>
                <strong>{{ $refPago }}</strong>
            </div>
            <div class="ad-sol-field">
                <span>Estado pago</span>
                <strong>
                    <span class="ad-badge {{ $estadoPago === 'VALIDADO' ? 'ad-badge-ok' : ($estadoPago === 'PENDIENTE' ? 'ad-badge-pendiente' : 'ad-badge-bad') }}">
                        {{ $estadoPago }}
                    </span>
                </strong>
            </div>
            <div class="ad-sol-field">
                <span>Alta</span>
                <strong>{{ $solicitud->creado_en ? $solicitud->creado_en->format('d/m/Y H:i') : '—' }}</strong>
            </div>
            <div class="ad-sol-field">
                <span>Revisión</span>
                <strong>{{ $fechaRev ? $fechaRev->format('d/m/Y H:i') : 'Pendiente de revisión' }}</strong>
            </div>
        </div>

        @if ($pago)
            <section class="ad-sol-comp">
                <h3>Comprobante de pasarela</h3>
                <div class="ad-sol-comp-row">
                    @include('partials.admin-media-thumb', ['src' => $comp, 'alt' => 'Comprobante', 'icon' => 'pagos'])
                    <button type="button" class="ad-btn" data-open-lightbox data-src="{{ $comp }}">Ver comprobante</button>
                </div>
            </section>
        @endif

        @if ($solicitud->observacion_admin)
            <div class="ad-sol-field is-full" style="margin-top:12px">
                <span>Observación admin</span>
                <strong>{{ $solicitud->observacion_admin }}</strong>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.solicitudes.suspender', $solicitud->id_solicitud) }}" class="ad-form ad-sol-actions" data-sol-form>
            @csrf
            <label>
                <span>Motivo de la incidencia (opcional)</span>
                <textarea name="observacion_admin" rows="3" placeholder="Ej. pago no reflejado / comprobante inválido">{{ old('observacion_admin') }}</textarea>
            </label>
            <div class="ad-modal-actions">
                <a class="ad-btn ad-btn-ghost" href="{{ route('admin.solicitudes.index') }}">Volver</a>
                @if ($solicitud->estado_solicitud === 'PENDIENTE')
                    <button
                        class="ad-btn"
                        type="button"
                        data-confirm-action
                        data-action="{{ route('admin.solicitudes.aprobar', $solicitud->id_solicitud) }}"
                        data-title="¿Aprobar y activar esta solicitud?"
                        data-body="Se creará o reactivará el cliente, su plan y los módulos. Si fue un clic accidental, pulsa Cancelar."
                        data-confirm-label="Sí, aprobar"
                    >Aprobar y activar</button>
                    <button
                        class="ad-btn ad-btn-ghost"
                        type="button"
                        data-confirm-action
                        data-action="{{ route('admin.solicitudes.rechazar', $solicitud->id_solicitud) }}"
                        data-title="¿Rechazar esta solicitud?"
                        data-body="Si fue un clic accidental, pulsa Cancelar. Solo continúa si quieres rechazar de forma intencional."
                        data-confirm-label="Sí, rechazar"
                    >Rechazar</button>
                @endif
                @php
                    $bloquearLabel = $clienteActivo || $solicitud->estado_solicitud === 'VALIDADA' ? 'Suspender acceso' : 'Bloquear por fraude';
                @endphp
                <button
                    class="ad-btn is-danger"
                    type="button"
                    data-confirm-action
                    data-action="{{ route('admin.solicitudes.suspender', $solicitud->id_solicitud) }}"
                    data-title="¿{{ $bloquearLabel }}?"
                    data-body="Si fue un clic accidental, pulsa Cancelar para retractarte. Solo confirma si la acción es intencional."
                    data-confirm-label="Sí, {{ strtolower($bloquearLabel) }}"
                    data-danger="1"
                >{{ $bloquearLabel }}</button>
            </div>
        </form>
    </article>

    <div class="ad-modal" data-confirm-modal hidden>
        <div class="ad-modal-card" role="dialog" aria-modal="true" aria-labelledby="ad-confirm-title">
            <div class="ad-modal-head">
                <div>
                    <h2 id="ad-confirm-title" data-confirm-title>Confirmar acción</h2>
                    <p data-confirm-body>¿Deseas continuar?</p>
                </div>
                <button type="button" class="ad-modal-close" data-confirm-cancel aria-label="Cerrar">×</button>
            </div>
            <div class="ad-modal-actions">
                <button type="button" class="ad-btn ad-btn-ghost" data-confirm-cancel>Cancelar</button>
                <button type="button" class="ad-btn is-danger" data-confirm-ok>Confirmar</button>
            </div>
        </div>
    </div>

    <div class="ad-lightbox" data-lightbox hidden>
        <button type="button" class="ad-lightbox-close" data-close-lightbox aria-label="Cerrar">×</button>
        <div class="ad-lightbox-panel" data-lightbox-panel>
            <img src="" alt="Comprobante de pago" data-lightbox-img>
        </div>
    </div>

    <script>
        (function () {
            var box = document.querySelector('[data-lightbox]');
            var img = document.querySelector('[data-lightbox-img]');
            if (box && img) {
                function openLightbox(src) {
                    img.src = src;
                    box.removeAttribute('hidden');
                    document.body.style.overflow = 'hidden';
                }
                function closeLightbox() {
                    box.setAttribute('hidden', '');
                    img.removeAttribute('src');
                    document.body.style.overflow = '';
                }
                document.querySelectorAll('[data-open-lightbox]').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        openLightbox(btn.getAttribute('data-src'));
                    });
                });
                box.addEventListener('click', function (e) {
                    if (e.target === box || e.target.closest('[data-close-lightbox]')) closeLightbox();
                });
                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape' && !box.hasAttribute('hidden')) closeLightbox();
                });
            }

            var form = document.querySelector('[data-sol-form]');
            var modal = document.querySelector('[data-confirm-modal]');
            if (!form || !modal) return;

            var titleEl = modal.querySelector('[data-confirm-title]');
            var bodyEl = modal.querySelector('[data-confirm-body]');
            var okBtn = modal.querySelector('[data-confirm-ok]');
            var pendingAction = null;

            function openConfirm(btn) {
                pendingAction = btn.getAttribute('data-action');
                titleEl.textContent = btn.getAttribute('data-title') || 'Confirmar acción';
                bodyEl.textContent = btn.getAttribute('data-body') || '¿Deseas continuar?';
                okBtn.textContent = btn.getAttribute('data-confirm-label') || 'Confirmar';
                if (btn.getAttribute('data-danger') === '1') {
                    okBtn.classList.add('is-danger');
                    okBtn.classList.remove('ad-btn-ghost');
                } else {
                    okBtn.classList.remove('is-danger');
                }
                modal.removeAttribute('hidden');
                document.body.style.overflow = 'hidden';
            }
            function closeConfirm() {
                pendingAction = null;
                modal.setAttribute('hidden', '');
                document.body.style.overflow = '';
            }

            document.querySelectorAll('[data-confirm-action]').forEach(function (btn) {
                btn.addEventListener('click', function () { openConfirm(btn); });
            });
            modal.querySelectorAll('[data-confirm-cancel]').forEach(function (btn) {
                btn.addEventListener('click', closeConfirm);
            });
            modal.addEventListener('click', function (e) {
                if (e.target === modal) closeConfirm();
            });
            okBtn.addEventListener('click', function () {
                if (!pendingAction) return;
                form.setAttribute('action', pendingAction);
                form.submit();
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && !modal.hasAttribute('hidden')) closeConfirm();
            });
        })();
    </script>
@endsection
