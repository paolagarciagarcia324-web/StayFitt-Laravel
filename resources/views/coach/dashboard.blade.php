@extends('layouts.coach')

@section('title', 'Dashboard')

@section('content')
    @php
        $user = $coach->usuario;
        $nombre = trim(($user?->nombres ?? '').' '.($user?->apellidos ?? ''));
    @endphp

    <div class="ch-page">
        <section class="ch-hero">
            <div class="ch-avatar ch-avatar--lg">
                @if ($coach->foto_url)
                    <img src="{{ $coach->foto_url }}" alt="{{ $nombre }}">
                @elseif ($user?->avatarUrl())
                    <img src="{{ $user->avatarUrl() }}" alt="{{ $nombre }}">
                @else
                    {{ $user?->iniciales() ?? 'C' }}
                @endif
            </div>
            <div>
                <div class="ch-kicker">Centro de operaciones</div>
                <h1 class="ch-title">Hola, {{ $user?->nombres ?? 'Coach' }}</h1>
                <p class="ch-sub">
                    {{ $coach->especialidad ?? 'Entrenamiento personal' }}
                    · {{ strtoupper($coach->estado_coach ?? 'ACTIVO') }}
                    @if ($coach->codigo_coach)
                        · {{ $coach->codigo_coach }}
                    @endif
                </p>
                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:12px">
                    <span class="ch-badge is-pink">{{ $kpis['clientes'] }} clientes activos</span>
                    <span class="ch-badge is-cyan">{{ $kpis['sesiones'] }} sesiones próximas</span>
                    @if ($kpis['mensajes'] > 0)
                        <span class="ch-badge is-warn">{{ $kpis['mensajes'] }} mensajes sin leer</span>
                    @endif
                </div>
            </div>
            <div class="ch-hero__actions">
                <a class="ch-btn" href="{{ route('coach.clientes.index') }}">Ver clientes</a>
                <a class="ch-btn ch-btn-ghost" href="{{ route('coach.entrenamiento.index') }}">Planificar rutina</a>
                <a class="ch-btn ch-btn-ghost" href="{{ route('coach.agenda.index') }}">Agenda</a>
            </div>
        </section>

        <div class="ch-metrics">
            <x-coach.metric label="Clientes" :value="$kpis['clientes']" hint="Asignados activos" />
            <x-coach.metric label="Sesiones" :value="$kpis['sesiones']" hint="Próximas 5 días" />
            <x-coach.metric label="Rutinas activas" :value="$kpis['rutinas']" hint="En planes vigentes" />
            <x-coach.metric label="Progresos mes" :value="$kpis['progresos_mes']" hint="Registros del mes" />
            <x-coach.metric label="Mensajes" :value="$kpis['mensajes']" hint="Pendientes de leer" />
        </div>

        <div class="ch-grid-12">
            <div class="ch-span-7">
                <x-coach.card title="Mis clientes" subtitle="Acceso rápido a expedientes">
                    <x-slot:actions>
                        <a class="ch-link" href="{{ route('coach.clientes.index') }}">Ver todos →</a>
                    </x-slot:actions>
                    @if ($clientes->isEmpty())
                        <p class="ch-empty">Aún no tienes clientes asignados. Contacta al administrador para asignaciones.</p>
                    @else
                        <div class="ch-clients">
                            @foreach ($clientes as $c)
                                @php
                                    $u = $c->usuario;
                                    $plan = $c->planActivo()?->plan?->nombre ?? 'Sin plan';
                                    $pct = min(100, max(20, ($c->diasRacha() ?? 0) * 12));
                                @endphp
                                <article class="ch-client">
                                    <div class="ch-client__top">
                                        <div class="ch-avatar">{{ $u?->iniciales() ?? '?' }}</div>
                                        <div class="ch-client__meta">
                                            <strong>{{ trim(($u?->nombres ?? '').' '.($u?->apellidos ?? '')) }}</strong>
                                            <small>{{ $plan }} · {{ $c->modalidad }}</small>
                                        </div>
                                    </div>
                                    <div class="ch-client__progress"><span style="width:{{ $pct }}%"></span></div>
                                    <div class="ch-client__actions">
                                        <a class="ch-link" href="{{ route('coach.clientes.show', $c->id_cliente) }}">Expediente</a>
                                        <a class="ch-link" href="{{ route('coach.reportes.progreso', ['id_cliente' => $c->id_cliente]) }}">Progreso</a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </x-coach.card>

                <x-coach.card title="Propuestas de plan" subtitle="Estado del flujo editorial" style="margin-top:16px">
                    <div class="ch-metrics">
                        <x-coach.metric label="Borradores" :value="$propuestas['borradores']" />
                        <x-coach.metric label="En revisión" :value="$propuestas['pendientes']" />
                        <x-coach.metric label="Publicados" :value="$propuestas['publicados']" />
                    </div>
                    <div style="margin-top:12px">
                        <a class="ch-btn ch-btn-ghost" href="{{ route('coach.propuestas.create') }}">Nueva propuesta</a>
                    </div>
                </x-coach.card>
            </div>

            <div class="ch-span-5">
                <x-coach.card title="Próximas sesiones" subtitle="Calendario del coach">
                    @forelse ($sesiones as $s)
                        <div class="ch-session">
                            <div class="ch-session__date">
                                <strong>{{ optional($s->fecha)->format('d') }}</strong>
                                <small>{{ optional($s->fecha)->translatedFormat('M') }}</small>
                            </div>
                            <div>
                                <strong style="font-size:13px">{{ $s->titulo }}</strong>
                                <div style="font-size:11px;color:var(--ch-muted);margin-top:2px">
                                    {{ $s->rangoHora12() }} · {{ $s->modalidad }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="ch-empty">No hay sesiones programadas. <a class="ch-link" href="{{ route('coach.agenda.index') }}">Programar una</a></p>
                    @endforelse
                </x-coach.card>

                <x-coach.card title="Actividades pendientes" subtitle="Requieren tu atención" style="margin-top:16px">
                    @forelse ($pendientes as $item)
                        <div class="ch-pending">
                            <span class="ch-pending__dot"></span>
                            <div>
                                <strong style="font-size:12px">{{ $item['titulo'] }}</strong>
                                <div style="font-size:11px;color:var(--ch-muted)">{{ $item['detalle'] }}</div>
                                <a class="ch-link" href="{{ $item['url'] }}">Revisar →</a>
                            </div>
                        </div>
                    @empty
                        <p class="ch-empty">Todo al día. Buen trabajo.</p>
                    @endforelse
                </x-coach.card>
            </div>
        </div>

        <x-coach.card title="Últimos progresos registrados" subtitle="Seguimiento corporal de tus atletas">
            @if ($progresos->isEmpty())
                <p class="ch-empty">Sin registros de progreso recientes.</p>
            @else
                <div class="ch-grid-12">
                    @foreach ($progresos as $r)
                        @php $cu = $r->cliente?->usuario; @endphp
                        <div class="ch-span-4">
                            <div class="ch-client" style="margin:0">
                                <strong style="font-size:13px">{{ trim(($cu?->nombres ?? 'Cliente').' '.($cu?->apellidos ?? '')) }}</strong>
                                <small style="color:var(--ch-muted)">{{ optional($r->fecha_registro)->format('d/m/Y') }}</small>
                                <div style="display:flex;gap:12px;margin-top:8px;font-size:12px">
                                    @if ($r->peso_kg)<span>Peso <strong>{{ $r->peso_kg }} kg</strong></span>@endif
                                    @if ($r->cintura_cm)<span>Cintura <strong>{{ $r->cintura_cm }} cm</strong></span>@endif
                                </div>
                                @if ($r->observacion_coach)
                                    <p style="font-size:11px;color:var(--ch-muted);margin:8px 0 0">{{ Str::limit($r->observacion_coach, 80) }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-coach.card>
    </div>
@endsection
