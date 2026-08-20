@php
    $isEdit = $plan->exists ?? false;
    $selected = old('modulos', $isEdit ? $plan->modulos->pluck('id_modulo')->all() : []);
@endphp
<div class="ad-modal {{ $errors->any() && ! $isEdit ? 'is-open' : '' }}" data-plan-modal {{ ($errors->any() && ! $isEdit) ? '' : 'hidden' }}>
    <div class="ad-modal-card ad-modal-wide">
        <header class="ad-modal-head">
            <div>
                <h2>Nuevo plan</h2>
                <p>Configuración comercial, cupos y módulos.</p>
            </div>
            <button type="button" class="ad-modal-close" data-close-plan-form aria-label="Cerrar">×</button>
        </header>
        @if ($errors->any() && ! $isEdit)
            <div class="ad-alert ad-alert-error"><ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif
        <form class="ad-form" method="POST" action="{{ route('admin.planes.store') }}">
            @csrf
            @include('admin.planes._fields', ['plan' => $plan, 'modulos' => $modulos, 'selected' => $selected, 'readonlyCupos' => false])
            <div class="ad-modal-actions">
                <button class="ad-btn ad-btn-ghost" type="button" data-close-plan-form>Cancelar</button>
                <button class="ad-btn" type="submit">Crear plan</button>
            </div>
        </form>
    </div>
</div>
<script>
    (function () {
        var modal = document.querySelector('[data-plan-modal]');
        function open() { if (modal) { modal.hidden = false; modal.classList.add('is-open'); } }
        function close() { if (modal) { modal.hidden = true; modal.classList.remove('is-open'); } }
        document.querySelector('[data-open-plan-form]')?.addEventListener('click', open);
        document.querySelectorAll('[data-close-plan-form]').forEach(function (b) { b.addEventListener('click', close); });
        modal?.addEventListener('click', function (e) { if (e.target === modal) close(); });
    })();
</script>
