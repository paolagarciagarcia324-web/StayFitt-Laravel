@php
    $adminUser = $adminUser ?? auth()->user();
    $iniciales = $adminUser?->iniciales() ?? 'FF';
    $topCount = (int) ($headerTopProductos?->count() ?? 0);
    $alertaCount = (int) ($headerAlertas?->count() ?? 0);
@endphp
<div class="ad-tools" data-tools>
    <div class="ad-tool-pill">
        <div class="ad-tool">
            <button type="button" class="ad-tool-btn" data-panel="actividad" aria-label="Registro de actividad">
                @include('partials.admin-icon', ['icon' => 'actividad'])
            </button>
            <div class="ad-tool-panel" data-box="actividad" hidden>
                <header>
                    <strong>Actividad del sistema</strong>
                    <a href="{{ route('admin.usuarios.index') }}">Ver</a>
                </header>
                <p class="ad-tool-label">Entraron (24 h)</p>
                @forelse ($headerEntraron ?? [] as $u)
                    <div class="ad-tool-row">
                        <span>{{ $u->nombres }} {{ $u->apellidos }}</span>
                        <small>{{ optional($u->ultimo_acceso_en)->diffForHumans() }}</small>
                    </div>
                @empty
                    <p class="ad-empty">Nadie ha ingresado hoy.</p>
                @endforelse
                <p class="ad-tool-label">Activos (7 días)</p>
                @forelse ($headerActivos ?? [] as $u)
                    <div class="ad-tool-row">
                        <span>{{ $u->nombres }} {{ $u->apellidos }}</span>
                        <small>activo</small>
                    </div>
                @empty
                    <p class="ad-empty">Sin usuarios activos recientes.</p>
                @endforelse
                <p class="ad-tool-label">Sin actividad (30 días)</p>
                @forelse ($headerInactivos ?? [] as $u)
                    <div class="ad-tool-row">
                        <span>{{ $u->nombres }} {{ $u->apellidos }}</span>
                        <small>{{ $u->ultimo_acceso_en ? optional($u->ultimo_acceso_en)->diffForHumans() : 'nunca' }}</small>
                    </div>
                @empty
                    <p class="ad-empty">Todos han tenido actividad.</p>
                @endforelse
            </div>
        </div>

        <div class="ad-tool">
            <button type="button" class="ad-tool-btn" data-panel="ventas" aria-label="Productos más vendidos">
                @include('partials.admin-icon', ['icon' => 'bolso'])
                @if ($topCount > 0)
                    <em class="ad-badge-dot is-cyan">{{ $topCount }}</em>
                @endif
            </button>
            <div class="ad-tool-panel" data-box="ventas" hidden>
                <header>
                    <strong>Más vendidos del mes</strong>
                    <a href="{{ route('admin.tienda.index') }}">Ver</a>
                </header>
                @forelse ($headerTopProductos ?? [] as $item)
                    <div class="ad-tool-row">
                        <span>{{ $item->nombre }}</span>
                        <small>{{ (int) $item->unidades }} uds</small>
                    </div>
                @empty
                    <p class="ad-empty">Aún no hay ventas de tienda este mes.</p>
                @endforelse
            </div>
        </div>

        <div class="ad-tool">
            <button type="button" class="ad-tool-btn" data-panel="alertas" aria-label="Planes por vencer">
                @include('partials.admin-icon', ['icon' => 'alerta'])
                @if ($alertaCount > 0)
                    <em class="ad-badge-dot is-alert">{{ $alertaCount }}</em>
                @endif
            </button>
            <div class="ad-tool-panel" data-box="alertas" hidden>
                <header>
                    <strong>Planes por vencer</strong>
                    <a href="{{ route('admin.clientes.virtuales') }}">Ver</a>
                </header>
                @forelse ($headerAlertas ?? [] as $planCliente)
                    <div class="ad-tool-row">
                        <span>{{ $planCliente->cliente?->usuario?->nombres }} {{ $planCliente->cliente?->usuario?->apellidos }}</span>
                        <small>{{ $planCliente->plan?->nombre }} · {{ optional($planCliente->fecha_fin)->format('d/m') }}</small>
                    </div>
                @empty
                    <p class="ad-empty">No hay caducidades en los próximos 14 días.</p>
                @endforelse
            </div>
        </div>

        <button type="button" class="ad-tool-btn" data-theme-toggle aria-label="Cambiar tema">
            <span data-theme-icon="moon">@include('partials.admin-icon', ['icon' => 'luna'])</span>
            <span data-theme-icon="sun" hidden>@include('partials.admin-icon', ['icon' => 'sol'])</span>
        </button>
    </div>

    <span class="ad-avatar-initials" title="{{ $adminUser?->nombres }} {{ $adminUser?->apellidos }}">{{ $iniciales }}</span>
</div>
