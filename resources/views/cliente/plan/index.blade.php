@extends('layouts.cliente')

@section('title', 'Mi plan')

@section('content')
    @php
        $planNombre = $planActivo?->plan?->nombre ?? 'Sin plan activo';
        $coachNombre = trim(($cliente->coach?->usuario?->nombres ?? '').' '.($cliente->coach?->usuario?->apellidos ?? ''));
        $accesosActivos = $accesos->where('estado_acceso', 'ACTIVO');
        $moduloTiles = [
            'ENTRENAMIENTO' => ['route' => 'cliente.entrenamiento.index', 'label' => 'Entrenar'],
            'NUTRICION' => ['route' => 'cliente.nutricion.index', 'label' => 'Nutrición'],
            'CONTENIDO_VIRTUAL' => ['route' => 'cliente.contenido.index', 'label' => 'Videos'],
            'PROGRESO' => ['route' => 'cliente.progreso.index', 'label' => 'Progreso'],
            'AGENDA' => ['route' => 'cliente.agenda.index', 'label' => 'Agenda'],
            'COMUNICACION' => ['route' => 'cliente.comunicacion.index', 'label' => 'Comunidad'],
            'TIENDA' => ['route' => 'cliente.tienda.index', 'label' => 'Tienda'],
        ];
    @endphp

    <x-cliente.page-head
        title="Mi membresía"
        subtitle="Suscripción, accesos y coach asignado"
        kicker="Plan activo"
    >
        <x-slot:actions>
            <a class="cv-btn cv-btn-ghost cv-btn-sm" href="{{ route('cliente.comercial.planes') }}">Explorar planes</a>
            <a class="cv-btn cv-btn-sm" href="{{ route('cliente.pagos.index') }}">Ver pagos</a>
        </x-slot:actions>
    </x-cliente.page-head>

    @if ($planActivo)
        <section class="cv-plan is-featured">
            <span class="cv-plan__ribbon"><span class="cv-badge is-pink">Plan activo</span></span>

            <div class="cv-kicker">Membresía vigente</div>
            <h2 class="cv-title" style="margin-top:4px">{{ $planNombre }}</h2>
            <p class="cv-sub" style="max-width:none">
                {{ $planActivo->plan?->descripcion ?: 'Tu suscripción FigueFit con acceso a módulos premium.' }}
            </p>

            <div class="cv-profile-rail">
                <div class="cv-row">
                    <span>Inicio</span>
                    <strong>{{ optional($planActivo->fecha_inicio)->format('d/m/Y') ?? $planActivo->fecha_inicio }}</strong>
                </div>
                <div class="cv-row">
                    <span>Fin</span>
                    <strong>{{ optional($planActivo->fecha_fin)->format('d/m/Y') ?? $planActivo->fecha_fin }}</strong>
                </div>
                <div class="cv-row">
                    <span>Estado</span>
                    <strong>{{ $planActivo->estado_plan_cliente }}</strong>
                </div>
                <div class="cv-row">
                    <span>Coach</span>
                    <strong>{{ $coachNombre !== '' ? $coachNombre : 'Por asignar' }}</strong>
                </div>
            </div>

            @if ($planActivo->plan?->beneficios)
                <ul>
                    @foreach (preg_split('/\r\n|\r|\n/', $planActivo->plan->beneficios) as $beneficio)
                        @if (trim($beneficio) !== '')
                            <li>{{ trim($beneficio) }}</li>
                        @endif
                    @endforeach
                </ul>
            @endif

            @if ($cliente->diasRestantesPlan() !== null && $planActivo->fecha_fin)
                @php
                    $diasTotales = max(1, optional($planActivo->fecha_inicio)->diffInDays($planActivo->fecha_fin) ?: 1);
                    $diasRestantes = max(0, (int) $cliente->diasRestantesPlan());
                    $pct = min(100, max(0, round(($diasRestantes / $diasTotales) * 100)));
                @endphp
                <div>
                    <div style="display:flex;justify-content:space-between;font-size:12px;color:var(--cv-muted);margin-bottom:6px">
                        <span>Tiempo restante</span>
                        <span>{{ $diasRestantes }} días</span>
                    </div>
                    <div class="cv-progress"><i style="width:{{ $pct }}%"></i></div>
                </div>
            @endif
        </section>
    @else
        <x-cliente.card title="Sin plan activo" subtitle="Explora opciones comerciales para activar tu membresía">
            <p class="cv-empty">No tienes un plan activo en este momento.</p>
            <a class="cv-btn" href="{{ route('cliente.comercial.planes') }}">Ver planes disponibles</a>
        </x-cliente.card>
    @endif

    <div class="cv-metrics">
        <x-cliente.metric label="Estado cuenta" :value="$cliente->estado_cliente" hint="Membresía" />
        <x-cliente.metric label="Modalidad" :value="$cliente->modalidad" hint="Presencial / virtual" />
        <x-cliente.metric label="Días restantes" :value="$cliente->diasRestantesPlan() ?? '—'" hint="Plan vigente" />
        <x-cliente.metric label="Racha" :value="$cliente->diasRacha().' días'" hint="Actividad consecutiva" />
    </div>

    <div class="cv-grid-12">
        <div class="cv-span-7">
            <x-cliente.card title="Módulos habilitados" subtitle="Accesos incluidos en tu suscripción">
                @if ($accesosActivos->isEmpty())
                    <p class="cv-empty">Sin accesos vigentes en este momento.</p>
                @else
                    <div class="cv-tiles">
                        @foreach ($accesosActivos as $acceso)
                            @php
                                $code = $acceso->modulo?->codigoCanonico();
                                $tile = $code ? ($moduloTiles[$code] ?? null) : null;
                                $nombre = $acceso->modulo?->nombreCanonico() ?? 'Módulo';
                            @endphp
                            @if ($tile)
                                <a class="cv-tile" href="{{ route($tile['route']) }}">
                                    <div class="cv-tile__ico is-pink">✓</div>
                                    <strong>{{ $nombre }}</strong>
                                    <span>Hasta {{ optional($acceso->fecha_fin)->format('d/m/Y') ?? $acceso->fecha_fin }}</span>
                                </a>
                            @else
                                <div class="cv-tile">
                                    <div class="cv-tile__ico">✓</div>
                                    <strong>{{ $nombre }}</strong>
                                    <span>Hasta {{ optional($acceso->fecha_fin)->format('d/m/Y') ?? $acceso->fecha_fin }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </x-cliente.card>
        </div>

        <div class="cv-span-5">
            <x-cliente.card title="Historial de planes" subtitle="Suscripciones anteriores">
                @if ($historial->isEmpty())
                    <p class="cv-empty">Sin historial de membresías.</p>
                @else
                    <div class="cv-list-feed">
                        @foreach ($historial as $pc)
                            <article class="cv-feed-item">
                                <div class="cv-tile__ico {{ $pc->estado_plan_cliente === 'ACTIVO' ? 'is-pink' : '' }}">P</div>
                                <div>
                                    <strong>{{ $pc->plan?->nombre ?? 'Plan' }}</strong>
                                    <p style="margin:4px 0 0;color:var(--cv-faint);font-size:12px">
                                        {{ $pc->estado_plan_cliente }}
                                        · {{ optional($pc->fecha_inicio)->format('d/m/Y') }}
                                        — {{ optional($pc->fecha_fin)->format('d/m/Y') ?? $pc->fecha_fin }}
                                    </p>
                                </div>
                                <span class="cv-badge">{{ optional($pc->fecha_fin)->format('d/m/y') ?? '—' }}</span>
                            </article>
                        @endforeach
                    </div>
                @endif
            </x-cliente.card>
        </div>
    </div>
@endsection
