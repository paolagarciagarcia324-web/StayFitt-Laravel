@php
    $horarios = $horarios ?? collect();
    $plan = $plan ?? null;
@endphp
<div class="ad-modal {{ isset($errors) && $errors->any() ? 'is-open' : '' }}" data-user-modal {{ isset($errors) && $errors->any() ? '' : 'hidden' }}>
    <div class="ad-modal-card ad-user-create-modal">
        <header class="ad-modal-head ad-user-create-head">
            <div>
                <h2>Nuevo usuario</h2>
                <p>Alta presencial · ficha de sala</p>
            </div>
            <button type="button" class="ad-modal-close" data-close-user-form aria-label="Cerrar">×</button>
        </header>

        @if (isset($errors) && $errors->any())
            <div class="ad-alert ad-alert-error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="ad-form ad-user-create-form" method="POST" action="{{ route('admin.usuarios.store') }}" enctype="multipart/form-data" data-user-create-form novalidate>
            @csrf
            <div class="ad-user-create-body">
                @include('admin.usuarios._create_fields', ['horarios' => $horarios, 'plan' => $plan])
            </div>
            <div class="ad-modal-actions ad-user-create-actions">
                <button class="ad-btn ad-btn-ghost" type="button" data-close-user-form>Cancelar</button>
                <button class="ad-btn" type="submit">Crear usuario</button>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        if (window.__ffUserCreateModalReady) return;
        window.__ffUserCreateModalReady = true;

        function wireCreatePreview() {
            var file = document.querySelector('[data-user-create-file]');
            var url = document.querySelector('[data-user-create-url]');
            var img = document.querySelector('[data-user-create-img]');
            var empty = document.querySelector('[data-user-create-empty]');
            function show(src) {
                if (!img) return;
                if (src) {
                    img.src = src;
                    img.hidden = false;
                    if (empty) empty.hidden = true;
                } else {
                    img.hidden = true;
                    img.removeAttribute('src');
                    if (empty) empty.hidden = false;
                }
            }
            file?.addEventListener('change', function () {
                var f = file.files && file.files[0];
                if (!f) return;
                var reader = new FileReader();
                reader.onload = function (e) { show(e.target.result); };
                reader.readAsDataURL(f);
            });
            url?.addEventListener('input', function () {
                if (file && file.files && file.files.length) return;
                show(url.value.trim());
            });
        }

        wireCreatePreview();

        var modal = document.querySelector('[data-user-modal]');
        function open() {
            if (!modal) return;
            modal.hidden = false;
            modal.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }
        function close() {
            if (!modal) return;
            modal.hidden = true;
            modal.classList.remove('is-open');
            document.body.style.overflow = '';
        }

        document.querySelectorAll('[data-open-user-form]').forEach(function (btn) {
            btn.addEventListener('click', open);
        });
        document.querySelectorAll('[data-close-user-form]').forEach(function (btn) {
            btn.addEventListener('click', close);
        });
        modal?.addEventListener('click', function (event) {
            if (event.target === modal) close();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal && !modal.hasAttribute('hidden')) close();
        });
    })();
</script>
