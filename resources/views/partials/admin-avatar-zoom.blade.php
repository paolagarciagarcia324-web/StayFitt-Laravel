<div class="ad-avatar-zoom" data-avatar-zoom-modal hidden>
    <button type="button" class="ad-avatar-zoom-backdrop" data-avatar-zoom-close aria-label="Cerrar"></button>
    <div class="ad-avatar-zoom-stage" role="dialog" aria-modal="true" aria-label="Foto de perfil ampliada">
        <img src="" alt="" data-avatar-zoom-img>
        <button type="button" class="ad-avatar-zoom-x" data-avatar-zoom-close aria-label="Cerrar">×</button>
    </div>
</div>

<script>
    (function () {
        if (window.__ffAvatarZoomReady) return;
        window.__ffAvatarZoomReady = true;

        var modal = document.querySelector('[data-avatar-zoom-modal]');
        if (!modal) return;
        var img = modal.querySelector('[data-avatar-zoom-img]');

        function openZoom(src, alt) {
            if (!src || !img) return;
            img.src = src;
            img.alt = alt || 'Foto de perfil';
            modal.hidden = false;
            modal.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }

        function closeZoom() {
            modal.hidden = true;
            modal.classList.remove('is-open');
            document.body.style.overflow = '';
            if (img) img.removeAttribute('src');
        }

        document.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-avatar-zoom]');
            if (!btn) return;
            e.preventDefault();
            e.stopPropagation();
            openZoom(btn.getAttribute('data-avatar-src'), btn.getAttribute('data-avatar-alt'));
        });

        modal.querySelectorAll('[data-avatar-zoom-close]').forEach(function (el) {
            el.addEventListener('click', closeZoom);
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal.hasAttribute('hidden')) closeZoom();
        });
    })();
</script>
