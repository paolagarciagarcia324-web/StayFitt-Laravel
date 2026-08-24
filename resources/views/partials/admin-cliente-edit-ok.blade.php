@php $editOk = session('cliente_edit_ok'); @endphp
@if (is_array($editOk))
    @php
        $editNombre = trim((string) ($editOk['nombre'] ?? 'Cliente'));
    @endphp
    <div class="ad-modal ad-ok-modal is-open" data-cli-edit-ok-modal role="dialog" aria-modal="true" aria-labelledby="ad-cli-edit-ok-title">
        <div class="ad-modal-card ad-ok-card ad-cli-edit-ok-card">
            <div class="ad-ok-confetti" aria-hidden="true">
                <i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i>
            </div>
            <div class="ad-ok-check" aria-hidden="true">
                <svg viewBox="0 0 52 52" width="56" height="56">
                    <circle cx="26" cy="26" r="24" fill="none"/>
                    <path fill="none" d="M14 27l8 8 16-16"/>
                </svg>
            </div>
            <h2 id="ad-cli-edit-ok-title">Cambios guardados</h2>
            <p class="ad-ok-lead">{{ $editNombre }}</p>
            <p class="ad-muted-note">Perfil, coach y accesos quedaron actualizados en el directorio.</p>
            <button type="button" class="ad-btn" data-close-cli-edit-ok>Continuar</button>
        </div>
    </div>
    <script>
        (function () {
            var modal = document.querySelector('[data-cli-edit-ok-modal]');
            if (!modal) return;
            document.body.style.overflow = 'hidden';
            function closeOk() {
                modal.classList.remove('is-open');
                modal.hidden = true;
                document.body.style.overflow = '';
            }
            modal.querySelectorAll('[data-close-cli-edit-ok]').forEach(function (btn) {
                btn.addEventListener('click', closeOk);
            });
            modal.addEventListener('click', function (e) {
                if (e.target === modal) closeOk();
            });
            setTimeout(closeOk, 1800);
        })();
    </script>
@endif
