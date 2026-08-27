@extends('layouts.institucional')

@section('title', 'Dashboard institucional')

@section('content')
    <div class="in-page">
        <section class="in-hero">
            <div class="in-org-hero-logo">
                @if ($institucion->logo_url)
                    <img src="{{ $institucion->logo_url }}" alt="">
                @else
                    {{ mb_substr($institucion->nombre, 0, 2) }}
                @endif
            </div>
            <div>
                <div class="in-kicker">{{ $institucion->etiquetaTipo() }}</div>
                <h1 class="in-title">{{ $institucion->nombre }}</h1>
                <p class="in-sub">
                    Convenio {{ $institucion->convenioVigente() ? 'vigente' : 'inactivo' }}
                    @if ($institucion->fecha_fin)
                        · Hasta {{ $institucion->fecha_fin->format('d/m/Y') }}
                    @endif
                </p>
                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:12px">
                    <span class="in-badge is-blue">{{ $kpis['vinculados'] }} vinculados</span>
                    <span class="in-badge is-teal">{{ $kpis['activos'] }} activos</span>
                    <span class="in-badge {{ $institucion->convenioVigente() ? 'is-ok' : 'is-warn' }}">
                        {{ $institucion->estado }}
                    </span>
                </div>
            </div>
            <div class="in-hero__actions">
                <a class="in-btn" href="{{ route('institucional.invitaciones.index') }}">Nueva invitación</a>
                <a class="in-btn in-btn-ghost" href="{{ route('institucional.reportes.index') }}">Ver reportes</a>
            </div>
        </section>

        <div class="in-metrics">
            <x-institucional.metric label="Usuarios vinculados" :value="$kpis['vinculados']" hint="Total en convenio" />
            <x-institucional.metric label="Usuarios activos" :value="$kpis['activos']" hint="Estado activo" />
            <x-institucional.metric label="Cupos usados" :value="$kpis['cupos_usados'].' / '.$kpis['cupos_totales']" hint="Consumo del servicio" />
            <x-institucional.metric label="Consumo" :value="$kpis['consumo_pct'].'%'" hint="Del cupo contratado" />
            <x-institucional.metric label="Invitaciones pendientes" :value="$kpis['invitaciones_pendientes']" hint="Por aceptar" />
        </div>

        @if ($kpis['cupos_totales'] > 0)
            <div class="in-progress" title="Consumo de cupos">
                <span style="width: {{ min(100, $kpis['consumo_pct']) }}%"></span>
            </div>
        @endif

        <div class="in-grid-12">
            <div class="in-span-7">
                <x-institucional.card title="Usuarios registrados" subtitle="Miembros del convenio">
                    <x-slot:actions><a class="in-link" href="{{ route('institucional.usuarios.index') }}">Ver todos →</a></x-slot:actions>
                    @if ($miembros->isEmpty())
                        <p class="in-empty">Aún no hay miembros. Genera invitaciones para vincular colaboradores.</p>
                    @else
                        <div class="in-members">
                            @foreach ($miembros as $c)
                                @php $u = $c->usuario; @endphp
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
                                            <small>{{ $c->objetivo_principal ?? 'Sin objetivo' }}</small>
                                        </div>
                                    </div>
                                    <span class="in-badge {{ in_array($c->estado_cliente, ['ACTIVO','ACTIVA']) ? 'is-ok' : 'is-muted' }}">{{ $c->estado_cliente }}</span>
                                    <a class="in-link" href="{{ route('institucional.usuarios.show', $c->id_cliente) }}">Ver perfil →</a>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </x-institucional.card>

                <x-institucional.card title="Participación mensual" subtitle="Miembros con actividad registrada" style="margin-top:16px">
                    @if (empty($participacion['values']) || max($participacion['values']) === 0)
                        <p class="in-empty">Sin datos de participación aún.</p>
                    @else
                        @php $max = max(1, max($participacion['values'])); @endphp
                        <div class="in-chart-bars">
                            @foreach ($participacion['labels'] as $i => $label)
                                <div class="in-chart-bar">
                                    <div style="height: {{ round(($participacion['values'][$i] / $max) * 100) }}%"></div>
                                    <span>{{ $label }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-institucional.card>
            </div>

            <div class="in-span-5">
                <x-institucional.card title="Actividad reciente">
                    @forelse ($actividad as $item)
                        <div class="in-timeline-item">
                            <span class="in-timeline-dot"></span>
                            <div>
                                <strong style="font-size:12px">{{ $item['titulo'] }}</strong>
                                <div style="font-size:11px;color:var(--in-muted)">{{ $item['detalle'] }}</div>
                            </div>
                        </div>
                    @empty
                        <p class="in-empty">Sin actividad reciente.</p>
                    @endforelse
                </x-institucional.card>

                <x-institucional.card title="Comunicados" style="margin-top:16px">
                    @forelse ($comunicados as $com)
                        <div class="in-timeline-item">
                            <span class="in-timeline-dot"></span>
                            <div>
                                <strong style="font-size:12px">{{ $com->titulo }}</strong>
                                <div style="font-size:11px;color:var(--in-muted)">{{ $com->destinatarios }} destinatarios · {{ $com->canal }}</div>
                            </div>
                        </div>
                    @empty
                        <p class="in-empty">Sin comunicados enviados.</p>
                    @endforelse
                    <a class="in-link" href="{{ route('institucional.comunicacion.index') }}">Centro de comunicación →</a>
                </x-institucional.card>

                <x-institucional.card title="Plan institucional" subtitle="Convenio activo" style="margin-top:16px">
                    @if ($plan)
                        <div class="in-convenio-banner" style="margin-bottom:12px;padding:12px">
                            <strong>{{ $plan->nombre }}</strong>
                            <span class="in-badge is-blue">{{ $plan->modalidad ?? 'VIRTUAL' }}</span>
                        </div>
                        @if (! empty($beneficios))
                            <ul class="in-benefits">
                                @foreach ($beneficios as $b)
                                    @if (trim($b) !== '')
                                        <li>{{ trim($b) }}</li>
                                    @endif
                                @endforeach
                            </ul>
                        @else
                            <p class="in-empty">Beneficios del plan disponibles en la sección Plan.</p>
                        @endif
                        <a class="in-link" href="{{ route('institucional.plan.index') }}">Ver plan completo →</a>
                    @else
                        <p class="in-empty">Sin plan asignado. Contacta a FigueFit.</p>
                    @endif
                </x-institucional.card>
            </div>
        </div>
    </div>
@endsection
