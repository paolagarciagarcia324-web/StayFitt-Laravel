@php
    $isEditRequest = (bool) old('_edit') || request()->filled('edit');
    $openOnLoad = $errors->any() || request()->filled('create') || request()->filled('edit');
    $selected = old('modulos', []);
@endphp
<div class="ad-modal ad-plan-modal {{ $openOnLoad ? 'is-open' : '' }}" data-plan-modal {{ $openOnLoad ? '' : 'hidden' }}>
    <div class="ad-modal-card ad-plan-modal-card">
        <header class="ad-modal-head ad-plan-modal-head">
            <div>
                <h2 data-plan-modal-title>{{ $isEditRequest ? 'Editar plan' : 'Nuevo plan' }}</h2>
                <p data-plan-modal-subtitle>{{ $isEditRequest ? 'Actualiza el plan seleccionado.' : 'Define identidad, precio, cupos y módulos del catálogo.' }}</p>
            </div>
            <button type="button" class="ad-modal-close" data-close-plan-form aria-label="Cerrar">×</button>
        </header>
        @if ($errors->any())
            <div class="ad-alert ad-alert-error"><ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif
        <form class="ad-form ad-plan-form" method="POST" action="{{ route('admin.planes.store') }}" enctype="multipart/form-data" data-plan-form>
            @csrf
            <input type="hidden" name="_edit" value="{{ old('_edit', request('edit', '')) }}" data-plan-edit-id>
            <div data-plan-method>@if ($isEditRequest) @method('PUT') @endif</div>
            @include('admin.planes._fields', [
                'plan' => new \App\Models\Plan,
                'modulos' => $modulos,
                'selected' => $selected,
                'readonlyCupos' => $isEditRequest,
            ])
            <div class="ad-modal-actions ad-plan-modal-actions">
                <button class="ad-btn ad-btn-ghost" type="button" data-close-plan-form>Cancelar</button>
                <button class="ad-btn" type="submit" data-plan-submit>{{ $isEditRequest ? 'Guardar cambios' : 'Crear plan' }}</button>
            </div>
        </form>
    </div>
</div>
