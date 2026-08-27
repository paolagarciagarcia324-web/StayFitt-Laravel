@extends('layouts.cliente')

@section('title', 'Inicio')

@section('content')
    @php
        $planNombre = $planActivo?->plan?->nombre ?? 'Sin plan activo';
        $moduloTiles = [
            'ENTRENAMIENTO' => ['route' => 'cliente.entrenamiento.index', 'label' => 'Entrenar', 'hint' => 'Rutinas y planes', 'pink' => true],
            'NUTRICION' => ['route' => 'cliente.nutricion.index', 'label' => 'Nutrición', 'hint' => 'Menús y guías', 'pink' => false],
            'CONTENIDO_VIRTUAL' => ['route' => 'cliente.contenido.index', 'label' => 'Videos', 'hint' => 'Contenido on-demand', 'pink' => true],
            'PROGRESO' => ['route' => 'cliente.progreso.index', 'label' => 'Progreso', 'hint' => 'Métricas corporales', 'pink' => false],
            'AGENDA' => ['route' => 'cliente.agenda.index', 'label' => 'Agenda', 'hint' => 'Sesiones y horarios', 'pink' => true],
            'COMUNICACION' => ['route' => 'cliente.comunicacion.index', 'label' => 'Comunidad', 'hint' => 'Chat y grupos', 'pink' => false],
            'TIENDA' => ['route' => 'cliente.tienda.index', 'label' => 'Tienda', 'hint' => 'Productos FigueFit', 'pink' => true],
        ];
    @endphp

    <section class="cv-hero">
        <div class="cv-avatar">
            @if ($user->avatarUrl())
                <img src="{{ $user->avatarUrl() }}" alt="{{ $user->nombres }}">
            @else
                {{ $user->iniciales() }}
            @endif
        </div>

        <div>
            <div class="cv-kicker">Tu espacio member</div>
            <h1 class="cv-title">Hola, {{ $user->nombres ?? 'atleta' }}</h1>
            <p class="cv-sub">
                {{ $cliente->modalidad ?? 'Membresía' }} · {{ $cliente->estado_cliente }}
                @if ($ultimoProgreso?->peso_kg)
                    · Último peso {{ $ultimoProgreso->peso_kg }} kg
                @endif
            </p>
            <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:12px">
                <span class="cv-badge is-pink">{{ $planNombre }}</span>
                @if ($planActivo?->fecha_fin)
                    <span class="cv-badge is-cyan">Vence {{ optional($planActivo->fecha_fin)->format('d/m/Y') }}</span>
                @endif
                @if ($cliente->diasRestantesPlan() !== null)
                    <span class="cv-badge is-ok">{{ $cliente->diasRestantesPlan() }} días restantes</span>
                @endif
            </div>
        </div>

        <div class="cv-hero__actions">
            @php $codes = $modulosActivos->map->codigoCanonico(); @endphp
            @if ($codes->contains('ENTRENAMIENTO'))
                <a class="cv-btn" href="{{ route('cliente.entrenamiento.index') }}">Entrenar hoy</a>
            @elseif ($codes->contains('CONTENIDO_VIRTUAL'))
                <a class="cv-btn" href="{{ route('cliente.contenido.index') }}">Ver videos</a>
            @else
                <a class="cv-btn" href="{{ route('cliente.plan.index') }}">Ver mi plan</a>
            @endif
            @if (strtoupper((string) $cliente->modalidad) === 'PRESENCIAL')
                <a class="cv-btn cv-btn-ghost" href="{{ route('cliente.presencial.perfil') }}">Ficha presencial</a>
            @else
                <a class="cv-btn cv-btn-ghost" href="{{ route('cliente.perfil.edit') }}">Mi perfil</a>
            @endif
            <a class="cv-btn cv-btn-ghost" href="{{ route('cliente.analisis.index') }}">Ver análisis</a>
        </div>
    </section>

    <div class="cv-metrics">
        <x-cliente.metric label="Notificaciones" :value="$noLeidas" hint="Sin leer" />
        <x-cliente.metric label="Días de plan" :value="$cliente->diasRestantesPlan() ?? '—'" hint="Membresía vigente" />
        <x-cliente.metric label="Rutinas (7 días)" :value="$rutinasCompletadas" hint="Completadas" />
        <x-cliente.metric label="Pagos pendientes" :value="$pagosPendientes" hint="Por regularizar" />
    </div>

    <div class="cv-grid-12">
        <div class="cv-span-7">
            <x-cliente.card title="Accesos rápidos" subtitle="Módulos habilitados en tu plan">
                @if ($modulosActivos->isEmpty())
                    <p class="cv-empty">Aún no tienes módulos habilitados. Contacta a FigueFit si tu plan ya está activo.</p>
                @else
                    <div class="cv-tiles">
                        @foreach ($modulosActivos as $mod)
                            @php
                                $code = $mod->codigoCanonico();
                                $tile = $moduloTiles[$code] ?? null;
                            @endphp
                            @if ($tile)
                                <a class="cv-tile" href="{{ route($tile['route']) }}">
                                    <div class="cv-tile__ico {{ $tile['pink'] ? 'is-pink' : '' }}">→</div>
                                    <strong>{{ $tile['label'] }}</strong>
                                    <span>{{ $tile['hint'] }}</span>
                                </a>
                            @endif
                        @endforeach
                        <a class="cv-tile" href="{{ route('cliente.comercial.planes') }}">
                            <div class="cv-tile__ico">+</div>
                            <strong>Planes</strong>
                            <span>Explorar membresías</span>
                        </a>
                        <a class="cv-tile" href="{{ route('cliente.notificaciones.index') }}">
                            <div class="cv-tile__ico is-pink">!</div>
                            <strong>Avisos</strong>
                            <span>{{ $noLeidas }} sin leer</span>
                        </a>
                    </div>
                @endif
            </x-cliente.card>
        </div>

        <div class="cv-span-5">
            <x-cliente.card title="Tu membresía" subtitle="Estado y plan activo">
                <div class="cv-profile-rail">
                    <div class="cv-row">
                        <span>Estado</span>
                        <strong>{{ $cliente->estado_cliente }}</strong>
                    </div>
                    <div class="cv-row">
                        <span>Modalidad</span>
                        <strong>{{ $cliente->modalidad }}</strong>
                    </div>
                    <div class="cv-row">
                        <span>Plan</span>
                        <strong>{{ $planNombre }}</strong>
                    </div>
                    <div class="cv-row">
                        <span>Vence</span>
                        <strong>{{ optional($planActivo?->fecha_fin)->format('d/m/Y') ?? '—' }}</strong>
                    </div>
                    @if ($ultimoProgreso)
                        <div class="cv-row">
                            <span>Último peso</span>
                            <strong>{{ $ultimoProgreso->peso_kg ? $ultimoProgreso->peso_kg.' kg' : '—' }}</strong>
                        </div>
                    @endif
                </div>

                @if ($modulosActivos->isNotEmpty())
                    <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:14px">
                        @foreach ($modulosActivos as $mod)
                            <span class="cv-badge is-cyan">{{ $mod->nombreCanonico() }}</span>
                        @endforeach
                    </div>
                @endif

                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:16px">
                    <a class="cv-btn cv-btn-sm" href="{{ route('cliente.plan.index') }}">Ver membresía</a>
                    <a class="cv-btn cv-btn-ghost cv-btn-sm" href="{{ route('cliente.pagos.index') }}">Pagos</a>
                </div>
            </x-cliente.card>
        </div>
    </div>

    @if ($invitaciones->isNotEmpty())
        <x-cliente.card title="WhatsApp presencial" subtitle="Invitaciones pendientes a tu grupo">
            <div class="cv-list-feed">
                @foreach ($invitaciones as $inv)
                    <article class="cv-feed-item">
                        <div class="cv-tile__ico is-pink">WA</div>
                        <div>
                            <strong>Únete al grupo de tu horario presencial</strong>
                            <p style="margin:4px 0 0;color:var(--cv-faint);font-size:12px">Confirma para recibir avisos y coordinación con tu coach.</p>
                        </div>
                        <div style="display:flex;flex-wrap:wrap;gap:6px">
                            <form method="POST" action="{{ route('cliente.comunicacion.whatsapp.aceptar', $inv->id_invitacion_wa) }}">
                                @csrf
                                <button class="cv-btn cv-btn-sm" type="submit">Unirme</button>
                            </form>
                            <form method="POST" action="{{ route('cliente.comunicacion.whatsapp.cerrar', $inv->id_invitacion_wa) }}">
                                @csrf
                                <button class="cv-btn cv-btn-ghost cv-btn-sm" type="submit">Ahora no</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        </x-cliente.card>
    @endif

    @if ($novedades->isNotEmpty())
        <div>
            <div class="cv-section-head">
                <div>
                    <h2>Novedades</h2>
                    <p>Lo nuevo en FigueFit para ti</p>
                </div>
                <a class="cv-btn cv-btn-ghost cv-btn-sm" href="{{ route('cliente.novedades.index') }}">Ver todas</a>
            </div>

            <div class="cv-media-grid">
                @foreach ($novedades as $nov)
                    <article class="cv-media">
                        <div class="cv-media__cover">
                            @if ($nov->imagenSrc())
                                <img src="{{ $nov->imagenSrc() }}" alt="{{ $nov->titulo }}">
                            @endif
                            <div class="cv-media__cover-meta">
                                <span class="cv-badge is-pink">{{ $nov->etiquetaTipo() }}</span>
                            </div>
                        </div>
                        <div class="cv-media__body">
                            <h3>{{ $nov->titulo }}</h3>
                            <p>{{ \Illuminate\Support\Str::limit($nov->descripcion ?: $nov->subtitulo, 110) }}</p>
                            <a class="cv-btn cv-btn-ghost cv-btn-sm" href="{{ $nov->destinoUrl() }}">{{ $nov->boton_texto ?: 'Ver más' }}</a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    @endif
@endsection
