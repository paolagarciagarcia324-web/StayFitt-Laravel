@php
    $variant = $variant ?? 'virtual';
    $compact = $compact ?? false;
    $asCards = $asCards ?? ! $compact;
    $cols = $compact ? 6 : ($variant === 'virtual' ? 10 : 10);
@endphp
@if ($asCards)
    @include('partials.admin-client-cards', ['clientes' => $clientes, 'variant' => $variant, 'emptyMessage' => $emptyMessage ?? null])
@else
<table class="ad-table ad-users-table ad-cli-table {{ $compact ? 'ad-table-compact' : '' }} {{ $variant === 'virtual' ? 'is-virtual' : 'is-presencial' }}">
    <colgroup>
        @unless ($compact)
            <col class="ad-cli-w-num">
        @endunless
        <col class="ad-cli-w-perfil">
        <col class="ad-cli-w-cliente">
        @unless ($compact)
            <col class="ad-cli-w-text">
            <col class="ad-cli-w-text">
        @else
            <col class="ad-cli-w-text">
        @endunless
        <col class="ad-cli-w-racha">
        <col class="ad-cli-w-dias">
        <col class="ad-cli-w-estado">
        @if (! $compact)
            <col class="ad-cli-w-access">
            <col class="ad-cli-w-actions">
        @endif
    </colgroup>
    <thead>
        <tr>
            @unless ($compact)
                <th class="ad-cli-col-num">#</th>
            @endunless
            <th class="ad-cli-col-perfil">Perfil</th>
            <th class="ad-cli-col-cliente">Cliente</th>
            @unless ($compact)
                @if ($variant === 'virtual')
                    <th class="ad-cli-col-inst">Institución</th>
                @else
                    <th class="ad-cli-col-inst">Horario</th>
                @endif
            @endunless
            <th class="ad-cli-col-plan">Plan</th>
            <th class="ad-cli-col-racha">Racha</th>
            <th class="ad-cli-col-dias">Días</th>
            <th class="ad-cli-col-estado">Estado</th>
            @if (! $compact)
                <th class="ad-cli-col-access">Acceso</th>
                <th class="ad-cli-col-actions" aria-label="Acciones"></th>
            @endif
        </tr>
    </thead>
    <tbody>
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
                $ultimoAcceso = $u?->ultimo_acceso_en;
                $ultimoAccesoTexto = $ultimoAcceso ? $ultimoAcceso->diffForHumans() : 'Nunca';
                $ultimoAccesoTitle = $ultimoAcceso ? $ultimoAcceso->format('d/m/Y H:i') : 'Sin registro de acceso';
                $instNombre = $variant === 'virtual'
                    ? ($c->institucion?->nombre ?? '—')
                    : ($c->horario?->nombre ?? '—');
                $planNombre = $planActivo?->plan?->nombre ?? '—';
                $nacLabel = $c->fecha_nacimiento
                    ? \Illuminate\Support\Carbon::parse($c->fecha_nacimiento)->format('d/m/Y')
                    : null;
                $searchBlob = mb_strtolower(trim(($nombre).' '.($u?->correo ?? '').' '.($instNombre).' '.($planNombre)));
            @endphp
            <tr data-search="{{ $searchBlob }}">
                @unless ($compact)
                    <td class="ad-cli-col-num">{{ isset($clientes) && method_exists($clientes, 'firstItem') ? $clientes->firstItem() + $index : $index + 1 }}</td>
                @endunless
                <td class="ad-cli-col-perfil">@include('partials.admin-avatar', ['user' => $u, 'size' => $compact ? 'sm' : 'md', 'zoomable' => ! $compact])</td>
                <td class="ad-cli-col-cliente">
                    <div class="ad-cli-cell">
                        <strong class="ad-cli-cell-name" title="{{ $nombre }}">{{ $u?->nombres }} {{ $u?->apellidos }}</strong>
                        <em class="ad-cli-cell-mail" title="{{ $u?->correo }}">{{ $u?->correo }}</em>
                        @if (! empty($unlinkInstitucion))
                            <form class="ad-inline-form" method="POST" action="{{ route('admin.instituciones.desvincular', $unlinkInstitucion) }}" onsubmit="return confirm('¿Quitar a este miembro de la institución?');">
                                @csrf
                                <input type="hidden" name="id_cliente" value="{{ $c->id_cliente }}">
                                <button class="ad-btn ad-btn-ghost" type="submit">Quitar</button>
                            </form>
                        @endif
                        <div class="ad-cli-cell-tags">
                            @if (! $compact && $variant === 'virtual' && $c->tipo_cliente)
                                <span class="ad-cli-type">{{ $c->tipo_cliente }}</span>
                            @endif
                            @if (! $compact && $variant === 'presencial' && $nacLabel)
                                <span class="ad-cli-cell-chip">{{ $nacLabel }}</span>
                            @endif
                            @if (! $compact && $variant === 'virtual' && $c->objetivo_principal)
                                <span class="ad-cli-cell-chip">{{ $c->objetivo_principal }}</span>
                            @elseif (! $compact && $variant === 'presencial' && $c->ciudad)
                                <span class="ad-cli-cell-chip">{{ $c->ciudad }}</span>
                            @endif
                        </div>
                    </div>
                </td>
                @unless ($compact)
                    <td class="ad-cli-col-inst"><span class="ad-cli-text" title="{{ $instNombre }}">{{ $instNombre }}</span></td>
                @endunless
                <td class="ad-cli-col-plan"><span class="ad-cli-text" title="{{ $planNombre }}">{{ $planNombre }}</span></td>
                <td class="ad-cli-col-racha"><span class="ad-pill-streak">{{ $c->diasRacha() }} d</span></td>
                <td class="ad-cli-col-dias">
                    @if ($diasPlan === null)
                        —
                    @elseif ($diasPlan < 0)
                        <span class="ad-badge ad-badge-bad">Vencido</span>
                    @else
                        <span class="ad-pill-days">{{ $diasPlan }} días</span>
                    @endif
                </td>
                <td class="ad-cli-col-estado">
                    <span class="ad-badge {{ in_array($c->estado_cliente, ['ACTIVA', 'ACTIVO']) ? 'ad-badge-ok' : 'ad-badge-pendiente' }}">{{ $c->estado_cliente }}</span>
                </td>
                @if (! $compact)
                    <td class="ad-cli-col-access">
                        <span class="ad-cli-access-time" title="{{ $ultimoAccesoTitle }}">{{ $ultimoAccesoTexto }}</span>
                    </td>
                    <td class="ad-cli-col-actions">
                        <div class="ad-row-actions ad-cli-actions">
                            <button
                                class="ad-icon-btn"
                                type="button"
                                title="Ver"
                                aria-label="Ver cliente"
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
                                data-nacimiento="{{ $variant === 'presencial' && $nacLabel ? $nacLabel : '' }}"
                                data-coach="{{ $coachNombre }}"
                                data-coach-id="{{ $coachId }}"
                                data-id="{{ $c->id_cliente }}"
                                data-update-url="{{ route('admin.clientes.update', $c->id_cliente) }}"
                                data-completo-url="{{ route('admin.clientes.updateCompleto', $c->id_cliente) }}"
                                data-coach-url="{{ route('admin.clientes.coach', $c->id_cliente) }}"
                                data-edit-url="{{ route('admin.clientes.edit', $c->id_cliente) }}"
                                data-accesos='@json($accesosJson)'
                            >
                                @include('partials.admin-icon', ['icon' => 'eye'])
                            </button>
                            <button
                                class="ad-icon-btn"
                                type="button"
                                title="Editar"
                                aria-label="Editar cliente"
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
                                data-accesos='@json($accesosJson)'
                            >
                                @include('partials.admin-icon', ['icon' => 'edit'])
                            </button>
                            <form class="ad-inline-form" method="POST" action="{{ route('admin.clientes.destroy', $c->id_cliente) }}" data-cliente-delete-form>
                                @csrf
                                @method('DELETE')
                                <button class="ad-icon-btn is-danger" type="button" data-cliente-delete title="Eliminar" aria-label="Eliminar cliente">
                                    @include('partials.admin-icon', ['icon' => 'trash'])
                                </button>
                            </form>
                        </div>
                    </td>
                @endif
            </tr>
        @empty
            <tr><td colspan="{{ $cols }}" class="ad-empty">{{ $emptyMessage ?? 'Sin clientes.' }}</td></tr>
        @endforelse
    </tbody>
</table>
<p class="ad-live-search-empty" data-live-search-empty hidden>Ningún cliente coincide con esa búsqueda.</p>
@if (isset($clientes) && method_exists($clientes, 'links') && ! $compact)
    <div class="ad-pagination">{{ $clientes->links() }}</div>
@endif
@endif
