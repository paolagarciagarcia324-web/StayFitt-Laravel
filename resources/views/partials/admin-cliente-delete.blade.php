<div class="ad-modal" data-cliente-confirm-modal hidden>
    <div class="ad-modal-card" role="dialog" aria-modal="true" aria-labelledby="ad-cliente-del-title">
        <header class="ad-modal-head">
            <div>
                <h2 id="ad-cliente-del-title">Eliminar cliente</h2>
                <p>Se quitará del listado. Esta acción no se puede deshacer.</p>
            </div>
            <button type="button" class="ad-modal-close" data-cliente-confirm-cancel aria-label="Cerrar">×</button>
        </header>
        <div class="ad-modal-actions">
            <button type="button" class="ad-btn ad-btn-ghost" data-cliente-confirm-cancel>Cancelar</button>
            <button type="button" class="ad-btn is-danger" data-cliente-confirm-ok>Sí, eliminar</button>
        </div>
    </div>
</div>

@php $ok = session('cliente_ok'); @endphp
@if (is_array($ok) && ! empty($ok['accion']))
    @php
        $accionLabel = match ($ok['accion'] ?? '') {
            'eliminado' => 'Cliente eliminado',
            'creado' => 'Cliente creado',
            default => 'Acción completada',
        };
        $detalle = match ($ok['accion'] ?? '') {
            'eliminado' => 'El cliente ya no aparece en el panel de Personas.',
            'creado' => 'El alta presencial quedó activa y ya figura en este directorio.',
            default => 'La operación terminó correctamente.',
        };
    @endphp
    <div class="ad-modal ad-ok-modal is-open" data-ok-modal role="dialog" aria-modal="true" aria-labelledby="ad-ok-title">
        <div class="ad-modal-card ad-ok-card">
            <div class="ad-ok-confetti" aria-hidden="true">
                <i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i>
            </div>
            <div class="ad-ok-check" aria-hidden="true">
                <svg viewBox="0 0 52 52" width="56" height="56">
                    <circle cx="26" cy="26" r="24" fill="none"/>
                    <path fill="none" d="M14 27l8 8 16-16"/>
                </svg>
            </div>
            <h2 id="ad-ok-title">{{ $accionLabel }}</h2>
            <p class="ad-ok-lead">fue un éxito</p>
            <p class="ad-muted-note">{{ $detalle }}</p>
            <button type="button" class="ad-btn" data-close-ok>Continuar</button>
        </div>
    </div>
@endif

<script>
    (function () {
        var confirmModal = document.querySelector('[data-cliente-confirm-modal]');
        var pendingDeleteForm = null;
        function openConfirm(form) {
            pendingDeleteForm = form;
            if (!confirmModal) return;
            confirmModal.hidden = false;
            confirmModal.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }
        function closeConfirm() {
            pendingDeleteForm = null;
            if (!confirmModal) return;
            confirmModal.hidden = true;
            confirmModal.classList.remove('is-open');
            document.body.style.overflow = '';
        }
        document.querySelectorAll('[data-cliente-delete]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var form = btn.closest('[data-cliente-delete-form]');
                if (form) openConfirm(form);
            });
        });
        document.querySelectorAll('[data-cliente-confirm-cancel]').forEach(function (b) {
            b.addEventListener('click', closeConfirm);
        });
        confirmModal?.querySelector('[data-cliente-confirm-ok]')?.addEventListener('click', function () {
            if (pendingDeleteForm) pendingDeleteForm.submit();
        });
        confirmModal?.addEventListener('click', function (e) {
            if (e.target === confirmModal) closeConfirm();
        });

        var okBox = document.querySelector('[data-ok-modal]');
        if (okBox) {
            document.body.style.overflow = 'hidden';
            function closeOk() {
                okBox.hidden = true;
                okBox.classList.remove('is-open');
                document.body.style.overflow = '';
            }
            okBox.querySelectorAll('[data-close-ok]').forEach(function (b) {
                b.addEventListener('click', closeOk);
            });
            okBox.addEventListener('click', function (e) {
                if (e.target === okBox) closeOk();
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeOk();
            });
        }
    })();
</script>
