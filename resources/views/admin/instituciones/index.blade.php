@extends('layouts.admin')

@section('title', 'Instituciones')
@section('subtitle', 'Convenios y clientes agrupados por sede')

@section('content')
    <section class="ad-card ad-users">
        <div class="ad-users-bar">
            <form class="ad-users-search" method="GET" action="{{ route('admin.instituciones.index') }}">
                <span>⌕</span>
                <input type="search" name="q" value="{{ $q }}" placeholder="Buscar institución o cliente">
            </form>
            <button class="ad-btn ad-btn-add" type="button" data-open-inst-form>+ Nueva institución</button>
        </div>

        @forelse ($instituciones as $inst)
            <article class="ad-inst-block">
                <header class="ad-inst-head">
                    <div>
                        <h2>{{ $inst->nombre }}</h2>
                        <p>{{ $inst->ciudad ?: 'Sin ciudad' }} · {{ $inst->correo ?: $inst->telefono ?: 'Sin contacto' }}</p>
                    </div>
                    <div class="ad-inst-meta">
                        <span class="ad-pill-days">{{ (int) $inst->cupos_usados }}/{{ (int) $inst->cupos_totales }} cupos</span>
                        <span class="ad-badge ad-badge-ok">{{ $inst->estado }}</span>
                    </div>
                </header>
                @include('partials.admin-client-table', [
                    'clientes' => $inst->clientes,
                    'variant' => 'virtual',
                    'compact' => true,
                    'emptyMessage' => 'Esta institución aún no tiene clientes vinculados.',
                ])
            </article>
        @empty
            <p class="ad-empty">No hay instituciones registradas.</p>
        @endforelse
    </section>

    <div class="ad-modal {{ $errors->any() ? 'is-open' : '' }}" data-inst-modal {{ $errors->any() ? '' : 'hidden' }}>
        <div class="ad-modal-card">
            <header class="ad-modal-head">
                <div>
                    <h2>Nueva institución</h2>
                    <p>Convenio corporativo o sede aliada.</p>
                </div>
                <button type="button" class="ad-modal-close" data-close-inst-form aria-label="Cerrar">×</button>
            </header>
            <form class="ad-form" method="POST" action="{{ route('admin.instituciones.store') }}">
                @csrf
                <div class="ad-form-row">
                    <label><span>Nombre *</span><input type="text" name="nombre" value="{{ old('nombre') }}" required></label>
                    <label><span>NIT</span><input type="text" name="nit" value="{{ old('nit') }}"></label>
                </div>
                <div class="ad-form-row">
                    <label><span>Contacto</span><input type="text" name="contacto_nombre" value="{{ old('contacto_nombre') }}"></label>
                    <label><span>Ciudad</span><input type="text" name="ciudad" value="{{ old('ciudad') }}"></label>
                </div>
                <div class="ad-form-row">
                    <label><span>Correo</span><input type="email" name="correo" value="{{ old('correo') }}"></label>
                    <label><span>Teléfono</span><input type="text" name="telefono" value="{{ old('telefono') }}"></label>
                </div>
                <div class="ad-form-row">
                    <label><span>Cupos totales</span><input type="number" name="cupos_totales" value="{{ old('cupos_totales', 0) }}" min="0"></label>
                    <label><span>Convenio</span><input type="text" name="convenio" value="{{ old('convenio') }}"></label>
                </div>
                <div class="ad-modal-actions">
                    <button class="ad-btn ad-btn-ghost" type="button" data-close-inst-form>Cancelar</button>
                    <button class="ad-btn" type="submit">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            var modal = document.querySelector('[data-inst-modal]');
            function open() { if (modal) { modal.hidden = false; modal.classList.add('is-open'); } }
            function close() { if (modal) { modal.hidden = true; modal.classList.remove('is-open'); } }
            document.querySelector('[data-open-inst-form]')?.addEventListener('click', open);
            document.querySelectorAll('[data-close-inst-form]').forEach(function (b) { b.addEventListener('click', close); });
            modal?.addEventListener('click', function (e) { if (e.target === modal) close(); });
        })();
    </script>
@endsection
