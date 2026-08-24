@php
    $coaches = $coaches ?? collect();
@endphp
<div class="ad-modal" data-cliente-edit-modal hidden>
    <div class="ad-modal-card ad-cli-edit-modal" role="dialog" aria-modal="true" aria-labelledby="ad-cli-edit-title">
        <form class="ad-cli-edit-shell" method="POST" data-cli-edit-form>
            @csrf
            @method('PUT')

            <header class="ad-cli-edit-head">
                <div class="ad-cli-edit-hero">
                    <div class="ad-cli-edit-avatar" data-cli-edit-avatar></div>
                    <div class="ad-cli-edit-who">
                        <h2 id="ad-cli-edit-title" data-cli-edit-nombre>Editar cliente</h2>
                        <p data-cli-edit-correo>—</p>
                        <div class="ad-cli-edit-badges" data-cli-edit-badges></div>
                    </div>
                </div>
                <button type="button" class="ad-modal-close" data-close-cliente-edit aria-label="Cerrar">×</button>
            </header>

            <div class="ad-cli-edit-body">
                <section class="ad-cli-edit-section">
                    <header class="ad-cli-edit-section-head">
                        <div class="ad-cli-edit-section-title">
                            <span class="ad-cli-edit-section-ico is-pink" aria-hidden="true">@include('partials.admin-icon', ['icon' => 'personas'])</span>
                            <h3>Perfil</h3>
                        </div>
                        <span>Datos de la ficha</span>
                    </header>
                    <div class="ad-cli-edit-grid">
                        <label>
                            <span>Ciudad</span>
                            <input type="text" name="ciudad" data-cli-edit-ciudad placeholder="Ej. Neiva" autocomplete="address-level2">
                        </label>
                        <label>
                            <span>Teléfono</span>
                            <input type="text" name="telefono" data-cli-edit-telefono placeholder="Ej. 3001234567" autocomplete="tel">
                        </label>
                        <label>
                            <span>Objetivo</span>
                            <input type="text" name="objetivo_principal" data-cli-edit-objetivo placeholder="Meta del cliente">
                        </label>
                        <label>
                            <span>Estado de cuenta</span>
                            <select name="estado_cliente" data-cli-edit-estado required>
                                @foreach (['PROSPECTO','PENDIENTE_PAGO','ACTIVO','ACTIVA','INACTIVO','SUSPENDIDO'] as $e)
                                    <option value="{{ $e }}">{{ $e }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                </section>

                <section class="ad-cli-edit-section">
                    <header class="ad-cli-edit-section-head">
                        <div class="ad-cli-edit-section-title">
                            <span class="ad-cli-edit-section-ico is-cyan" aria-hidden="true">@include('partials.admin-icon', ['icon' => 'coaches'])</span>
                            <h3>Coach</h3>
                        </div>
                        <span>Acompañamiento</span>
                    </header>
                    <div class="ad-cli-edit-grid">
                        <label>
                            <span>Coach asignado</span>
                            <select name="id_coach" data-cli-edit-coach @if ($coaches->isEmpty()) disabled @endif>
                                <option value="">Sin asignar</option>
                                @foreach ($coaches as $coach)
                                    <option value="{{ $coach->id_coach }}">
                                        {{ $coach->usuario?->nombres }} {{ $coach->usuario?->apellidos }} — {{ $coach->especialidad }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            <span>Motivo del cambio</span>
                            <input type="text" name="motivo" data-cli-edit-motivo placeholder="Opcional">
                        </label>
                    </div>
                </section>

                <section class="ad-cli-edit-section">
                    <header class="ad-cli-edit-section-head">
                        <div class="ad-cli-edit-section-title">
                            <span class="ad-cli-edit-section-ico is-pink" aria-hidden="true">@include('partials.admin-icon', ['icon' => 'planes'])</span>
                            <h3>Accesos</h3>
                        </div>
                        <span data-cli-edit-accesos-count>0 módulo(s)</span>
                    </header>
                    <div class="ad-cli-edit-fecha-bar" data-cli-edit-fecha-bar hidden>
                        <label>
                            <span>Fecha fin (todos los módulos)</span>
                            <input type="date" name="fecha_fin_accesos" data-cli-edit-fecha-fin>
                        </label>
                        <p class="ad-cli-edit-fecha-hint">Se aplica la misma fecha de vencimiento a todos los accesos del cliente.</p>
                    </div>
                    <div class="ad-cli-edit-accesos" data-cli-edit-accesos></div>
                </section>
            </div>

            <footer class="ad-cli-edit-foot">
                <button type="submit" class="ad-btn ad-cli-edit-btn-save" data-cli-edit-submit>Guardar Cambios</button>
                <button type="button" class="ad-btn ad-cli-edit-btn-close" data-close-cliente-edit>Cerrar</button>
            </footer>
        </form>
    </div>
</div>

<script>
    (function () {
        var modal = document.querySelector('[data-cliente-edit-modal]');
        if (!modal) return;

        var form = modal.querySelector('[data-cli-edit-form]');
        var avatarEl = modal.querySelector('[data-cli-edit-avatar]');
        var badgesEl = modal.querySelector('[data-cli-edit-badges]');
        var accesosBox = modal.querySelector('[data-cli-edit-accesos]');
        var accesosCount = modal.querySelector('[data-cli-edit-accesos-count]');
        var fechaBar = modal.querySelector('[data-cli-edit-fecha-bar]');
        var fechaFinInput = modal.querySelector('[data-cli-edit-fecha-fin]');
        var submitBtn = modal.querySelector('[data-cli-edit-submit]');

        function badgeClass(estado) {
            if (estado === 'ACTIVO' || estado === 'ACTIVA' || estado === 'PRESENCIAL') return 'ad-badge-ok';
            return 'ad-badge-pendiente';
        }

        function renderAvatar(foto, iniciales) {
            if (!avatarEl) return;
            if (foto) {
                avatarEl.innerHTML = '<img class="ad-user-avatar" src="' + String(foto).replace(/"/g, '&quot;') + '" alt="">';
            } else {
                avatarEl.innerHTML = '<span class="ad-user-avatar is-initials">' + (iniciales || 'FF') + '</span>';
            }
        }

        function renderBadges(modalidad, estado, tipo) {
            if (!badgesEl) return;
            var html = '';
            if (modalidad) html += '<span class="ad-badge ' + badgeClass(modalidad) + '">' + modalidad + '</span>';
            if (estado) html += '<span class="ad-badge ' + badgeClass(estado) + '">' + estado + '</span>';
            if (tipo) html += '<span class="ad-badge ad-badge-bad">' + tipo + '</span>';
            badgesEl.innerHTML = html;
        }

        function pickSharedFecha(items) {
            var counts = {};
            var first = '';
            items.forEach(function (a) {
                var f = (a.fecha_fin || '').trim();
                if (!f) return;
                if (!first) first = f;
                counts[f] = (counts[f] || 0) + 1;
            });
            var best = first;
            var bestN = 0;
            Object.keys(counts).forEach(function (k) {
                if (counts[k] > bestN) {
                    bestN = counts[k];
                    best = k;
                }
            });
            return best || '';
        }

        function renderAccesos(raw) {
            var items = [];
            try { items = JSON.parse(raw || '[]'); } catch (e) { items = []; }
            if (!Array.isArray(items)) items = [];
            if (accesosCount) accesosCount.textContent = items.length + ' módulo(s)';
            if (!accesosBox) return;

            if (!items.length) {
                accesosBox.innerHTML = '<p class="ad-cli-edit-empty">Sin módulos asignados en este plan.</p>';
                if (fechaBar) fechaBar.hidden = true;
                if (fechaFinInput) fechaFinInput.value = '';
                return;
            }

            if (fechaBar) fechaBar.hidden = false;
            if (fechaFinInput) fechaFinInput.value = pickSharedFecha(items);

            accesosBox.innerHTML = items.map(function (a) {
                var estados = ['ACTIVO', 'SUSPENDIDO', 'VENCIDO', 'INACTIVO'];
                var opts = estados.map(function (e) {
                    return '<option value="' + e + '"' + (a.estado === e ? ' selected' : '') + '>' + e + '</option>';
                }).join('');
                var id = a.id || a.id_acceso || '';
                return '<div class="ad-cli-edit-access">' +
                    '<div class="ad-cli-edit-access-head">' +
                    '<strong>' + (a.modulo || '—') + '</strong>' +
                    '</div>' +
                    '<div class="ad-cli-edit-access-grid">' +
                    '<label><span>Estado</span><select name="accesos[' + id + '][estado_acceso]" required>' + opts + '</select></label>' +
                    '</div></div>';
            }).join('');
        }

        function openEdit(btn) {
            var nombre = btn.getAttribute('data-nombre') || 'Cliente';
            var correo = btn.getAttribute('data-correo') || '—';
            modal.querySelector('[data-cli-edit-nombre]').textContent = nombre;
            modal.querySelector('[data-cli-edit-correo]').textContent = correo;

            if (form) {
                form.action = btn.getAttribute('data-completo-url')
                    || btn.getAttribute('data-update-url')
                    || '#';
                var ciudad = form.querySelector('[data-cli-edit-ciudad]');
                var telefono = form.querySelector('[data-cli-edit-telefono]');
                var objetivo = form.querySelector('[data-cli-edit-objetivo]');
                var estado = form.querySelector('[data-cli-edit-estado]');
                var coachSel = form.querySelector('[data-cli-edit-coach]');
                var motivo = form.querySelector('[data-cli-edit-motivo]');
                if (ciudad) ciudad.value = btn.getAttribute('data-ciudad') || '';
                if (telefono) telefono.value = btn.getAttribute('data-telefono') || '';
                if (objetivo) objetivo.value = btn.getAttribute('data-objetivo') || '';
                if (estado) estado.value = btn.getAttribute('data-estado') || 'ACTIVO';
                if (coachSel) coachSel.value = btn.getAttribute('data-coach-id') || '';
                if (motivo) motivo.value = '';
            }

            renderAvatar(btn.getAttribute('data-foto') || '', btn.getAttribute('data-iniciales') || 'FF');
            renderBadges(
                btn.getAttribute('data-modalidad') || '',
                btn.getAttribute('data-estado') || '',
                btn.getAttribute('data-tipo') || ''
            );
            renderAccesos(btn.getAttribute('data-accesos'));

            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Guardar Cambios';
            }

            modal.hidden = false;
            modal.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }

        function closeEdit() {
            modal.hidden = true;
            modal.classList.remove('is-open');
            document.body.style.overflow = '';
        }

        window.__openClienteEdit = openEdit;

        document.querySelectorAll('[data-open-cliente-edit]').forEach(function (btn) {
            btn.addEventListener('click', function () { openEdit(btn); });
        });
        modal.querySelectorAll('[data-close-cliente-edit]').forEach(function (b) {
            b.addEventListener('click', closeEdit);
        });
        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeEdit();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal.hasAttribute('hidden')) closeEdit();
        });

        if (form) {
            form.addEventListener('submit', function () {
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Guardando…';
                }
            });
        }
    })();
</script>
