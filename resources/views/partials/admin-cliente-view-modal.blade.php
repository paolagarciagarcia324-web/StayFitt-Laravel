<div class="ad-modal" data-cliente-view-modal hidden>
    <div class="ad-modal-card ad-cli-view-modal" role="dialog" aria-modal="true" aria-labelledby="ad-cli-view-title">
        <header class="ad-cli-view-head">
            <div class="ad-cli-view-hero">
                <div class="ad-cli-view-avatar" data-cli-view-avatar></div>
                <div class="ad-cli-view-who">
                    <h2 id="ad-cli-view-title" data-cli-view-nombre>Cliente</h2>
                    <p data-cli-view-correo>—</p>
                    <div class="ad-cli-view-badges" data-cli-view-badges></div>
                </div>
            </div>
            <button type="button" class="ad-modal-close" data-close-cliente-view aria-label="Cerrar">×</button>
        </header>

        <div class="ad-cli-view-body">
            <div class="ad-cli-view-grid">
                <article class="ad-cli-view-stat">
                    <em>Teléfono</em>
                    <strong data-cli-view-telefono>—</strong>
                </article>
                <article class="ad-cli-view-stat">
                    <em>Ciudad</em>
                    <strong data-cli-view-ciudad>—</strong>
                </article>
                <article class="ad-cli-view-stat">
                    <em>Objetivo</em>
                    <strong data-cli-view-objetivo>—</strong>
                </article>
                <article class="ad-cli-view-stat">
                    <em>Plan</em>
                    <strong data-cli-view-plan>—</strong>
                </article>
                <article class="ad-cli-view-stat">
                    <em>Racha</em>
                    <strong data-cli-view-racha>—</strong>
                </article>
                <article class="ad-cli-view-stat">
                    <em>Días de plan</em>
                    <strong data-cli-view-dias>—</strong>
                </article>
                <article class="ad-cli-view-stat">
                    <em data-cli-view-extra-label>Extra</em>
                    <strong data-cli-view-extra-value>—</strong>
                </article>
                <article class="ad-cli-view-stat" data-cli-view-nacimiento-wrap hidden>
                    <em>Nacimiento</em>
                    <strong data-cli-view-nacimiento>—</strong>
                </article>
                <article class="ad-cli-view-stat">
                    <em>Coach</em>
                    <strong data-cli-view-coach>—</strong>
                </article>
            </div>

            <section class="ad-cli-view-accesos">
                <header>
                    <h3>Accesos</h3>
                    <span data-cli-view-accesos-count>0</span>
                </header>
                <div class="ad-cli-view-accesos-list" data-cli-view-accesos-list></div>
            </section>
        </div>

        <footer class="ad-cli-view-foot">
            <button type="button" class="ad-btn ad-btn-ghost" data-close-cliente-view>Cerrar</button>
            <button type="button" class="ad-btn" data-cli-view-to-edit>Editar</button>
        </footer>
    </div>
</div>

