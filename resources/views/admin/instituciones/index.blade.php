@extends('layouts.admin')

@section('title', 'Instituciones')
@section('subtitle')
    <span class="ad-page-intro">
        <span class="ad-page-intro-lead">Convenios corporativos</span>
        <span class="ad-page-intro-note">Cupos, miembros vinculados y enlaces de registro.</span>
    </span>
@endsection

@section('actions')
    <button class="ad-btn ad-btn-add" type="button" data-open-inst-form>+ Nueva institución</button>
@endsection

@section('content')
    @php
        $kpis = $kpis ?? ['total' => 0, 'activas' => 0, 'cupos_usados' => 0, 'cupos_totales' => 0, 'enlaces' => 0, 'miembros' => 0];
        $cuposLbl = ($kpis['cupos_totales'] ?? 0) > 0
            ? ($kpis['cupos_usados'].'/'.$kpis['cupos_totales'])
            : (string) ($kpis['cupos_usados'] ?? 0);
    @endphp

    <div class="ad-kpis ad-users-kpis ad-inst-kpis">
        <div class="ad-kpi accent-pink">
            <span class="ad-kpi-ico">@include('partials.admin-icon', ['icon' => 'instituciones'])</span>
            <div class="ad-kpi-copy">
                <strong>{{ $kpis['total'] }}</strong>
                <span>Instituciones</span>
            </div>
        </div>
        <div class="ad-kpi accent-cyan">
            <span class="ad-kpi-ico">@include('partials.admin-icon', ['icon' => 'clientes'])</span>
            <div class="ad-kpi-copy">
                <strong>{{ $kpis['activas'] }}</strong>
                <span>Activas</span>
            </div>
        </div>
        <div class="ad-kpi accent-pink">
            <span class="ad-kpi-ico">@include('partials.admin-icon', ['icon' => 'cupo'])</span>
            <div class="ad-kpi-copy">
                <strong>{{ $cuposLbl }}</strong>
                <span>Cupos</span>
            </div>
        </div>
        <div class="ad-kpi accent-cyan">
            <span class="ad-kpi-ico">@include('partials.admin-icon', ['icon' => 'personas'])</span>
            <div class="ad-kpi-copy">
                <strong>{{ $kpis['miembros'] }}</strong>
                <span>Miembros</span>
            </div>
        </div>
    </div>

    <section class="ad-card ad-inst-board">
        <div class="ad-inst-toolbar">
            @include('partials.admin-search', [
                'action' => route('admin.instituciones.index'),
                'value' => $q,
                'placeholder' => 'Buscar institución o cliente',
            ])
            <div class="ad-inst-filters" role="tablist" aria-label="Filtrar por estado">
                <button type="button" class="ad-inst-filter is-active" data-inst-filter="ALL">Todas</button>
                <button type="button" class="ad-inst-filter" data-inst-filter="ACTIVA">Activas</button>
                <button type="button" class="ad-inst-filter" data-inst-filter="INACTIVA">Inactivas</button>
                <button type="button" class="ad-inst-filter" data-inst-filter="VENCIDA">Vencidas</button>
            </div>
        </div>

        <div class="ad-inst-grid" data-inst-grid>
            @forelse ($instituciones as $inst)
                @php
                    $tot = max(0, (int) $inst->cupos_totales);
                    $used = (int) $inst->cupos_usados;
                    $pct = $tot > 0 ? (int) min(100, round(($used / $tot) * 100)) : 0;
                    $miembros = $inst->clientes->count();
                    $enlacesN = $inst->enlaces->count();
                    $parts = preg_split('/\s+/u', trim((string) $inst->nombre)) ?: [];
                    $ini = mb_strtoupper(
                        mb_substr($parts[0] ?? 'I', 0, 1).
                        mb_substr($parts[1] ?? ($parts[0] ?? 'N'), 0, 1)
                    );
                    $estadoClass = match ($inst->estado) {
                        'ACTIVA' => 'is-ok',
                        'VENCIDA' => 'is-bad',
                        default => 'is-warn',
                    };
                    $cupoTone = $pct >= 90 ? 'is-hot' : ($pct >= 70 ? 'is-mid' : 'is-ok');
                    $contacto = $inst->contacto_nombre ?: $inst->correo ?: $inst->telefono ?: 'Sin contacto';
                @endphp
                <article class="ad-inst-card" data-inst-card data-estado="{{ $inst->estado }}" data-id="{{ $inst->id_institucion }}">
                    <header class="ad-inst-card-head">
                        <div class="ad-inst-avatar" aria-hidden="true">{{ $ini }}</div>
                        <div class="ad-inst-identity">
                            <h2>{{ $inst->nombre }}</h2>
                            <p>
                                <span>{{ $inst->ciudad ?: 'Sin ciudad' }}</span>
                                @if ($inst->convenio)
                                    <span class="ad-inst-dot">·</span>
                                    <span>{{ $inst->convenio }}</span>
                                @endif
                            </p>
                            <em>{{ $contacto }}</em>
                        </div>
                        <span class="ad-inst-status {{ $estadoClass }}">{{ $inst->estado }}</span>
                    </header>

                    <div class="ad-inst-cupo {{ $cupoTone }}">
                        <div class="ad-inst-cupo-top">
                            <strong>Cupos</strong>
                            <span>{{ $used }} / {{ $tot ?: '—' }}</span>
                        </div>
                        <div class="ad-inst-cupo-track" role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
                            <i style="--p: {{ $pct }}%"></i>
                        </div>
                        <small>{{ $tot > 0 ? $pct.'% ocupado' : 'Sin cupo definido' }}</small>
                    </div>

                    <div class="ad-inst-stats">
                        <button type="button" class="ad-inst-stat" data-inst-open-panel="miembros" title="Ver miembros">
                            <strong>{{ $miembros }}</strong>
                            <span>Miembros</span>
                        </button>
                        <button type="button" class="ad-inst-stat" data-inst-open-panel="enlaces" title="Ver enlaces">
                            <strong>{{ $enlacesN }}</strong>
                            <span>Enlaces</span>
                        </button>
                        @if ($inst->nit)
                            <div class="ad-inst-stat is-static">
                                <strong class="ad-inst-nit">{{ $inst->nit }}</strong>
                                <span>NIT</span>
                            </div>
                        @endif
                    </div>

                    <div class="ad-inst-actions">
                        <button class="ad-btn ad-btn-ghost ad-inst-act" type="button"
                            data-open-enlace
                            data-id="{{ $inst->id_institucion }}">
                            @include('partials.admin-icon', ['icon' => 'link'])
                            Enlace
                        </button>
                        <button class="ad-btn ad-btn-ghost ad-inst-act" type="button"
                            data-open-vincular
                            data-id="{{ $inst->id_institucion }}">
                            @include('partials.admin-icon', ['icon' => 'personas'])
                            Vincular
                        </button>
                        <button class="ad-btn ad-btn-ghost ad-inst-act" type="button"
                            data-open-inst-edit
                            data-id="{{ $inst->id_institucion }}"
                            data-nombre="{{ $inst->nombre }}"
                            data-nit="{{ $inst->nit }}"
                            data-contacto="{{ $inst->contacto_nombre }}"
                            data-correo="{{ $inst->correo }}"
                            data-telefono="{{ $inst->telefono }}"
                            data-ciudad="{{ $inst->ciudad }}"
                            data-cupos="{{ $inst->cupos_totales }}"
                            data-convenio="{{ $inst->convenio }}"
                            data-estado="{{ $inst->estado }}">
                            @include('partials.admin-icon', ['icon' => 'edit'])
                            Editar
                        </button>
                    </div>

                    <div class="ad-inst-detail" data-inst-detail hidden>
                        <div class="ad-inst-tabs" role="tablist">
                            <button type="button" class="ad-inst-tab is-active" data-inst-tab="miembros" role="tab">Miembros</button>
                            <button type="button" class="ad-inst-tab" data-inst-tab="enlaces" role="tab">Enlaces</button>
                            <button type="button" class="ad-inst-tab-close" data-inst-close-detail aria-label="Cerrar detalle">×</button>
                        </div>

                        <div class="ad-inst-pane is-active" data-inst-pane="miembros">
                            @include('partials.admin-client-table', [
                                'clientes' => $inst->clientes,
                                'variant' => 'virtual',
                                'compact' => true,
                                'emptyMessage' => 'Sin miembros. Usa Vincular o genera un enlace de registro.',
                            ])
                        </div>

                        <div class="ad-inst-pane" data-inst-pane="enlaces" hidden>
                            @if ($inst->enlaces->isEmpty())
                                <div class="ad-inst-empty-pane">
                                    <p>No hay enlaces de registro.</p>
                                    <button class="ad-btn ad-btn-ghost" type="button" data-open-enlace data-id="{{ $inst->id_institucion }}">Generar enlace</button>
                                </div>
                            @else
                                <ul class="ad-inst-links">
                                    @foreach ($inst->enlaces as $enlace)
                                        @php
                                            $linkPct = $enlace->cupos_maximos > 0
                                                ? (int) min(100, round(($enlace->usos_actuales / $enlace->cupos_maximos) * 100))
                                                : 0;
                                            $linkOk = $enlace->estado === 'ACTIVO';
                                        @endphp
                                        <li class="ad-inst-link">
                                            <div class="ad-inst-link-main">
                                                <strong>{{ $enlace->plan?->nombre ?: 'Plan' }}</strong>
                                                <span class="ad-badge {{ $linkOk ? 'ad-badge-ok' : 'ad-badge-pendiente' }}">{{ $enlace->estado }}</span>
                                            </div>
                                            <div class="ad-inst-link-meta">
                                                <span>{{ $enlace->usos_actuales }}/{{ $enlace->cupos_maximos }} usos</span>
                                                <span>Expira {{ optional($enlace->fecha_expiracion)->format('d/m/Y') ?: '—' }}</span>
                                            </div>
                                            <div class="ad-inst-link-bar"><i style="--p: {{ $linkPct }}%"></i></div>
                                            <div class="ad-inst-link-token">
                                                <code data-token-value="{{ $enlace->token }}">{{ \Illuminate\Support\Str::limit($enlace->token, 22) }}</code>
                                                <button type="button" class="ad-btn ad-btn-ghost ad-inst-copy" data-copy-token="{{ $enlace->token }}">Copiar</button>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="ad-empty-hero ad-inst-empty">
                    <span class="ad-empty-orb">@include('partials.admin-icon', ['icon' => 'instituciones'])</span>
                    <strong>Sin instituciones aún</strong>
                    <p>Crea la primera para gestionar cupos corporativos y enlaces de alta.</p>
                    <button class="ad-btn" type="button" data-open-inst-form>Crear institución</button>
                </div>
            @endforelse
        </div>
        <p class="ad-inst-filter-empty" data-inst-filter-empty hidden>Ninguna institución con ese estado.</p>
    </section>

    <div class="ad-modal {{ $errors->any() ? 'is-open' : '' }}" data-inst-modal {{ $errors->any() ? '' : 'hidden' }}>
        <div class="ad-modal-card">
            <header class="ad-modal-head">
                <div>
                    <h2>Nueva institución</h2>
                    <p>Convenio, contacto y cupos iniciales</p>
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

    <div class="ad-modal" data-inst-edit-modal hidden>
        <div class="ad-modal-card">
            <header class="ad-modal-head">
                <div>
                    <h2>Editar institución</h2>
                    <p>Actualiza datos del convenio y estado</p>
                </div>
                <button type="button" class="ad-modal-close" data-close-inst-edit aria-label="Cerrar">×</button>
            </header>
            <form class="ad-form" method="POST" data-inst-edit-form>
                @csrf
                @method('PUT')
                <div class="ad-form-row">
                    <label><span>Nombre *</span><input type="text" name="nombre" data-field="nombre" required></label>
                    <label><span>NIT</span><input type="text" name="nit" data-field="nit"></label>
                </div>
                <div class="ad-form-row">
                    <label><span>Contacto</span><input type="text" name="contacto_nombre" data-field="contacto"></label>
                    <label><span>Ciudad</span><input type="text" name="ciudad" data-field="ciudad"></label>
                </div>
                <div class="ad-form-row">
                    <label><span>Correo</span><input type="email" name="correo" data-field="correo"></label>
                    <label><span>Teléfono</span><input type="text" name="telefono" data-field="telefono"></label>
                </div>
                <div class="ad-form-row">
                    <label><span>Cupos totales</span><input type="number" name="cupos_totales" data-field="cupos" min="0"></label>
                    <label><span>Convenio</span><input type="text" name="convenio" data-field="convenio"></label>
                </div>
                <label><span>Estado *</span>
                    <select name="estado" data-field="estado" required>
                        @foreach (['ACTIVA','INACTIVA','VENCIDA'] as $e)
                            <option value="{{ $e }}">{{ $e }}</option>
                        @endforeach
                    </select>
                </label>
                <div class="ad-modal-actions">
                    <button class="ad-btn ad-btn-ghost" type="button" data-close-inst-edit>Cancelar</button>
                    <button class="ad-btn" type="submit">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="ad-modal" data-enlace-modal hidden>
        <div class="ad-modal-card">
            <header class="ad-modal-head">
                <div>
                    <h2>Generar enlace</h2>
                    <p>Registro institucional con cupo y vencimiento</p>
                </div>
                <button type="button" class="ad-modal-close" data-close-enlace aria-label="Cerrar">×</button>
            </header>
            <form class="ad-form" method="POST" data-enlace-form>
                @csrf
                <div class="ad-form-row">
                    <label><span>Plan</span>
                        <select name="id_plan" required>
                            @foreach ($planes as $plan)
                                <option value="{{ $plan->id_plan }}">{{ $plan->nombre }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label><span>Cupos máximos</span><input type="number" name="cupos_maximos" min="1" value="20" required></label>
                </div>
                <label><span>Expira</span><input type="datetime-local" name="fecha_expiracion" required></label>
                <div class="ad-modal-actions">
                    <button class="ad-btn ad-btn-ghost" type="button" data-close-enlace>Cancelar</button>
                    <button class="ad-btn" type="submit">Generar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="ad-modal" data-vincular-modal hidden>
        <div class="ad-modal-card">
            <header class="ad-modal-head">
                <div>
                    <h2>Vincular cliente</h2>
                    <p>Asigna un cliente libre a este convenio</p>
                </div>
                <button type="button" class="ad-modal-close" data-close-vincular aria-label="Cerrar">×</button>
            </header>
            <form class="ad-form" method="POST" data-vincular-form>
                @csrf
                <label><span>Cliente</span>
                    <select name="id_cliente" required>
                        @forelse ($clientesSinInst as $c)
                            <option value="{{ $c->id_cliente }}">{{ $c->usuario?->nombres }} {{ $c->usuario?->apellidos }} ({{ $c->usuario?->correo }})</option>
                        @empty
                            <option value="" disabled>No hay clientes disponibles</option>
                        @endforelse
                    </select>
                </label>
                <div class="ad-modal-actions">
                    <button class="ad-btn ad-btn-ghost" type="button" data-close-vincular>Cancelar</button>
                    <button class="ad-btn" type="submit" @disabled($clientesSinInst->isEmpty())>Vincular</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            function wireModal(openSel, closeSel, modalSel, onOpen) {
                var modal = document.querySelector(modalSel);
                function open(ctx) {
                    if (!modal) return;
                    if (onOpen) onOpen(ctx);
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
                document.querySelectorAll(openSel).forEach(function (b) {
                    b.addEventListener('click', function () { open(b); });
                });
                document.querySelectorAll(closeSel).forEach(function (b) {
                    b.addEventListener('click', close);
                });
                modal?.addEventListener('click', function (e) { if (e.target === modal) close(); });
                return { open: open, close: close };
            }

            wireModal('[data-open-inst-form]', '[data-close-inst-form]', '[data-inst-modal]');

            var editForm = document.querySelector('[data-inst-edit-form]');
            wireModal('[data-open-inst-edit]', '[data-close-inst-edit]', '[data-inst-edit-modal]', function (btn) {
                if (!editForm || !btn) return;
                editForm.action = '{{ url('admin/instituciones') }}/' + btn.getAttribute('data-id');
                ['nombre','nit','contacto','correo','telefono','ciudad','cupos','convenio','estado'].forEach(function (f) {
                    var el = editForm.querySelector('[data-field="'+f+'"]');
                    if (el) el.value = btn.getAttribute('data-'+f) || '';
                });
            });

            var enlaceForm = document.querySelector('[data-enlace-form]');
            wireModal('[data-open-enlace]', '[data-close-enlace]', '[data-enlace-modal]', function (btn) {
                if (!enlaceForm || !btn) return;
                enlaceForm.action = '{{ url('admin/instituciones') }}/' + btn.getAttribute('data-id') + '/enlaces';
            });

            var vincularForm = document.querySelector('[data-vincular-form]');
            wireModal('[data-open-vincular]', '[data-close-vincular]', '[data-vincular-modal]', function (btn) {
                if (!vincularForm || !btn) return;
                vincularForm.action = '{{ url('admin/instituciones') }}/' + btn.getAttribute('data-id') + '/vincular';
            });

            /* Filters */
            var filters = document.querySelectorAll('[data-inst-filter]');
            var cards = document.querySelectorAll('[data-inst-card]');
            var emptyFilter = document.querySelector('[data-inst-filter-empty]');
            filters.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    filters.forEach(function (b) { b.classList.remove('is-active'); });
                    btn.classList.add('is-active');
                    var key = btn.getAttribute('data-inst-filter');
                    var visible = 0;
                    cards.forEach(function (card) {
                        var show = key === 'ALL' || card.getAttribute('data-estado') === key;
                        card.hidden = !show;
                        if (show) visible++;
                    });
                    if (emptyFilter) emptyFilter.hidden = visible > 0 || cards.length === 0;
                });
            });

            /* Detail panels */
            function showPane(card, pane) {
                var detail = card.querySelector('[data-inst-detail]');
                if (!detail) return;
                detail.hidden = false;
                card.classList.add('is-open');
                detail.querySelectorAll('[data-inst-tab]').forEach(function (t) {
                    t.classList.toggle('is-active', t.getAttribute('data-inst-tab') === pane);
                });
                detail.querySelectorAll('[data-inst-pane]').forEach(function (p) {
                    var on = p.getAttribute('data-inst-pane') === pane;
                    p.hidden = !on;
                    p.classList.toggle('is-active', on);
                });
            }
            function closeDetail(card) {
                var detail = card.querySelector('[data-inst-detail]');
                if (!detail) return;
                detail.hidden = true;
                card.classList.remove('is-open');
            }

            document.querySelectorAll('[data-inst-open-panel]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var card = btn.closest('[data-inst-card]');
                    if (!card) return;
                    showPane(card, btn.getAttribute('data-inst-open-panel'));
                });
            });
            document.querySelectorAll('[data-inst-tab]').forEach(function (tab) {
                tab.addEventListener('click', function () {
                    var card = tab.closest('[data-inst-card]');
                    if (!card) return;
                    showPane(card, tab.getAttribute('data-inst-tab'));
                });
            });
            document.querySelectorAll('[data-inst-close-detail]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var card = btn.closest('[data-inst-card]');
                    if (card) closeDetail(card);
                });
            });

            /* Copy token */
            document.querySelectorAll('[data-copy-token]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var val = btn.getAttribute('data-copy-token') || '';
                    function done() {
                        var prev = btn.textContent;
                        btn.textContent = 'Copiado';
                        btn.classList.add('is-copied');
                        setTimeout(function () {
                            btn.textContent = prev;
                            btn.classList.remove('is-copied');
                        }, 1400);
                    }
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(val).then(done).catch(function () {
                            window.prompt('Copia el token:', val);
                        });
                    } else {
                        window.prompt('Copia el token:', val);
                    }
                });
            });
        })();
    </script>
@endsection
