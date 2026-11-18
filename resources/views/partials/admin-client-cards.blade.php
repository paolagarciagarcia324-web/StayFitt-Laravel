@php
    $variant = $variant ?? 'virtual';
@endphp
<div class="ad-people-grid ad-stagger">
    @forelse ($clientes as $index => $c)
        @php
            $u = $c->usuario;
            $planActivo = $c->planActivo();
            $diasPlan = $c->diasRestantesPlan();
            $nombre = trim(($u?->nombres).' '.($u?->apellidos)) ?: 'Cliente';
            $diasLabel = $diasPlan === null ? '—' : ($diasPlan < 0 ? 'Vencido' : $diasPlan.' días');
            $coachNombre = trim(($c->coach?->usuario?->nombres).' '.($c->coach?->usuario?->apellidos)) ?: 'Sin asignar';
            $accesosJson = $c->relationLoaded('accesos')
                ? $c->accesos->map(function ($a) use ($c) {
                    $fin = $a->fecha_fin
                        ? \Illuminate\Support\Carbon::parse($a->fecha_fin)->format('Y-m-d')
                        : '';

                    return [
                        'id' => $a->id_acceso,
                        'modulo' => $a->modulo?->nombre ?: ('#'.$a->id_modulo),
                        'vigencia' => ($a->fecha_inicio ?: '—').' → '.($a->fecha_fin ?: '—'),
                        'estado' => $a->estado_acceso,
                        'fecha_fin' => $fin,
                        'url' => route('admin.clientes.acceso', [$c->id_cliente, $a->id_acceso]),
                    ];
                })->values()->all()
                : [];
            $coachId = (int) ($c->id_coach ?: $c->id_coach_asignado ?: 0);
            $instNombre = $variant === 'virtual'
                ? ($c->institucion?->nombre ?? '—')
                : ($c->horario?->nombre ?? '—');
            $planNombre = $planActivo?->plan?->nombre ?? '—';
            $searchBlob = mb_strtolower(trim(($nombre).' '.($u?->correo ?? '').' '.($instNombre).' '.($planNombre)));
            $estadoOk = in_array($c->estado_cliente, ['ACTIVA', 'ACTIVO'], true);
        @endphp
        <article class="ad-people-card" data-inst-card data-search="{{ $searchBlob }}" data-estado="{{ $c->estado_cliente }}">
            <span class="ad-people-status {{ $estadoOk ? 'is-ok' : 'is-off' }}">{{ $c->estado_cliente }}</span>
            <header>
                @include('partials.admin-avatar', ['user' => $u, 'zoomable' => true])
                <div>
                    <strong>{{ $nombre }}</strong>
                    <em class="ad-user-mail">{{ $variant === 'virtual' ? ($c->tipo_cliente ?: 'Virtual') : 'Presencial' }}</em>
                </div>
            </header>
            <div class="ad-people-meta">
                <span><em>{{ $variant === 'virtual' ? 'Institución' : 'Horario' }}</em> {{ $instNombre }}</span>
                <span><em>Plan</em> {{ $planNombre }}</span>
                <span><em>Correo</em> {{ $u?->correo ?: '—' }}</span>
                <span><em>Teléfono</em> {{ $u?->telefono ?: '—' }}</span>
            </div>
            <div class="ad-people-actions">
                <button class="ad-btn ad-btn-ghost" type="button"
                    data-open-cliente-view
                    data-nombre="{{ $nombre }}"
                    data-correo="{{ $u?->correo ?: '—' }}"
                    data-foto="{{ $u?->avatarUrl() ?: '' }}"
                    data-iniciales="{{ $u?->iniciales() ?? 'FF' }}"
                    data-modalidad="{{ $c->modalidad }}"
                    data-estado="{{ $c->estado_cliente }}"
                    data-tipo="{{ $c->tipo_cliente ?: '' }}"
                    data-telefono="{{ $u?->telefono ?: '' }}"
                    data-ciudad="{{ $c->ciudad ?: '' }}"
                    data-objetivo="{{ $c->objetivo_principal ?: '' }}"
                    data-plan="{{ $planNombre }}"
                    data-racha="{{ $c->diasRacha() }} días"
                    data-dias="{{ $diasLabel }}"
                    data-extra-label="{{ $variant === 'virtual' ? 'Institución' : 'Horario' }}"
                    data-extra-value="{{ $instNombre }}"
                    data-nacimiento=""
                    data-coach="{{ $coachNombre }}"
                    data-coach-id="{{ $coachId }}"
                    data-id="{{ $c->id_cliente }}"
                    data-update-url="{{ route('admin.clientes.update', $c->id_cliente) }}"
                    data-completo-url="{{ route('admin.clientes.updateCompleto', $c->id_cliente) }}"
                    data-coach-url="{{ route('admin.clientes.coach', $c->id_cliente) }}"
                    data-edit-url="{{ route('admin.clientes.edit', $c->id_cliente) }}"
                    data-accesos='@json($accesosJson)'>Ver</button>
                <button class="ad-btn" type="button"
                    data-open-cliente-edit
                    data-id="{{ $c->id_cliente }}"
                    data-nombre="{{ $nombre }}"
                    data-correo="{{ $u?->correo ?: '—' }}"
                    data-foto="{{ $u?->avatarUrl() ?: '' }}"
                    data-iniciales="{{ $u?->iniciales() ?? 'FF' }}"
                    data-modalidad="{{ $c->modalidad }}"
                    data-estado="{{ $c->estado_cliente }}"
                    data-tipo="{{ $c->tipo_cliente ?: '' }}"
                    data-telefono="{{ $u?->telefono ?: '' }}"
                    data-ciudad="{{ $c->ciudad ?: '' }}"
                    data-objetivo="{{ $c->objetivo_principal ?: '' }}"
                    data-coach-id="{{ $coachId }}"
                    data-update-url="{{ route('admin.clientes.update', $c->id_cliente) }}"
                    data-completo-url="{{ route('admin.clientes.updateCompleto', $c->id_cliente) }}"
                    data-coach-url="{{ route('admin.clientes.coach', $c->id_cliente) }}"
                    data-accesos='@json($accesosJson)'>Editar</button>
            </div>
        </article>
    @empty
        <p class="ad-empty">{{ $emptyMessage ?? 'Sin clientes.' }}</p>
    @endforelse
</div>
<p class="ad-live-search-empty" data-live-search-empty hidden>Ningún cliente coincide con esa búsqueda.</p>
@if (isset($clientes) && method_exists($clientes, 'links'))
    <div class="ad-pagination">{{ $clientes->links() }}</div>
@endif
