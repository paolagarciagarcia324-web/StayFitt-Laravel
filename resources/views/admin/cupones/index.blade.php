@extends('layouts.admin')

@section('title', 'Cupones')
@section('subtitle', 'Descuentos de planes')

@section('actions')
    <button class="ad-btn ad-btn-add" type="button" data-open-cupon-form>+ Crear cupón</button>
@endsection

@section('content')
    <x-admin.panel title="Cupones activos y vigentes" subtitle="Descuentos aplicables a planes" flush>
        <div class="ad-table-wrap">
        <table class="ad-table ad-users-table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Tipo</th>
                    <th>Valor</th>
                    <th>Plan</th>
                    <th>Vigencia</th>
                    <th>Usos</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($cupones as $cupon)
                    <tr>
                        <td><strong>{{ $cupon->codigo }}</strong></td>
                        <td>{{ $cupon->tipo ?: $cupon->tipo_descuento }}</td>
                        <td>{{ $cupon->valor }}{{ ($cupon->tipo ?: $cupon->tipo_descuento) === 'PORCENTAJE' ? '%' : ' COP' }}</td>
                        <td>{{ $cupon->plan?->nombre ?: 'Todos' }}</td>
                        <td>{{ optional($cupon->fecha_inicio)->format('d/m/Y') }} – {{ optional($cupon->fecha_fin)->format('d/m/Y') }}</td>
                        <td>{{ $cupon->usos_actuales }}/{{ $cupon->usos_maximos ?: $cupon->uso_maximo ?: '∞' }}</td>
                        <td><span class="ad-badge {{ ($cupon->estado ?: $cupon->estado_cupon) === 'ACTIVO' ? 'ad-badge-ok' : 'ad-badge-bad' }}">{{ $cupon->estado ?: $cupon->estado_cupon }}</span></td>
                        <td>
                            <a class="ad-link" href="{{ route('admin.cupones.usos', $cupon->id_descuento) }}">Usos</a>
                            <button class="ad-btn ad-btn-ghost" type="button"
                                data-open-cupon-edit
                                data-id="{{ $cupon->id_descuento }}"
                                data-valor="{{ $cupon->valor }}"
                                data-inicio="{{ optional($cupon->fecha_inicio)->format('Y-m-d\\TH:i') }}"
                                data-fin="{{ optional($cupon->fecha_fin)->format('Y-m-d\\TH:i') }}"
                                data-usos="{{ $cupon->usos_maximos ?: $cupon->uso_maximo }}"
                                data-estado="{{ $cupon->estado ?: $cupon->estado_cupon }}">Editar</button>
                            @if (($cupon->estado ?: $cupon->estado_cupon) === 'ACTIVO')
                                <form method="POST" action="{{ route('admin.cupones.destroy', $cupon->id_descuento) }}" style="display:inline" onsubmit="return confirm('¿Desactivar este cupón?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="ad-btn ad-btn-ghost" type="submit">Desactivar</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="ad-empty-hero">
                                <span class="ad-empty-orb">@include('partials.admin-icon', ['icon' => 'planes'])</span>
                                <strong>Sin cupones</strong>
                                <button class="ad-btn" type="button" data-open-cupon-form>Crear cupón</button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <div class="ad-panel__foot" style="justify-content:center">
            {{ $cupones->links() }}
        </div>
    </x-admin.panel>

    <div class="ad-modal" data-cupon-modal hidden>
        <div class="ad-modal-card">
            <header class="ad-modal-head">
                <div><h2>Nuevo cupón</h2></div>
                <button type="button" class="ad-modal-close" data-close-cupon-form aria-label="Cerrar">×</button>
            </header>
            <form class="ad-form" method="POST" action="{{ route('admin.cupones.store') }}">
                @csrf
                <div class="ad-form-row">
                    <label><span>Código *</span><input type="text" name="codigo" required></label>
                    <label><span>Tipo *</span>
                        <select name="tipo" required>
                            <option value="PORCENTAJE">PORCENTAJE</option>
                            <option value="MONTO">MONTO</option>
                        </select>
                    </label>
                </div>
                <div class="ad-form-row">
                    <label><span>Valor *</span><input type="number" step="0.01" name="valor" required></label>
                    <label><span>Plan (opcional)</span>
                        <select name="id_plan">
                            <option value="">Todos</option>
                            @foreach ($planes as $plan)
                                <option value="{{ $plan->id_plan }}">{{ $plan->nombre }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
                <div class="ad-form-row">
                    <label><span>Inicio *</span><input type="datetime-local" name="fecha_inicio" required></label>
                    <label><span>Fin *</span><input type="datetime-local" name="fecha_fin" required></label>
                </div>
                <div class="ad-form-row">
                    <label><span>Usos máximos</span><input type="number" name="usos_maximos" min="1"></label>
                    <label><span>Estado *</span>
                        <select name="estado" required>
                            <option value="ACTIVO">ACTIVO</option>
                            <option value="INACTIVO">INACTIVO</option>
                        </select>
                    </label>
                </div>
                <div class="ad-modal-actions">
                    <button class="ad-btn ad-btn-ghost" type="button" data-close-cupon-form>Cancelar</button>
                    <button class="ad-btn" type="submit">Crear</button>
                </div>
            </form>
        </div>
    </div>

    <div class="ad-modal" data-cupon-edit-modal hidden>
        <div class="ad-modal-card">
            <header class="ad-modal-head">
                <div><h2>Editar cupón</h2></div>
                <button type="button" class="ad-modal-close" data-close-cupon-edit aria-label="Cerrar">×</button>
            </header>
            <form class="ad-form" method="POST" data-cupon-edit-form>
                @csrf
                @method('PUT')
                <div class="ad-form-row">
                    <label><span>Valor *</span><input type="number" step="0.01" name="valor" data-field="valor" required></label>
                    <label><span>Usos máximos</span><input type="number" name="usos_maximos" data-field="usos" min="1"></label>
                </div>
                <div class="ad-form-row">
                    <label><span>Inicio *</span><input type="datetime-local" name="fecha_inicio" data-field="inicio" required></label>
                    <label><span>Fin *</span><input type="datetime-local" name="fecha_fin" data-field="fin" required></label>
                </div>
                <label><span>Estado *</span>
                    <select name="estado" data-field="estado" required>
                        <option value="ACTIVO">ACTIVO</option>
                        <option value="INACTIVO">INACTIVO</option>
                    </select>
                </label>
                <div class="ad-modal-actions">
                    <button class="ad-btn ad-btn-ghost" type="button" data-close-cupon-edit>Cancelar</button>
                    <button class="ad-btn" type="submit">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            var modal = document.querySelector('[data-cupon-modal]');
            function open() { if (modal) { modal.hidden = false; modal.classList.add('is-open'); } }
            function close() { if (modal) { modal.hidden = true; modal.classList.remove('is-open'); } }
            document.querySelector('[data-open-cupon-form]')?.addEventListener('click', open);
            document.querySelectorAll('[data-close-cupon-form]').forEach(function (b) { b.addEventListener('click', close); });
            modal?.addEventListener('click', function (e) { if (e.target === modal) close(); });

            var editModal = document.querySelector('[data-cupon-edit-modal]');
            var editForm = document.querySelector('[data-cupon-edit-form]');
            function openEdit(btn) {
                if (!editModal || !editForm) return;
                editForm.action = '{{ url('admin/cupones') }}/' + btn.getAttribute('data-id');
                editForm.querySelector('[data-field="valor"]').value = btn.getAttribute('data-valor') || '';
                editForm.querySelector('[data-field="inicio"]').value = btn.getAttribute('data-inicio') || '';
                editForm.querySelector('[data-field="fin"]').value = btn.getAttribute('data-fin') || '';
                editForm.querySelector('[data-field="usos"]').value = btn.getAttribute('data-usos') || '';
                editForm.querySelector('[data-field="estado"]').value = btn.getAttribute('data-estado') || 'ACTIVO';
                editModal.hidden = false;
                editModal.classList.add('is-open');
            }
            function closeEdit() { if (editModal) { editModal.hidden = true; editModal.classList.remove('is-open'); } }
            document.querySelectorAll('[data-open-cupon-edit]').forEach(function (b) { b.addEventListener('click', function () { openEdit(b); }); });
            document.querySelectorAll('[data-close-cupon-edit]').forEach(function (b) { b.addEventListener('click', closeEdit); });
            editModal?.addEventListener('click', function (e) { if (e.target === editModal) closeEdit(); });
        })();
    </script>
@endsection
