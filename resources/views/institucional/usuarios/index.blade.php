@extends('layouts.institucional')

@section('title', 'Usuarios vinculados')

@section('content')
    <div class="in-page">
        <section class="in-hero" style="grid-template-columns:1fr auto">
            <div>
                <div class="in-kicker">Gestión de miembros</div>
                <h1 class="in-title">Usuarios vinculados</h1>
                <p class="in-sub">{{ $institucion->nombre }} · Colaboradores del convenio institucional</p>
            </div>
            <div class="in-hero__actions">
                <a class="in-btn" href="{{ route('institucional.invitaciones.index') }}">Invitar</a>
            </div>
        </section>

        <form class="in-filters" method="GET">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar nombre o correo">
            <select name="estado">
                <option value="">Todos los estados</option>
                @foreach (['ACTIVO', 'ACTIVA', 'SUSPENDIDO', 'INACTIVO'] as $e)
                    <option value="{{ $e }}" @selected(request('estado') === $e)>{{ $e }}</option>
                @endforeach
            </select>
            <button type="submit" class="in-btn in-btn-ghost">Filtrar</button>
        </form>

        @if ($miembros->isEmpty())
            <x-institucional.card title="Sin miembros">
                <p class="in-empty">Genera invitaciones individuales o enlaces grupales para registrar colaboradores.</p>
                <a class="in-btn" href="{{ route('institucional.invitaciones.index') }}">Ir a invitaciones</a>
            </x-institucional.card>
        @else
            <div class="in-members">
                @foreach ($miembros as $c)
                    @php
                        $u = $c->usuario;
                        $plan = $c->planesCliente->sortByDesc('id_plan_cliente')->first()?->plan;
                    @endphp
                    <article class="in-member">
                        <div class="in-member__top">
                            <div class="in-avatar">
                                @if ($u?->avatarUrl())
                                    <img src="{{ $u->avatarUrl() }}" alt="">
                                @else
                                    {{ $u?->iniciales() ?? '?' }}
                                @endif
                            </div>
                            <div class="in-member__meta">
                                <strong>{{ trim(($u?->nombres ?? '').' '.($u?->apellidos ?? '')) }}</strong>
                                <small>{{ $u?->correo ?? $u?->email }}</small>
                            </div>
                        </div>
                        <div style="display:flex;flex-wrap:wrap;gap:6px">
                            <span class="in-badge {{ in_array($c->estado_cliente, ['ACTIVO','ACTIVA']) ? 'is-ok' : 'is-muted' }}">{{ $c->estado_cliente }}</span>
                            <span class="in-badge is-blue">{{ $c->modalidad }}</span>
                            @if ($plan)
                                <span class="in-badge is-teal">{{ $plan->nombre }}</span>
                            @endif
                        </div>
                        <small style="color:var(--in-muted);font-size:11px">Alta: {{ $c->fecha_alta?->format('d/m/Y') ?? '—' }}</small>
                        <a class="in-link" href="{{ route('institucional.usuarios.show', $c->id_cliente) }}">Ver detalle →</a>
                    </article>
                @endforeach
            </div>
            <div class="in-pagination">{{ $miembros->links() }}</div>
        @endif
    </div>
@endsection