<script>
    (function () {
        var modal = document.querySelector('[data-cliente-view-modal]');
        if (!modal) return;

        var avatarEl = modal.querySelector('[data-cli-view-avatar]');
        var badgesEl = modal.querySelector('[data-cli-view-badges]');
        var nacWrap = modal.querySelector('[data-cli-view-nacimiento-wrap]');
        var accesosList = modal.querySelector('[data-cli-view-accesos-list]');
        var accesosCount = modal.querySelector('[data-cli-view-accesos-count]');
        var toEditBtn = modal.querySelector('[data-cli-view-to-edit]');
        var lastViewBtn = null;

        function setText(sel, value) {
            var el = modal.querySelector(sel);
            if (el) el.textContent = value || '—';
        }

        function badgeClass(estado) {
            if (estado === 'ACTIVO' || estado === 'ACTIVA') return 'ad-badge-ok';
            if (estado === 'PRESENCIAL') return 'ad-badge-ok';
            return 'ad-badge-pendiente';
        }

        function renderAvatar(foto, iniciales) {
            if (!avatarEl) return;
            if (foto) {
                avatarEl.innerHTML = '<img class="ad-user-avatar" src="' + foto.replace(/"/g, '&quot;') + '" alt="">';
            } else {
                avatarEl.innerHTML = '<span class="ad-user-avatar is-initials">' + (iniciales || 'FF') + '</span>';
            }
        }

        function renderBadges(modalidad, estado, tipo) {
            if (!badgesEl) return;
            var html = '';
            if (modalidad) {
                html += '<span class="ad-badge ' + badgeClass(modalidad) + '">' + modalidad + '</span>';
            }
            if (estado) {
                html += '<span class="ad-badge ' + badgeClass(estado) + '">' + estado + '</span>';
            }
            if (tipo) {
                html += '<span class="ad-badge ad-badge-bad">' + tipo + '</span>';
            }
            badgesEl.innerHTML = html;
        }

        function renderAccesos(raw) {
            var items = [];
            try { items = JSON.parse(raw || '[]'); } catch (e) { items = []; }
            if (!Array.isArray(items)) items = [];
            if (accesosCount) accesosCount.textContent = items.length + ' módulo(s)';
            if (!accesosList) return;
            if (!items.length) {
                accesosList.innerHTML = '<p class="ad-cli-view-empty">Sin módulos asignados.</p>';
                return;
            }
            accesosList.innerHTML = items.map(function (a) {
                var estado = a.estado || '—';
                var cls = badgeClass(estado);
                return '<div class="ad-cli-view-access">' +
                    '<div><strong>' + (a.modulo || '—') + '</strong><em>' + (a.vigencia || '—') + '</em></div>' +
                    '<span class="ad-badge ' + cls + '">' + estado + '</span>' +
                    '</div>';
            }).join('');
        }

        function openView(btn) {
            lastViewBtn = btn;
            setText('[data-cli-view-nombre]', btn.getAttribute('data-nombre'));
            setText('[data-cli-view-correo]', btn.getAttribute('data-correo'));
            setText('[data-cli-view-telefono]', btn.getAttribute('data-telefono'));
            setText('[data-cli-view-ciudad]', btn.getAttribute('data-ciudad'));
            setText('[data-cli-view-objetivo]', btn.getAttribute('data-objetivo'));
            setText('[data-cli-view-plan]', btn.getAttribute('data-plan'));
            setText('[data-cli-view-racha]', btn.getAttribute('data-racha'));
            setText('[data-cli-view-dias]', btn.getAttribute('data-dias'));
            setText('[data-cli-view-extra-label]', btn.getAttribute('data-extra-label'));
            setText('[data-cli-view-extra-value]', btn.getAttribute('data-extra-value'));
            setText('[data-cli-view-coach]', btn.getAttribute('data-coach'));

            var nac = (btn.getAttribute('data-nacimiento') || '').trim();
            if (nacWrap) {
                if (nac) {
                    nacWrap.hidden = false;
                    setText('[data-cli-view-nacimiento]', nac);
                } else {
                    nacWrap.hidden = true;
                }
            }

            renderAvatar(btn.getAttribute('data-foto') || '', btn.getAttribute('data-iniciales') || 'FF');
            renderBadges(
                btn.getAttribute('data-modalidad') || '',
                btn.getAttribute('data-estado') || '',
                btn.getAttribute('data-tipo') || ''
            );
            renderAccesos(btn.getAttribute('data-accesos'));

            modal.hidden = false;
            modal.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }

        function closeView() {
            modal.hidden = true;
            modal.classList.remove('is-open');
            document.body.style.overflow = '';
        }

        document.querySelectorAll('[data-open-cliente-view]').forEach(function (btn) {
            btn.addEventListener('click', function () { openView(btn); });
        });
        toEditBtn?.addEventListener('click', function () {
            if (!lastViewBtn) return;
            closeView();
            if (typeof window.__openClienteEdit === 'function') {
                window.__openClienteEdit(lastViewBtn);
            } else {
                var id = lastViewBtn.getAttribute('data-id');
                var editBtn = id
                    ? document.querySelector('[data-open-cliente-edit][data-id="' + id + '"]')
                    : null;
                if (editBtn) editBtn.click();
            }
        });
        modal.querySelectorAll('[data-close-cliente-view]').forEach(function (b) {
            b.addEventListener('click', closeView);
        });
        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeView();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal.hasAttribute('hidden')) closeView();
        });
    })();
</script>
