@extends('layouts.staff')

@section('title', 'Centro operativo')

@section('content')
    <div class="st-page">
        <section class="st-hero">
            <div class="st-avatar st-avatar--lg">{{ $user->iniciales() }}</div>
            <div>
                <div class="st-kicker">Asistente técnico · Ops</div>
                <h1 class="st-title">Hola, {{ $user->nombres }}</h1>
                <p class="st-sub">
                    Apoyando a <strong>{{ trim($coach->usuario?->nombres.' '.$coach->usuario?->apellidos) }}</strong>
                    · {{ $coach->especialidad ?? 'Coach' }}
                </p>
                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:12px">
                    <span class="st-badge is-violet">{{ $kpis['clientes'] }} clientes apoyo</span>
                    <span class="st-badge is-cyan">{{ $kpis['borradores'] }} borradores</span>
                    <span class="st-badge is-warn">{{ $kpis['revision'] }} en revisión</span>
                </div>
            </div>
            <div class="st-hero__actions">
                <a class="st-btn" href="{{ route('coach.propuestas.create') }}">Nueva propuesta</a>
                <a class="st-btn st-btn-ghost" href="{{ route('staff.rutinas.index') }}">Rutinas</a>
                <a class="st-btn st-btn-ghost" href="{{ route('staff.novedades.create') }}">Novedad</a>
            </div>
        </section>

        <div class="st-metrics">
            <x-staff.metric label="Clientes apoyo" :value="$kpis['clientes']" hint="Cartera del coach" />
            <x-staff.metric label="Borradores" :value="$kpis['borradores']" hint="Propuestas / rutinas" />
            <x-staff.metric label="En revisión" :value="$kpis['revision']" hint="Esperando coach/admin" />
            <x-staff.metric label="Videos activos" :value="$kpis['videos']" hint="Biblioteca" />
            <x-staff.metric label="Rutinas borrador" :value="$kpis['rutinas_borrador']" hint="Pendientes aprobación" />
        </div>

        <div class="st-flow" style="margin:4px 0">
            <span class="is-active">1. Borrador</span>
            <span>→</span>
            <span>2. En revisión</span>
            <span>→</span>
            <span>3. Coach aprueba</span>
            <span>→</span>
            <span>4. Cliente recibe</span>
        </div>

        <div class="st-grid-12">
            <div class="st-span-7">
                <x-staff.card title="Clientes de apoyo" subtitle="Cartera del coach seleccionado">
                    <x-slot:actions><a class="st-link" href="{{ route('staff.clientes.index') }}">Ver todos →</a></x-slot:actions>
                    @if ($clientes->isEmpty())
                        <p class="st-empty">Sin clientes asignados al coach actual.</p>
                    @else
                        <div class="st-clients">
                            @foreach ($clientes as $c)
                                @php $u = $c->usuario; @endphp
                                <article class="st-client">
                                    <div class="st-client__top">
                                        <div class="st-avatar">{{ $u?->iniciales() ?? '?' }}</div>
                                        <div class="st-client__meta">
                                            <strong>{{ trim(($u?->nombres ?? '').' '.($u?->apellidos ?? '')) }}</strong>
                                            <small>{{ $c->objetivo_principal ?? 'Sin objetivo' }}</small>
                                        </div>
                                    </div>
                                    <div class="st-client__actions">
                                        <a class="st-link" href="{{ route('staff.clientes.show', $c->id_cliente) }}">Expediente</a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </x-staff.card>

                <x-staff.card title="Propuestas recientes" style="margin-top:16px">
                    @forelse ($propuestas as $p)
                        <div class="st-session">
                            <div>
                                <strong style="font-size:13px">{{ $p->titulo_interno }}</strong>
                                <div style="font-size:11px;color:var(--st-muted)">{{ $p->etiquetaEstado() }}</div>
                            </div>
                            <a class="st-link" href="{{ route('coach.propuestas.entrenamiento', $p) }}">Abrir →</a>
                        </div>
                    @empty
                        <p class="st-empty">Sin propuestas. Crea la primera.</p>
                    @endforelse
                </x-staff.card>
            </div>

            <div class="st-span-5">
                <x-staff.card title="Tareas pendientes">
                    @forelse ($pendientes as $item)
                        <div class="st-pending">
                            <span class="st-pending__dot"></span>
                            <div>
                                <strong style="font-size:12px">{{ $item['titulo'] }}</strong>
                                <div style="font-size:11px;color:var(--st-muted)">{{ $item['detalle'] }}</div>
                                <span class="st-badge is-violet">{{ $item['estado'] ?? 'Pendiente' }}</span>
                                <a class="st-link" href="{{ $item['url'] }}">Ir →</a>
                            </div>
                        </div>
                    @empty
                        <p class="st-empty">Sin pendientes operativos.</p>
                    @endforelse
                </x-staff.card>

                <x-staff.card title="Novedades welcome" subtitle="{{ $novedadesActivas }} activas" style="margin-top:16px">
                    <a class="st-btn st-btn-ghost" href="{{ route('staff.novedades.index') }}">Gestionar novedades</a>
                </x-staff.card>
            </div>
        </div>
    </div>
@endsection
