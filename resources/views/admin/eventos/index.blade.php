@extends('layouts.admin')

@section('title', 'Eventos')
@section('subtitle', 'Actividades especiales')

@section('actions')
    <button class="ad-btn ad-btn-add" type="button" data-open-evento-form>+ Crear evento</button>
@endsection

@section('content')
    <section class="ad-card ad-users">
        <table class="ad-table ad-users-table">
            <thead>
                <tr>
                    <th>Evento</th>
                    <th>Tipo</th>
                    <th>Fechas</th>
                    <th>Modalidad</th>
                    <th>Cupos</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($eventos as $evento)
                    <tr>
                        <td>
                            <strong>{{ $evento->nombre }}</strong>
                            <em class="ad-user-mail">{{ \Illuminate\Support\Str::limit($evento->descripcion, 60) }}</em>
                        </td>
                        <td>{{ $evento->tipo }}</td>
                        <td>{{ optional($evento->fecha_inicio)->format('d/m/Y H:i') }}</td>
                        <td>{{ $evento->modalidad }}</td>
                        <td>{{ (int) $evento->cupo_inscritos }}/{{ $evento->cupo_maximo ?: '∞' }}</td>
                        <td><span class="ad-badge {{ $evento->estado === 'PUBLICADO' ? 'ad-badge-ok' : 'ad-badge-pendiente' }}">{{ $evento->estado }}</span></td>
                        <td>
                            <button class="ad-btn ad-btn-ghost" type="button"
                                data-open-evento-edit
                                data-id="{{ $evento->id_evento }}"
                                data-nombre="{{ $evento->nombre }}"
                                data-descripcion="{{ $evento->descripcion }}"
                                data-tipo="{{ $evento->tipo }}"
                                data-inicio="{{ optional($evento->fecha_inicio)->format('Y-m-d\\TH:i') }}"
                                data-fin="{{ optional($evento->fecha_fin)->format('Y-m-d\\TH:i') }}"
                                data-modalidad="{{ $evento->modalidad }}"
                                data-cupo="{{ $evento->cupo_maximo }}"
                                data-estado="{{ $evento->estado }}">Editar</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="ad-empty-hero">
                                <span class="ad-empty-orb">@include('partials.admin-icon', ['icon' => 'eventos'])</span>
                                <strong>Sin eventos</strong>
                                <button class="ad-btn" type="button" data-open-evento-form>Crear evento</button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="ad-pagination">{{ $eventos->links() }}</div>
    </section>

    <div class="ad-modal" data-evento-modal hidden>
        <div class="ad-modal-card">
            <header class="ad-modal-head">
                <div><h2>Nuevo evento</h2></div>
                <button type="button" class="ad-modal-close" data-close-evento-form aria-label="Cerrar">×</button>
            </header>
            <form class="ad-form" method="POST" action="{{ route('admin.eventos.store') }}">
                @csrf
                @include('admin.eventos._fields', ['evento' => null])
                <div class="ad-modal-actions">
                    <button class="ad-btn ad-btn-ghost" type="button" data-close-evento-form>Cancelar</button>
                    <button class="ad-btn" type="submit">Crear</button>
                </div>
            </form>
        </div>
    </div>

    <div class="ad-modal" data-evento-edit-modal hidden>
        <div class="ad-modal-card">
            <header class="ad-modal-head">
                <div><h2>Editar evento</h2></div>
                <button type="button" class="ad-modal-close" data-close-evento-edit aria-label="Cerrar">×</button>
            </header>
            <form class="ad-form" method="POST" data-evento-edit-form>
                @csrf
                @method('PUT')
                @include('admin.eventos._fields', ['evento' => null, 'withDataFields' => true])
                <div class="ad-modal-actions">
                    <button class="ad-btn ad-btn-ghost" type="button" data-close-evento-edit>Cancelar</button>
                    <button class="ad-btn" type="submit">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            var modal = document.querySelector('[data-evento-modal]');
            function open() { if (modal) { modal.hidden = false; modal.classList.add('is-open'); } }
            function close() { if (modal) { modal.hidden = true; modal.classList.remove('is-open'); } }
            document.querySelector('[data-open-evento-form]')?.addEventListener('click', open);
            document.querySelectorAll('[data-close-evento-form]').forEach(function (b) { b.addEventListener('click', close); });
            modal?.addEventListener('click', function (e) { if (e.target === modal) close(); });

            var editModal = document.querySelector('[data-evento-edit-modal]');
            var editForm = document.querySelector('[data-evento-edit-form]');
            function openEdit(btn) {
                if (!editModal || !editForm) return;
                editForm.action = '{{ url('admin/eventos') }}/' + btn.getAttribute('data-id');
                ['nombre','descripcion','tipo','inicio','fin','modalidad','cupo','estado'].forEach(function (f) {
                    var el = editForm.querySelector('[data-field="'+f+'"]');
                    if (el) el.value = btn.getAttribute('data-'+f) || '';
                });
                editModal.hidden = false;
                editModal.classList.add('is-open');
            }
            function closeEdit() { if (editModal) { editModal.hidden = true; editModal.classList.remove('is-open'); } }
            document.querySelectorAll('[data-open-evento-edit]').forEach(function (b) { b.addEventListener('click', function () { openEdit(b); }); });
            document.querySelectorAll('[data-close-evento-edit]').forEach(function (b) { b.addEventListener('click', closeEdit); });
            editModal?.addEventListener('click', function (e) { if (e.target === editModal) closeEdit(); });
        })();
    </script>
@endsection
