@php
    $variant = $variant ?? 'virtual';
    $compact = $compact ?? false;
@endphp
<table class="ad-table ad-users-table {{ $compact ? 'ad-table-compact' : '' }}">
    <thead>
        <tr>
            @unless ($compact)
                <th>#</th>
            @endunless
            <th>Perfil</th>
            <th>Cliente</th>
            @unless ($compact)
                @if ($variant === 'virtual')
                    <th>Tipo</th>
                    <th>Institución</th>
                @else
                    <th>Nacimiento</th>
                    <th>Horario</th>
                @endif
            @endunless
            <th>Plan</th>
            <th>Racha</th>
            <th>Días plan</th>
            <th>Estado</th>
            @if (! $compact)
                <th>Último acceso</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @forelse ($clientes as $index => $c)
            @php
                $u = $c->usuario;
                $planActivo = $c->planActivo();
                $diasPlan = $c->diasRestantesPlan();
            @endphp
            <tr>
                @unless ($compact)
                    <td>{{ isset($clientes) && method_exists($clientes, 'firstItem') ? $clientes->firstItem() + $index : $index + 1 }}</td>
                @endunless
                <td>@include('partials.admin-avatar', ['user' => $u, 'size' => $compact ? 'sm' : 'md'])</td>
                <td>
                    <strong>{{ $u?->nombres }} {{ $u?->apellidos }}</strong>
                    <em class="ad-user-mail">{{ $u?->correo }}</em>
                    @if (! $compact && $variant === 'virtual' && $c->objetivo_principal)
                        <em class="ad-user-mail">{{ $c->objetivo_principal }}</em>
                    @elseif (! $compact && $variant === 'presencial' && $c->ciudad)
                        <em class="ad-user-mail">{{ $c->ciudad }}</em>
                    @endif
                </td>
                @unless ($compact)
                    @if ($variant === 'virtual')
                        <td>{{ $c->tipo_cliente }}</td>
                        <td>{{ $c->institucion?->nombre ?? '—' }}</td>
                    @else
                        <td>{{ $c->fecha_nacimiento ? \Illuminate\Support\Carbon::parse($c->fecha_nacimiento)->format('d/m/Y') : '—' }}</td>
                        <td>{{ $c->horario?->nombre ?? '—' }}</td>
                    @endif
                @endunless
                <td>{{ $planActivo?->plan?->nombre ?? '—' }}</td>
                <td><span class="ad-pill-streak">{{ $c->diasRacha() }} d</span></td>
                <td>
                    @if ($diasPlan === null)
                        —
                    @elseif ($diasPlan < 0)
                        <span class="ad-badge ad-badge-bad">Vencido</span>
                    @else
                        <span class="ad-pill-days">{{ $diasPlan }} días</span>
                    @endif
                </td>
                <td>
                    <span class="ad-badge {{ in_array($c->estado_cliente, ['ACTIVA', 'ACTIVO']) ? 'ad-badge-ok' : 'ad-badge-pendiente' }}">{{ $c->estado_cliente }}</span>
                </td>
                @if (! $compact)
                    <td>{{ $u?->ultimo_acceso_en ? $u->ultimo_acceso_en->diffForHumans() : '—' }}</td>
                @endif
            </tr>
        @empty
            @php
                $cols = $compact ? 6 : ($variant === 'virtual' ? 10 : 10);
            @endphp
            <tr><td colspan="{{ $cols }}" class="ad-empty">{{ $emptyMessage ?? 'Sin clientes.' }}</td></tr>
        @endforelse
    </tbody>
</table>
@if (isset($clientes) && method_exists($clientes, 'links') && ! $compact)
    <div class="ad-pagination">{{ $clientes->links() }}</div>
@endif
