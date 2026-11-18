@extends('layouts.admin')

@section('title', 'Planes')
@section('subtitle')
    <span class="ad-page-intro">
        <span class="ad-page-intro-lead">Catálogo comercial</span>
        <span class="ad-page-intro-note">Precios, cupos, modalidad y módulos incluidos.</span>
    </span>
@endsection

@section('content')
    @php
        $plansData = $planes->mapWithKeys(function ($plan) {
            return [$plan->slug => [
                'id' => $plan->id_plan,
                'slug' => $plan->slug,
                'codigo' => $plan->codigo,
                'nombre' => $plan->nombre,
                'descripcion' => $plan->descripcion,
                'imagen_url' => $plan->imagen_url,
                'imagen_src' => $plan->imagenSrc(),
                'inclusiones' => $plan->inclusiones,
                'beneficios' => $plan->beneficios,
                'caracteristicas' => collect($plan->caracteristicas_json ?? [])->implode("\n"),
                'precio' => $plan->precio,
                'moneda' => $plan->moneda,
                'duracion_dias' => $plan->duracion_dias,
                'dias_gracia' => $plan->dias_gracia,
                'modalidad' => $plan->modalidad,
                'tipo_cliente' => $plan->tipo_cliente,
                'cupos_totales' => $plan->cupos_totales,
                'cupo_maximo' => $plan->cupo_maximo,
                'cupos_reservados' => $plan->cupos_reservados,
                'cupos_ocupados' => $plan->cupos_ocupados,
                'orden' => $plan->orden,
                'etiqueta' => $plan->etiqueta,
                'estado_plan' => $plan->estado_plan,
                'requiere_coach' => (bool) $plan->requiere_coach,
                'renovacion_permitida' => (bool) $plan->renovacion_permitida,
                'renovacion_automatica' => (bool) $plan->renovacion_automatica,
                'popular' => (bool) $plan->popular,
                'destacado' => (bool) $plan->destacado,
                'modulos' => $plan->modulos->pluck('id_modulo')->values()->all(),
            ]];
        });
    @endphp

    <section class="ad-card ad-users ad-plan-board" data-plan-board>
        <div class="ad-users-bar">
            @include('partials.admin-search', [
                'action' => route('admin.planes.index'),
                'value' => $q,
                'placeholder' => 'Buscar plan por nombre, código o modalidad',
                'live' => true,
            ])
            <button class="ad-btn ad-btn-add" type="button" data-open-plan-form>+ Crear plan</button>
        </div>

        <div class="ad-carousel" data-carousel>
            <button class="ad-carousel-btn" type="button" data-carousel-dir="prev" aria-label="Anterior">‹</button>
        <div class="ad-catalog">
            @forelse ($planes as $plan)
                @php
                    $searchBlob = mb_strtolower(trim(
                        ($plan->nombre ?? '').' '.
                        ($plan->codigo ?? '').' '.
                        ($plan->slug ?? '').' '.
                        ($plan->modalidad ?? '').' '.
                        ($plan->estado_plan ?? '').' '.
                        ($plan->etiqueta ?? '')
                    ));
                @endphp
                <article class="ad-catalog-card" data-plan-row data-plan-slug="{{ $plan->slug }}" data-inst-card data-search="{{ $searchBlob }}">
                    <div class="ad-cat-cover">
                        <span style="position:absolute;left:16px;top:14px;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase">{{ $plan->modalidad }}</span>
                    </div>
                    <div class="ad-cat-photo">
                        @if ($plan->imagenSrc())
                            <img src="{{ $plan->imagenSrc() }}" alt="{{ $plan->nombre }}">
                        @else
                            @include('partials.admin-icon', ['icon' => 'planes'])
                        @endif
                    </div>
                    <div class="ad-cat-body">
                        <strong>{{ $plan->nombre }}</strong>
                        <em class="ad-user-mail">{{ $plan->descripcion ?: ($plan->codigo ?: $plan->slug) }}</em>
                        <span class="ad-cat-price">{{ $plan->precioFormateado() }}</span>
                        <span class="ad-user-mail">{{ (int) $plan->cupos_ocupados }}/{{ (int) $plan->cupos_totales }} cupos · {{ $plan->modulos->count() }} módulos</span>
                        <div class="ad-people-actions">
                            <button class="ad-btn" type="button" data-open-plan-edit>Editar plan</button>
                            <button class="ad-btn ad-btn-ghost" type="button" data-open-plan-delete>Quitar</button>
                        </div>
                    </div>
                </article>
            @empty
                <p class="ad-empty">No hay planes en el catálogo.</p>
            @endforelse
        </div>
            <button class="ad-carousel-btn" type="button" data-carousel-dir="next" aria-label="Siguiente">›</button>
        </div>
        <p class="ad-live-search-empty" data-live-search-empty hidden>Ningún plan coincide con esa búsqueda.</p>
    </section>

    @include('admin.planes._form-modal', ['plan' => new \App\Models\Plan, 'modulos' => $modulos])

    <script type="application/json" id="ad-plans-data">@json($plansData)</script>

    <div class="ad-modal" data-plan-delete-modal hidden>
        <div class="ad-modal-card" role="dialog" aria-modal="true" aria-labelledby="ad-plan-del-title">
            <header class="ad-modal-head">
                <div>
                    <h2 id="ad-plan-del-title">Eliminar plan</h2>
                    <p>Si tiene clientes activos, se desactivará del catálogo en lugar de borrarse.</p>
                </div>
                <button type="button" class="ad-modal-close" data-close-plan-delete aria-label="Cerrar">×</button>
            </header>
            <p class="ad-plan-delete-msg">¿Confirmas eliminar <strong data-plan-delete-name>—</strong>?</p>
            <form method="POST" data-plan-delete-form>
                @csrf
                @method('DELETE')
                <div class="ad-modal-actions">
                    <button type="button" class="ad-btn ad-btn-ghost" data-close-plan-delete>Cancelar</button>
                    <button type="submit" class="ad-btn is-danger">Sí, eliminar</button>
                </div>
            </form>
        </div>
    </div>

    @php $ok = session('plan_ok'); @endphp
    @if (is_array($ok) && ! empty($ok['accion']))
        @php
            $accionLabel = match ($ok['accion'] ?? '') {
                'creado' => 'Plan creado',
                'actualizado' => 'Plan actualizado',
                'eliminado' => 'Plan eliminado',
                'desactivado' => 'Plan desactivado',
                default => 'Acción completada',
            };
            $detalle = match ($ok['accion'] ?? '') {
                'creado' => 'El plan quedó listo en el catálogo comercial.',
                'actualizado' => 'Los cambios de precio, cupos y módulos se guardaron correctamente.',
                'eliminado' => 'El plan ya no aparece en el listado.',
                'desactivado' => 'El plan tiene clientes vinculados; quedó inactivo en el catálogo.',
                default => 'La operación terminó correctamente.',
            };
        @endphp
        <div class="ad-modal ad-ok-modal is-open" data-ok-modal role="dialog" aria-modal="true" aria-labelledby="ad-ok-title">
            <div class="ad-modal-card ad-ok-card">
                <div class="ad-ok-confetti" aria-hidden="true">
                    <i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i>
                </div>
                <div class="ad-ok-check" aria-hidden="true">
                    <svg viewBox="0 0 52 52" width="56" height="56">
                        <circle cx="26" cy="26" r="24" fill="none"/>
                        <path fill="none" d="M14 27l8 8 16-16"/>
                    </svg>
                </div>
                <h2 id="ad-ok-title">{{ $accionLabel }}</h2>
                <p class="ad-ok-lead">fue un éxito</p>
                <p class="ad-muted-note">{{ $detalle }}</p>
                <button type="button" class="ad-btn" data-close-ok>Continuar</button>
            </div>
        </div>
    @endif

    <script>
    (function () {
        var modal = document.querySelector('[data-plan-modal]');
        var form = document.querySelector('[data-plan-form]');
        var deleteModal = document.querySelector('[data-plan-delete-modal]');
        var deleteForm = document.querySelector('[data-plan-delete-form]');
        var board = document.querySelector('[data-plan-board]');
        var storeUrl = @json(route('admin.planes.store'));
        var updateBase = @json(url('admin/planes'));
        var plansIndex = @json(route('admin.planes.index'));
        var editSlugFromQuery = @json(request('edit', ''));
        var plansData = {};

        try {
            var plansNode = document.getElementById('ad-plans-data');
            if (plansNode && plansNode.textContent) {
                plansData = JSON.parse(plansNode.textContent);
            }
        } catch (err) {
            plansData = {};
        }

        function setBodyLock(on) {
            document.body.style.overflow = on ? 'hidden' : '';
        }

        function showPreview(src) {
            if (!modal) return;
            var img = modal.querySelector('[data-plan-preview-img]');
            var empty = modal.querySelector('[data-plan-preview-empty]');
            if (!img) return;
            if (src) {
                img.src = src;
                img.hidden = false;
                if (empty) empty.hidden = true;
            } else {
                img.removeAttribute('src');
                img.hidden = true;
                if (empty) empty.hidden = false;
            }
        }

        function wirePreview() {
            if (!modal) return;
            var file = modal.querySelector('[data-plan-preview-file]');
            var url = modal.querySelector('[data-plan-preview-url]');
            if (file) {
                file.onchange = function () {
                    var f = file.files && file.files[0];
                    if (!f) return;
                    var r = new FileReader();
                    r.onload = function (e) { showPreview(e.target.result); };
                    r.readAsDataURL(f);
                };
            }
            if (url) {
                url.oninput = function () {
                    if (file && file.files && file.files.length) return;
                    showPreview(url.value.trim());
                };
            }
        }

        function setMethodPut(on) {
            if (!form) return;
            var holder = form.querySelector('[data-plan-method]');
            if (!holder) return;
            holder.innerHTML = on ? '<input type="hidden" name="_method" value="PUT">' : '';
        }

        function fillForm(plan) {
            if (!form || !plan) return;
            var fields = ['codigo','nombre','imagen_url','precio','moneda','duracion_dias','dias_gracia','modalidad','tipo_cliente','cupos_totales','cupo_maximo','orden','estado_plan','etiqueta','descripcion','inclusiones','beneficios','caracteristicas'];
            fields.forEach(function (name) {
                var el = form.querySelector('[data-field="' + name + '"]');
                if (!el) return;
                var val = plan[name];
                el.value = val === null || val === undefined ? '' : val;
            });

            ['requiere_coach','renovacion_permitida','renovacion_automatica','popular','destacado'].forEach(function (name) {
                var el = form.querySelector('[data-field="' + name + '"]');
                if (el) el.checked = !!plan[name];
            });

            form.querySelectorAll('[data-modulo]').forEach(function (cb) {
                var id = parseInt(cb.getAttribute('data-modulo'), 10);
                cb.checked = (plan.modulos || []).some(function (m) {
                    return parseInt(m, 10) === id;
                });
            });

            var readonly = form.querySelector('[data-plan-cupos-readonly]');
            if (readonly) {
                readonly.hidden = false;
                var res = readonly.querySelector('[data-field="cupos_reservados"]');
                var occ = readonly.querySelector('[data-field="cupos_ocupados"]');
                if (res) res.value = plan.cupos_reservados || 0;
                if (occ) occ.value = plan.cupos_ocupados || 0;
            }

            showPreview(plan.imagen_src || plan.imagen_url || '');
            var file = form.querySelector('[data-plan-preview-file]');
            if (file) file.value = '';
        }

        function resetForm() {
            if (!form) return;
            form.reset();
            form.action = storeUrl;
            setMethodPut(false);
            var editId = form.querySelector('[data-plan-edit-id]');
            if (editId) editId.value = '';
            var readonly = form.querySelector('[data-plan-cupos-readonly]');
            if (readonly) readonly.hidden = true;
            form.querySelectorAll('[data-modulo]').forEach(function (cb) { cb.checked = false; });
            showPreview('');
            var title = modal.querySelector('[data-plan-modal-title]');
            var subtitle = modal.querySelector('[data-plan-modal-subtitle]');
            var submit = form.querySelector('[data-plan-submit]');
            if (title) title.textContent = 'Nuevo plan';
            if (subtitle) subtitle.textContent = 'Define identidad, precio, cupos y módulos del catálogo.';
            if (submit) submit.textContent = 'Crear plan';
        }

        function openCreate() {
            if (!modal) return;
            resetForm();
            modal.hidden = false;
            modal.classList.add('is-open');
            setBodyLock(true);
        }

        function openEdit(plan) {
            if (!modal || !form || !plan) return;
            resetForm();
            form.action = updateBase + '/' + plan.slug;
            setMethodPut(true);
            var editId = form.querySelector('[data-plan-edit-id]');
            if (editId) editId.value = plan.slug;
            fillForm(plan);
            var title = modal.querySelector('[data-plan-modal-title]');
            var subtitle = modal.querySelector('[data-plan-modal-subtitle]');
            var submit = form.querySelector('[data-plan-submit]');
            if (title) title.textContent = 'Editar plan';
            if (subtitle) {
                subtitle.textContent = plan.nombre
                    ? ('Editando · ' + plan.nombre)
                    : 'Actualiza el plan seleccionado.';
            }
            if (submit) submit.textContent = 'Guardar cambios';
            modal.hidden = false;
            modal.classList.add('is-open');
            setBodyLock(true);
        }

        function closeForm() {
            if (!modal) return;
            modal.hidden = true;
            modal.classList.remove('is-open');
            setBodyLock(false);
            if (window.location.search) {
                window.history.replaceState({}, '', plansIndex);
            }
        }

        function planFromBtn(btn) {
            var row = btn.closest('[data-plan-row]');
            if (!row) return null;
            var slug = row.getAttribute('data-plan-slug');
            if (!slug || !plansData[slug]) return null;
            return plansData[slug];
        }

        var openPlanFormBtn = document.querySelector('[data-open-plan-form]');
        if (openPlanFormBtn) openPlanFormBtn.addEventListener('click', openCreate);
        document.querySelectorAll('[data-close-plan-form]').forEach(function (b) {
            b.addEventListener('click', closeForm);
        });
        if (modal) {
            modal.addEventListener('click', function (e) {
                if (e.target === modal) closeForm();
            });
        }

        if (board) {
            board.addEventListener('click', function (e) {
                var editBtn = e.target.closest('[data-open-plan-edit]');
                if (editBtn) {
                    e.preventDefault();
                    var editPlan = planFromBtn(editBtn);
                    if (editPlan) openEdit(editPlan);
                    return;
                }
                var delBtn = e.target.closest('[data-open-plan-delete]');
                if (delBtn) {
                    e.preventDefault();
                    var delPlan = planFromBtn(delBtn);
                    if (delPlan) openDelete(delPlan);
                }
            });
        }

        function openDelete(plan) {
            if (!deleteModal || !deleteForm || !plan) return;
            deleteForm.action = updateBase + '/' + plan.slug;
            var nameEl = deleteModal.querySelector('[data-plan-delete-name]');
            if (nameEl) nameEl.textContent = plan.nombre || 'este plan';
            deleteModal.hidden = false;
            deleteModal.classList.add('is-open');
            setBodyLock(true);
        }

        function closeDelete() {
            if (!deleteModal) return;
            deleteModal.hidden = true;
            deleteModal.classList.remove('is-open');
            setBodyLock(false);
        }

        document.querySelectorAll('[data-close-plan-delete]').forEach(function (b) {
            b.addEventListener('click', closeDelete);
        });
        if (deleteModal) {
            deleteModal.addEventListener('click', function (e) {
                if (e.target === deleteModal) closeDelete();
            });
        }

        var okModal = document.querySelector('[data-ok-modal]');
        if (okModal) {
            setBodyLock(true);
            function closeOk() {
                okModal.hidden = true;
                okModal.classList.remove('is-open');
                setBodyLock(false);
            }
            okModal.querySelectorAll('[data-close-ok]').forEach(function (b) {
                b.addEventListener('click', closeOk);
            });
            okModal.addEventListener('click', function (e) {
                if (e.target === okModal) closeOk();
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && okModal.classList.contains('is-open')) closeOk();
            });
        }

        wirePreview();

        if (modal && modal.classList.contains('is-open')) {
            setBodyLock(true);
        }

        if (editSlugFromQuery && !@json($errors->any())) {
            var matchPlan = plansData[editSlugFromQuery] || null;
            if (matchPlan) openEdit(matchPlan);
        } else if (@json(request()->filled('create')) && !@json($errors->any())) {
            openCreate();
        } else if (@json($errors->any() && old('_edit'))) {
            var slug = @json(old('_edit', ''));
            form.action = updateBase + '/' + slug;
            setMethodPut(true);
            var readonly = form.querySelector('[data-plan-cupos-readonly]');
            if (readonly) readonly.hidden = false;
            var title = modal.querySelector('[data-plan-modal-title]');
            var subtitle = modal.querySelector('[data-plan-modal-subtitle]');
            var submit = form.querySelector('[data-plan-submit]');
            if (title) title.textContent = 'Editar plan';
            if (subtitle) subtitle.textContent = 'Actualiza el plan seleccionado.';
            if (submit) submit.textContent = 'Guardar cambios';
            wirePreview();
        } else if (@json($errors->any())) {
            wirePreview();
        }

        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;
            if (okModal && okModal.classList.contains('is-open')) return;
            if (deleteModal && deleteModal.classList.contains('is-open')) closeDelete();
            else if (modal && modal.classList.contains('is-open')) closeForm();
        });
    })();
    </script>
@endsection
