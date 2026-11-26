@extends('layouts.admin')

@section('title', 'Calendario')
@section('subtitle')
    <span class="ad-page-intro">
        <span class="ad-page-intro-lead">Agenda operativa de eventos y sesiones.</span>
        <span class="ad-page-intro-note">Fija fechas, evita solapes de coach y recibe recordatorios de lo que viene.</span>
    </span>
@endsection

@section('flash')
@endsection

@section('actions')
    <button class="ad-pend-btn is-fucsia ad-cal-create-btn" type="button" data-open-sesion-form>+ Añadir sesión</button>
    <a class="ad-pend-btn is-menta ad-cal-create-btn" href="{{ route('admin.eventos.index') }}">Eventos</a>
@endsection

@section('content')
    @php
        $anchor = $mes->isSameMonth(now()) ? now() : $mes->copy()->startOfMonth();
        $semanaCeldas = collect($celdas)->filter(fn ($c) => $c['fecha']->isSameWeek($anchor, \Illuminate\Support\Carbon::MONDAY))->values();
        if ($semanaCeldas->isEmpty()) {
            $semanaCeldas = collect($celdas)->take(7)->values();
        }
        $horasTf = ['06:00', '07:00', '08:00', '09:00', '10:00', '16:00', '17:00', '18:00'];
        $sesionesHoy = $sesiones->filter(function ($s) {
            $f = $s->fecha instanceof \Illuminate\Support\Carbon ? $s->fecha : \Illuminate\Support\Carbon::parse($s->fecha);
            return $f->isToday();
        });
        // Semanas Lun–Vie que tocan el mes (incluye días adyacentes, como el plan físico)
        $weekKeys = collect($celdas)
            ->filter(fn ($c) => $c['enMes'])
            ->map(fn ($c) => $c['fecha']->copy()->startOfWeek(\Illuminate\Support\Carbon::MONDAY)->toDateString())
            ->unique()
            ->values();
        $boardWeeks = $weekKeys->map(function ($weekStart) use ($celdas) {
            return collect($celdas)
                ->filter(function ($c) use ($weekStart) {
                    $ws = $c['fecha']->copy()->startOfWeek(\Illuminate\Support\Carbon::MONDAY)->toDateString();

                    return $ws === $weekStart && $c['fecha']->dayOfWeekIso <= 5;
                })
                ->values();
        })->filter(fn ($w) => $w->isNotEmpty())->values();
        $mesLabelCal = ucfirst($mes->locale('es')->translatedFormat('F Y'));
    @endphp

    <header class="ad-evt-month-head ad-cal-evt-month">
        <a class="ad-evt-month-nav" href="{{ route('admin.calendario.index', ['mes' => $mesAnterior]) }}" aria-label="Mes anterior">‹</a>
        <div class="ad-evt-month-label">
            <em>Agenda</em>
            <strong>{{ $mesLabelCal }}</strong>
        </div>
        <a class="ad-evt-month-nav" href="{{ route('admin.calendario.index', ['mes' => $mesSiguiente]) }}" aria-label="Mes siguiente">›</a>
    </header>

    <div class="ad-cal-toolbar">
        <div class="ad-seg ad-cal-seg" role="tablist" aria-label="Vista del calendario">
            <button type="button" data-cal-switch="month" class="is-on" role="tab" aria-selected="true">Mes</button>
            <button type="button" data-cal-switch="week" role="tab" aria-selected="false">Semana</button>
            <button type="button" data-cal-switch="day" role="tab" aria-selected="false">Día</button>
        </div>
        <p class="ad-cal-toolbar-hint" data-cal-hint>Vista mes: sesiones por fecha y ejercicio. Usa Semana o Día para entrar al detalle.</p>
        <div class="ad-dash-hero-metrics">
            <div class="ad-dash-metric">
                <strong data-count="{{ $sesionesHoy->count() }}">{{ $sesionesHoy->count() }}</strong>
                <span>Sesiones hoy</span>
            </div>
            <div class="ad-dash-metric">
                <strong data-count="{{ $sesiones->count() }}">{{ $sesiones->count() }}</strong>
                <span>Sesiones del mes</span>
            </div>
        </div>
    </div>

    {{-- MES: tablero fecha + ejercicio --}}
    <section class="ad-cal-layout ad-cal-board-wrap" data-cal-view="month">
        <section class="ad-glass-card ad-cal-board-card">
            <header class="ad-cal-board-head">
                <div>
                    <h3>Plan de sesiones</h3>
                    <p>Cada celda muestra la fecha y el ejercicio / sesión programada.</p>
                </div>
                <div class="ad-cal-legend">
                    <span><i class="is-sesion"></i> Sesión</span>
                    <span><i class="is-evento"></i> Evento</span>
                    <span><i class="is-cancel"></i> Cancelada</span>
                </div>
            </header>

            <div class="ad-cal-board">
                <div class="ad-cal-board-row is-head">
                    @foreach (['Lunes','Martes','Miércoles','Jueves','Viernes'] as $dow)
                        <div class="ad-cal-board-dow">{{ $dow }}</div>
                    @endforeach
                </div>
                @forelse ($boardWeeks as $week)
                    <div class="ad-cal-board-row">
                        @for ($iso = 1; $iso <= 5; $iso++)
                            @php
                                $celda = collect($week)->first(fn ($c) => (int) $c['fecha']->dayOfWeekIso === $iso);
                            @endphp
                            @if ($celda)
                                @php
                                    $fecha = $celda['fecha'];
                                    $sess = $celda['sesiones'];
                                    $evs = $celda['eventos'];
                                    $primera = $sess->first();
                                    $tituloUp = mb_strtoupper((string) ($primera->titulo ?? ''));
                                    $cancelada = ($primera && $primera->estado === 'CANCELADA')
                                        || str_contains($tituloUp, 'NO HAY CLASE');
                                    $especial = str_contains($tituloUp, 'CIERRE')
                                        || ($evs->isNotEmpty() && $sess->isEmpty());
                                @endphp
                                <button
                                    type="button"
                                    class="ad-cal-board-cell {{ $celda['enMes'] ? '' : 'is-out' }} {{ $celda['hoy'] ? 'is-today' : '' }} {{ $cancelada ? 'is-cancel' : '' }} {{ $especial ? 'is-special' : '' }} {{ $sess->isNotEmpty() || $evs->isNotEmpty() ? 'has-item' : '' }}"
                                    data-cal-day-modal="{{ $fecha->toDateString() }}"
                                    title="Ver sesión · {{ $fecha->format('d/m/Y') }}"
                                >
                                    <span class="ad-cal-board-day">{{ $fecha->day }}</span>
                                    <div class="ad-cal-board-body">
                                        @forelse ($sess->take(2) as $sesion)
                                            @php
                                                $t = mb_strtoupper((string) $sesion->titulo);
                                                $isCancelTxt = $sesion->estado === 'CANCELADA' || str_contains($t, 'NO HAY CLASE');
                                                $isSpecialTxt = str_contains($t, 'CIERRE');
                                            @endphp
                                            <strong class="{{ $isCancelTxt ? 'is-cancel-txt' : ($isSpecialTxt ? 'is-special-txt' : '') }}">
                                                {{ $t }}
                                            </strong>
                                            @unless ($isCancelTxt)
                                                <em>{{ $sesion->horaInicioCorta() }}</em>
                                            @endunless
                                        @empty
                                            @forelse ($evs->take(1) as $evento)
                                                <strong class="is-special-txt">{{ mb_strtoupper($evento->nombre) }}</strong>
                                                <em>Evento</em>
                                            @empty
                                                <span class="ad-cal-board-empty">Sin sesión</span>
                                            @endforelse
                                        @endforelse
                                        @if ($sess->count() + $evs->count() > 2)
                                            <span class="ad-cal-board-more">+{{ $sess->count() + $evs->count() - 2 }} más</span>
                                        @endif
                                    </div>
                                </button>
                            @else
                                <div class="ad-cal-board-cell is-blank" aria-hidden="true"></div>
                            @endif
                        @endfor
                    </div>
                @empty
                    <div class="ad-empty-hero" style="grid-column:1/-1;padding:28px">
                        <strong>Sin días laborables este mes</strong>
                    </div>
                @endforelse
            </div>

            @if ($eventos->isEmpty() && $sesiones->isEmpty())
                <div class="ad-empty-hero">
                    <span class="ad-empty-orb">@include('partials.admin-icon', ['icon' => 'calendario'])</span>
                    <strong>Sin actividad este mes</strong>
                    <button class="ad-pend-btn is-fucsia" type="button" data-open-sesion-form>Añadir sesión</button>
                </div>
            @endif
        </section>

        <aside class="ad-card ad-cal-reminders" data-cal-reminders>
            <button type="button" class="ad-cal-rem-toggle" data-cal-rem-toggle aria-expanded="false">
                <span class="ad-cal-rem-toggle-main">
                    <strong>Recordatorios</strong>
                    <small>Próximos 21 días</small>
                </span>
                @if (($recordatoriosCount ?? 0) > 0)
                    <span class="ad-cal-rem-count" aria-label="{{ $recordatoriosCount }} próximos">
                        <svg viewBox="0 0 36 36" width="34" height="34" aria-hidden="true">
                            <circle cx="18" cy="18" r="15.5" fill="none" stroke="rgba(46,245,230,0.22)" stroke-width="2"/>
                            <circle cx="18" cy="18" r="15.5" fill="none" stroke="url(#adRemGrad)" stroke-width="2.2" stroke-linecap="round"
                                stroke-dasharray="97" stroke-dashoffset="{{ max(8, 97 - min(97, (int) $recordatoriosCount * 12)) }}"
                                transform="rotate(-90 18 18)"/>
                            <defs>
                                <linearGradient id="adRemGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#2ef5e6"/>
                                    <stop offset="100%" stop-color="#ff2d78"/>
                                </linearGradient>
                            </defs>
                        </svg>
                        <strong>{{ $recordatoriosCount }}</strong>
                    </span>
                @endif
                <span class="ad-cal-rem-chevron" aria-hidden="true">▾</span>
            </button>
            <div class="ad-cal-rem-panel" data-cal-rem-panel hidden>
                @forelse ($recordatorios as $item)
                    <article class="ad-cal-rem {{ $item['urgencia'] === 'alta' ? 'is-alta' : ($item['urgencia'] === 'media' ? 'is-media' : '') }}">
                        <span class="ad-cal-rem-tag">{{ $item['tipo'] === 'evento' ? 'Evento' : 'Sesión' }}</span>
                        <strong>{{ $item['titulo'] }}</strong>
                        <em>{{ $item['cuando'] }}</em>
                        <p>{{ $item['detalle'] }}</p>
                        @if (! empty($item['href']))
                            <a href="{{ $item['href'] }}">Ver en eventos</a>
                        @endif
                    </article>
                @empty
                    <div class="ad-empty-hero" style="padding:20px">
                        <strong>Sin recordatorios próximos</strong>
                        <p class="ad-page-intro-note">Cuando haya eventos o sesiones en los próximos días, aparecerán aquí. Al cumplirse la fecha salen solos.</p>
                    </div>
                @endforelse
            </div>
        </aside>
    </section>

    {{-- SEMANA: detalle horario --}}
    <div class="ad-tf" data-cal-view="week" hidden>
        <aside class="ad-tf-side">
            <section class="ad-glass-card">
                <strong>{{ ucfirst($mes->locale('es')->translatedFormat('F Y')) }}</strong>
                <div class="ad-tf-mini">
                    @foreach (['L','M','X','J','V','S','D'] as $d)
                        <em>{{ $d }}</em>
                    @endforeach
                    @foreach ($celdas as $celda)
                        <button type="button"
                            class="{{ $celda['hoy'] ? 'is-today' : '' }} {{ $celda['enMes'] ? '' : 'is-muted' }} {{ ! empty($celda['hasItems']) ? 'has-events' : '' }}"
                            data-open-sesion-day
                            data-fecha="{{ $celda['fecha']->toDateString() }}"
                            data-cal-goto-day="{{ $celda['fecha']->toDateString() }}">
                            {{ $celda['fecha']->day }}
                        </button>
                    @endforeach
                </div>
            </section>
            <section class="ad-glass-card">
                <strong>Hoy en sala</strong>
                <div class="ad-check-list">
                    @forelse ($sesionesHoy as $sesion)
                        <label><input type="checkbox" disabled checked> {{ $sesion->horaInicioCorta() }} · {{ $sesion->titulo }}</label>
                    @empty
                        <p class="ad-user-mail">Sin sesiones hoy. Clic en un día para fijar una.</p>
                    @endforelse
                </div>
            </section>
        </aside>
        <section class="ad-glass-card ad-tf-week">
            <header class="ad-tf-week-head">
                <strong>Detalle semana {{ $semanaCeldas->first()['fecha']->format('d') }} – {{ $semanaCeldas->last()['fecha']->format('d M') }}</strong>
                <em class="ad-user-mail">Filtro de detalle · clic en bloque para crear / revisar</em>
            </header>
            <div class="ad-tf-grid">
                <span></span>
                @foreach ($semanaCeldas as $dia)
                    <strong class="{{ $dia['hoy'] ? 'is-today' : '' }}">{{ $dia['fecha']->locale('es')->isoFormat('ddd DD') }}</strong>
                @endforeach
                @foreach ($horasTf as $hora)
                    <em>{{ \App\Models\HorarioPresencial::formatoHora12($hora) }}</em>
                    @foreach ($semanaCeldas as $dia)
                        @php
                            $items = $dia['sesiones']->filter(fn ($s) => substr((string) $s->hora_inicio, 0, 5) === $hora)
                                ->merge($dia['eventos']->filter(fn ($e) => $e->fecha_inicio && $e->fecha_inicio->format('H:i') === $hora));
                        @endphp
                        <button type="button" class="ad-tf-cell {{ $items->isNotEmpty() ? 'has-item' : '' }}"
                            data-open-sesion-day
                            data-fecha="{{ $dia['fecha']->toDateString() }}">
                            @foreach ($items->take(2) as $item)
                                <span>{{ $item->titulo ?? $item->nombre }}</span>
                            @endforeach
                        </button>
                    @endforeach
                @endforeach
            </div>
        </section>
    </div>

    {{-- DÍA: detalle por fecha --}}
    <div class="ad-glass-card ad-cal-day-detail" data-cal-view="day" hidden>
        <header class="ad-cal-day-head">
            <div>
                <em class="ad-cal-day-kicker">Detalle del día</em>
                <h3 data-cal-day-title>{{ $anchor->locale('es')->isoFormat('dddd D [de] MMMM') }}</h3>
            </div>
            <button type="button" class="ad-pend-btn is-fucsia" data-open-sesion-form>+ Sesión</button>
        </header>
        <div class="ad-cal-day-list" data-cal-day-list>
            @php
                $diaAnchorKey = $anchor->toDateString();
                $celdaDia = collect($celdas)->first(fn ($c) => $c['fecha']->toDateString() === $diaAnchorKey);
                $sessDia = $celdaDia['sesiones'] ?? collect();
                $evsDia = $celdaDia['eventos'] ?? collect();
            @endphp
            @forelse ($sessDia as $sesion)
                <article class="ad-cal-day-item {{ $sesion->estado === 'CANCELADA' ? 'is-cancel' : '' }}" data-cal-day-item data-fecha="{{ optional($sesion->fecha)->format('Y-m-d') }}">
                    <span class="ad-cal-day-time">{{ $sesion->horaInicioCorta() }}</span>
                    <div>
                        <strong>{{ mb_strtoupper($sesion->titulo) }}</strong>
                        <em>{{ $sesion->tipo }} · {{ $sesion->modalidad }} · {{ $sesion->estado }}</em>
                    </div>
                </article>
            @empty
                @foreach ($evsDia as $evento)
                    <article class="ad-cal-day-item is-special" data-cal-day-item data-fecha="{{ $diaAnchorKey }}">
                        <span class="ad-cal-day-time">{{ $evento->fecha_inicio ? \App\Models\HorarioPresencial::formatoHora12($evento->fecha_inicio->format('H:i')) : '—' }}</span>
                        <div>
                            <strong>{{ mb_strtoupper($evento->nombre) }}</strong>
                            <em>Evento · {{ $evento->modalidad }}</em>
                        </div>
                    </article>
                @endforeach
                @if ($evsDia->isEmpty())
                    <p class="ad-empty" data-cal-day-empty>Nada programado este día. Clic en + Sesión para fijar una.</p>
                @endif
            @endforelse
        </div>
        @php
            $calDayData = [];
            foreach ($celdas as $c) {
                $key = $c['fecha']->toDateString();
                $calDayData[$key] = [
                    'label' => $c['fecha']->locale('es')->isoFormat('dddd D [de] MMMM'),
                    'sesiones' => $c['sesiones']->map(static function ($s) {
                        $fechaVal = optional($s->fecha)->format('Y-m-d') ?: (string) $s->fecha;

                        return [
                            'id' => (int) $s->id_sesion,
                            'titulo' => $s->titulo,
                            'hora' => $s->horaInicioCorta(),
                            'hora_inicio' => substr((string) $s->hora_inicio, 0, 5),
                            'hora_fin' => substr((string) $s->hora_fin, 0, 5),
                            'fecha' => $fechaVal,
                            'id_coach' => (string) $s->id_coach,
                            'tipo' => $s->tipo,
                            'modalidad' => $s->modalidad,
                            'cupo_maximo' => (string) $s->cupo_maximo,
                            'estado' => $s->estado,
                        ];
                    })->values()->all(),
                    'eventos' => $c['eventos']->map(static function ($e) {
                        return [
                            'id' => (int) $e->id_evento,
                            'nombre' => $e->nombre,
                            'hora' => $e->fecha_inicio
                                ? \App\Models\HorarioPresencial::formatoHora12($e->fecha_inicio->format('H:i'))
                                : '—',
                            'modalidad' => $e->modalidad,
                        ];
                    })->values()->all(),
                ];
            }
        @endphp
        <script type="application/json" id="ad-cal-day-data">@json($calDayData)</script>
    </div>

    <section class="ad-card ad-users ad-cal-archive" style="margin-top:16px">
        <div class="ad-card-head">
            <h2>Archivo de sesiones</h2>
            <small>Organizadas por mes</small>
        </div>

        <div class="ad-cal-archive-scroll">
        @forelse ($sesionesPorMes as $folder)
            <details class="ad-cal-folder {{ ! empty($folder['is_current']) ? 'is-current' : '' }}" @if (! empty($folder['is_current'])) open @endif>
                <summary class="ad-cal-folder-head">
                    <span class="ad-cal-folder-ico" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 8.5A2.5 2.5 0 0 1 5.5 6H9l2 2h7.5A2.5 2.5 0 0 1 21 10.5v7A2.5 2.5 0 0 1 18.5 20h-13A2.5 2.5 0 0 1 3 17.5v-9Z"/>
                        </svg>
                    </span>
                    <span class="ad-cal-folder-title">
                        <strong>{{ $folder['label'] }}</strong>
                        @if (! empty($folder['is_current']))
                            <em>Mes actual</em>
                        @endif
                    </span>
                    <span class="ad-cal-folder-meta">
                        <span class="ad-cal-folder-count">{{ $folder['count'] }}</span>
                        <span class="ad-cal-folder-chevron" aria-hidden="true">▾</span>
                    </span>
                </summary>
                <div class="ad-cal-folder-body">
                    @forelse ($folder['sesiones'] as $sesion)
                        @php
                            $fechaVal = optional($sesion->fecha)->format('Y-m-d') ?: (string) $sesion->fecha;
                            $hi = substr((string) $sesion->hora_inicio, 0, 5);
                            $hf = substr((string) $sesion->hora_fin, 0, 5);
                            $coachNombre = $sesion->coach?->usuario?->name
                                ?? $sesion->coach?->usuario?->nombres
                                ?? ('Coach #'.$sesion->id_coach);
                        @endphp
                        <article class="ad-cal-session-row">
                            <div class="ad-cal-session-main">
                                <strong>{{ $sesion->titulo }}</strong>
                                <em>{{ optional($sesion->fecha)->format('d/m/Y') }} · {{ $sesion->rangoHora12() }}</em>
                                <span>{{ $sesion->tipo }} · {{ $coachNombre }} · {{ $sesion->modalidad }}</span>
                            </div>
                            <span class="ad-badge ad-badge-ok">{{ $sesion->estado }}</span>
                            <div class="ad-row-actions">
                                <button class="ad-icon-btn" type="button"
                                    title="Editar"
                                    aria-label="Editar sesión"
                                    data-open-sesion-edit
                                    data-id="{{ $sesion->id_sesion }}"
                                    data-titulo="{{ $sesion->titulo }}"
                                    data-id_coach="{{ $sesion->id_coach }}"
                                    data-tipo="{{ $sesion->tipo }}"
                                    data-fecha="{{ $fechaVal }}"
                                    data-hora_inicio="{{ $hi }}"
                                    data-hora_fin="{{ $hf }}"
                                    data-modalidad="{{ $sesion->modalidad }}"
                                    data-cupo_maximo="{{ $sesion->cupo_maximo }}"
                                    data-estado="{{ $sesion->estado }}">
                                    @include('partials.admin-icon', ['icon' => 'edit'])
                                </button>
                                <form method="POST" action="{{ route('admin.calendario.sesiones.destroy', $sesion->id_sesion) }}" class="ad-inline-form" data-sesion-delete-form>
                                    @csrf
                                    @method('DELETE')
                                    <button class="ad-icon-btn is-danger" type="button" title="Eliminar" aria-label="Eliminar sesión" data-sesion-delete>
                                        @include('partials.admin-icon', ['icon' => 'trash'])
                                    </button>
                                </form>
                            </div>
                        </article>
                    @empty
                        <p class="ad-page-intro-note" style="margin:0;padding:8px 4px">Sin sesiones en este mes.</p>
                    @endforelse
                </div>
            </details>
        @empty
            <div class="ad-empty-hero" style="padding:24px">
                <span class="ad-empty-orb">@include('partials.admin-icon', ['icon' => 'horarios'])</span>
                <strong>Sin sesiones</strong>
                <button class="ad-btn" type="button" data-open-sesion-form>Añadir sesión</button>
            </div>
        @endforelse
        </div>
    </section>

    @php
        $tipos = ['INDIVIDUAL', 'GRUPAL'];
        $modalidades = ['PRESENCIAL', 'VIRTUAL'];
        $estados = ['PROGRAMADA', 'EN_CURSO', 'COMPLETADA', 'CANCELADA'];
    @endphp

    <div class="ad-modal ad-cal-day-modal" data-cal-day-info-modal hidden>
        <div class="ad-modal-card ad-cal-day-modal-card" role="dialog" aria-modal="true" aria-labelledby="ad-cal-day-modal-title">
            <div class="ad-cal-day-modal-glow" aria-hidden="true"></div>
            <header class="ad-modal-head ad-cal-day-modal-head">
                <div>
                    <p class="ad-cal-day-modal-kicker">Detalle del día</p>
                    <h2 id="ad-cal-day-modal-title" data-cal-day-modal-title>—</h2>
                </div>
                <button type="button" class="ad-modal-close" data-cal-day-modal-close aria-label="Cerrar">×</button>
            </header>
            <div class="ad-cal-day-modal-body" data-cal-day-modal-body></div>
            <div class="ad-modal-actions ad-cal-day-modal-actions">
                <button type="button" class="ad-pend-btn is-menta" data-cal-day-modal-close>← Regresar al calendario</button>
                <button type="button" class="ad-pend-btn is-fucsia" data-cal-day-modal-add>+ Sesión</button>
            </div>
        </div>
    </div>

    <div class="ad-modal ad-evt-form-modal" data-sesion-modal hidden>
        <div class="ad-modal-card ad-evt-form-card ad-sesion-form-card">
            <div class="ad-evt-form-glow" aria-hidden="true"></div>
            <header class="ad-modal-head ad-evt-form-head">
                <div>
                    <p class="ad-evt-form-kicker">Calendario · Sesión</p>
                    <h2>Nueva sesión</h2>
                    <p class="ad-sesion-form-note">Misma ficha que edición: título, coach, agenda y estado quedan trazados en el plan.</p>
                </div>
                <button type="button" class="ad-modal-close" data-close-sesion-form aria-label="Cerrar">×</button>
            </header>
            <form class="ad-form ad-evt-form ad-sesion-form" method="POST" action="{{ route('admin.calendario.sesiones.store') }}" data-sesion-create-form>
                @csrf
                @include('admin.calendario._fields', compact('coaches', 'tipos', 'modalidades', 'estados'))
                <div class="ad-modal-actions ad-evt-form-actions">
                    <button class="ad-pend-btn is-menta" type="button" data-close-sesion-form>Cancelar</button>
                    <button class="ad-pend-btn is-fucsia" type="submit">Crear sesión</button>
                </div>
            </form>
        </div>
    </div>

    <div class="ad-modal ad-evt-form-modal" data-sesion-edit-modal hidden>
        <div class="ad-modal-card ad-evt-form-card ad-sesion-form-card">
            <div class="ad-evt-form-glow" aria-hidden="true"></div>
            <header class="ad-modal-head ad-evt-form-head">
                <div>
                    <p class="ad-evt-form-kicker">Calendario · Edición</p>
                    <h2>Editar sesión</h2>
                    <p class="ad-sesion-form-note">Actualiza la programación; el cambio se refleja en el tablero del mes.</p>
                </div>
                <button type="button" class="ad-modal-close" data-close-sesion-edit aria-label="Cerrar">×</button>
            </header>
            <form class="ad-form ad-evt-form ad-sesion-form" method="POST" data-sesion-edit-form>
                @csrf
                @method('PUT')
                <input type="hidden" data-sesion-edit-id value="">
                @include('admin.calendario._fields', ['coaches' => $coaches, 'tipos' => $tipos, 'modalidades' => $modalidades, 'estados' => $estados, 'withDataFields' => true])
                <div class="ad-modal-actions ad-evt-form-actions">
                    <button class="ad-pend-btn is-menta" type="button" data-close-sesion-edit>Cancelar</button>
                    <button class="ad-pend-btn is-fucsia" type="submit">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>

    <div class="ad-modal ad-horario-del-modal" data-sesion-confirm-modal hidden>
        <div class="ad-modal-card ad-del-card" role="dialog" aria-modal="true" aria-labelledby="ad-sesion-del-title">
            <div class="ad-del-glow" aria-hidden="true"></div>
            <header class="ad-modal-head ad-del-head">
                <div class="ad-del-who">
                    <span class="ad-del-orb" aria-hidden="true">@include('partials.admin-icon', ['icon' => 'trash'])</span>
                    <div>
                        <p class="ad-del-kicker">Acción permanente</p>
                        <h2 id="ad-sesion-del-title">Eliminar sesión</h2>
                        <p>Esta acción no se puede deshacer.</p>
                    </div>
                </div>
                <button type="button" class="ad-modal-close" data-sesion-confirm-cancel aria-label="Cerrar">×</button>
            </header>
            <div class="ad-modal-actions ad-del-actions">
                <button type="button" class="ad-btn ad-btn-ghost" data-sesion-confirm-cancel>Cancelar</button>
                <button type="button" class="ad-pend-btn is-menta" data-sesion-confirm-ok>Sí, eliminar</button>
            </div>
        </div>
    </div>

    @php $ok = session('sesion_ok'); @endphp
    @if (is_array($ok) && ! empty($ok['accion']))
        @php
            $accion = $ok['accion'];
            $accionLabel = match ($accion) {
                'creada' => 'Sesión creada',
                'actualizada' => 'Sesión actualizada',
                'eliminada' => 'Sesión eliminada',
                default => 'Acción completada',
            };
            $detalle = match ($accion) {
                'creada' => 'Ya queda reflejada en el calendario y en el listado.',
                'actualizada' => 'Los cambios se guardaron correctamente.',
                'eliminada' => 'La sesión se eliminó del calendario.',
                default => 'La operación terminó correctamente.',
            };
        @endphp
        <div class="ad-modal ad-ok-modal is-open" data-ok-modal role="dialog" aria-modal="true" aria-labelledby="ad-ok-title">
            <div class="ad-modal-card ad-ok-card">
                <div class="ad-ok-confetti" aria-hidden="true">
                    <i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i>
                </div>
                <div class="ad-ok-check" aria-hidden="true">
                    <svg viewBox="0 0 52 52" width="56" height="56">
                        <circle cx="26" cy="26" r="24" fill="none"/>
                        <path fill="none" d="M14 27l8 8 16-16"/>
                    </svg>
                </div>
                <h2 id="ad-ok-title">{{ $accionLabel }}</h2>
                <p class="ad-ok-lead">fue un éxito</p>
                <p class="ad-muted-note">{{ $detalle }}</p>
                <button type="button" class="ad-btn" data-close-ok>Continuar</button>
            </div>
        </div>
    @endif

    @if (isset($errors) && $errors->any())
        <div class="ad-modal ad-warn-modal is-open" data-warn-modal role="dialog" aria-modal="true" aria-labelledby="ad-warn-title">
            <div class="ad-modal-card ad-warn-card">
                <div class="ad-warn-x" aria-hidden="true">
                    <svg viewBox="0 0 52 52" width="56" height="56">
                        <circle cx="26" cy="26" r="24" fill="none"/>
                        <path fill="none" d="M18 18l16 16M34 18 18 34"/>
                    </svg>
                </div>
                <h2 id="ad-warn-title">Revisa los datos</h2>
                <p class="ad-warn-lead">Hay algo que corregir antes de continuar.</p>
                <ul class="ad-warn-list">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="ad-btn is-danger" data-close-warn>Corregir</button>
            </div>
        </div>
    @endif

    <script>
        (function () {
            var createModal = document.querySelector('[data-sesion-modal]');
            var createForm = document.querySelector('[data-sesion-create-form]');
            var editModal = document.querySelector('[data-sesion-edit-modal]');
            var editForm = document.querySelector('[data-sesion-edit-form]');
            var confirmModal = document.querySelector('[data-sesion-confirm-modal]');
            var pendingDeleteForm = null;

            function setFecha(form, fecha) {
                var el = form && form.querySelector('[name="fecha"]');
                if (el && fecha) el.value = fecha;
            }

            function openCreate(fecha) {
                if (!createModal || !createForm) return;
                createForm.reset();
                if (fecha) setFecha(createForm, fecha);
                createModal.hidden = false;
                createModal.classList.add('is-open');
                document.body.style.overflow = 'hidden';
            }
            function closeCreate() {
                if (!createModal) return;
                createModal.hidden = true;
                createModal.classList.remove('is-open');
                document.body.style.overflow = '';
            }

            document.querySelectorAll('[data-open-sesion-form]').forEach(function (b) {
                b.addEventListener('click', function () { openCreate(''); });
            });
            document.querySelectorAll('[data-open-sesion-day]').forEach(function (cell) {
                cell.addEventListener('click', function () {
                    openCreate(cell.getAttribute('data-fecha') || '');
                });
            });
            document.querySelectorAll('[data-close-sesion-form]').forEach(function (b) {
                b.addEventListener('click', closeCreate);
            });
            createModal?.addEventListener('click', function (e) {
                if (e.target === createModal) closeCreate();
            });

            function fillEditForm(payload) {
                if (!editModal || !editForm || !payload) return;
                var id = String(payload.id || '');
                editForm.action = '{{ url('admin/calendario/sesiones') }}/' + id;
                var idHidden = editForm.querySelector('[data-sesion-edit-id]');
                if (idHidden) idHidden.value = id;
                ['titulo','id_coach','tipo','fecha','hora_inicio','hora_fin','modalidad','cupo_maximo','estado'].forEach(function (f) {
                    var el = editForm.querySelector('[data-field="'+f+'"]');
                    if (el) el.value = payload[f] != null ? String(payload[f]) : '';
                });
                editModal.hidden = false;
                editModal.classList.add('is-open');
                document.body.style.overflow = 'hidden';
            }
            function openEdit(btn) {
                if (!btn) return;
                fillEditForm({
                    id: btn.getAttribute('data-id'),
                    titulo: btn.getAttribute('data-titulo'),
                    id_coach: btn.getAttribute('data-id_coach'),
                    tipo: btn.getAttribute('data-tipo'),
                    fecha: btn.getAttribute('data-fecha'),
                    hora_inicio: btn.getAttribute('data-hora_inicio'),
                    hora_fin: btn.getAttribute('data-hora_fin'),
                    modalidad: btn.getAttribute('data-modalidad'),
                    cupo_maximo: btn.getAttribute('data-cupo_maximo'),
                    estado: btn.getAttribute('data-estado')
                });
            }
            function closeEdit() {
                if (!editModal) return;
                editModal.hidden = true;
                editModal.classList.remove('is-open');
                document.body.style.overflow = '';
            }
            document.querySelectorAll('[data-open-sesion-edit]').forEach(function (b) {
                b.addEventListener('click', function () { openEdit(b); });
            });
            window.__stayfitOpenSesionEdit = fillEditForm;
            window.__stayfitOpenSesionCreate = openCreate;
            document.querySelectorAll('[data-close-sesion-edit]').forEach(function (b) {
                b.addEventListener('click', closeEdit);
            });
            editModal?.addEventListener('click', function (e) {
                if (e.target === editModal) closeEdit();
            });

            function openConfirm(form) {
                pendingDeleteForm = form;
                if (!confirmModal) return;
                confirmModal.hidden = false;
                confirmModal.classList.add('is-open');
                document.body.style.overflow = 'hidden';
            }
            function closeConfirm() {
                pendingDeleteForm = null;
                if (!confirmModal) return;
                confirmModal.hidden = true;
                confirmModal.classList.remove('is-open');
                document.body.style.overflow = '';
            }
            document.querySelectorAll('[data-sesion-delete]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var form = btn.closest('[data-sesion-delete-form]');
                    if (form) openConfirm(form);
                });
            });
            document.querySelectorAll('[data-sesion-confirm-cancel]').forEach(function (b) {
                b.addEventListener('click', closeConfirm);
            });
            confirmModal?.querySelector('[data-sesion-confirm-ok]')?.addEventListener('click', function () {
                if (pendingDeleteForm) pendingDeleteForm.submit();
            });
            confirmModal?.addEventListener('click', function (e) {
                if (e.target === confirmModal) closeConfirm();
            });

            function wireFeedbackModal(sel, closeSel) {
                var box = document.querySelector(sel);
                if (!box) return;
                document.body.style.overflow = 'hidden';
                function closeBox() {
                    box.hidden = true;
                    box.classList.remove('is-open');
                    document.body.style.overflow = '';
                }
                box.querySelectorAll(closeSel).forEach(function (b) {
                    b.addEventListener('click', closeBox);
                });
                box.addEventListener('click', function (e) {
                    if (e.target === box) closeBox();
                });
                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') closeBox();
                });
            }
            wireFeedbackModal('[data-ok-modal]', '[data-close-ok]');
            wireFeedbackModal('[data-warn-modal]', '[data-close-warn]');

            var remBox = document.querySelector('[data-cal-reminders]');
            var remToggle = document.querySelector('[data-cal-rem-toggle]');
            var remPanel = document.querySelector('[data-cal-rem-panel]');
            remToggle?.addEventListener('click', function () {
                if (!remBox || !remPanel) return;
                var open = remBox.classList.toggle('is-open');
                remToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                remPanel.hidden = !open;
            });

            (function wireCalViews() {
                var switches = document.querySelectorAll('[data-cal-switch]');
                var views = document.querySelectorAll('[data-cal-view]');
                var hint = document.querySelector('[data-cal-hint]');
                var dayTitle = document.querySelector('[data-cal-day-title]');
                var dayList = document.querySelector('[data-cal-day-list]');
                var dayDataEl = document.getElementById('ad-cal-day-data');
                var dayModal = document.querySelector('[data-cal-day-info-modal]');
                var dayModalTitle = document.querySelector('[data-cal-day-modal-title]');
                var dayModalBody = document.querySelector('[data-cal-day-modal-body]');
                var dayModalAdd = document.querySelector('[data-cal-day-modal-add]');
                var dayData = {};
                var activeDayFecha = null;
                try { dayData = dayDataEl ? JSON.parse(dayDataEl.textContent || '{}') : {}; } catch (e) { dayData = {}; }
                var hints = {
                    month: 'Vista mes: sesiones por fecha y ejercicio. Clic en una celda para ver el detalle.',
                    week: 'Detalle semanal por franja horaria.',
                    day: 'Detalle del día seleccionado.'
                };

                function showView(name) {
                    views.forEach(function (v) {
                        var on = v.getAttribute('data-cal-view') === name;
                        v.hidden = !on;
                    });
                    switches.forEach(function (btn) {
                        var on = btn.getAttribute('data-cal-switch') === name;
                        btn.classList.toggle('is-on', on);
                        btn.setAttribute('aria-selected', on ? 'true' : 'false');
                    });
                    if (hint) hint.textContent = hints[name] || '';
                }

                function escAttr(v) {
                    return String(v == null ? '' : v)
                        .replace(/&/g, '&amp;')
                        .replace(/"/g, '&quot;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;');
                }
                function escText(v) {
                    return String(v == null ? '' : v)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;');
                }

                function buildDayHtml(pack) {
                    var html = '';
                    (pack.sesiones || []).forEach(function (s) {
                        var cancel = s.estado === 'CANCELADA' || String(s.titulo || '').toUpperCase().indexOf('NO HAY CLASE') !== -1;
                        var special = String(s.titulo || '').toUpperCase().indexOf('CIERRE') !== -1;
                        html += '<article class="ad-cal-day-item ' + (cancel ? 'is-cancel' : (special ? 'is-special' : '')) + '">' +
                            '<span class="ad-cal-day-time">' + escText(s.hora || '—') + '</span>' +
                            '<div class="ad-cal-day-item-main"><strong>' + escText(String(s.titulo || '').toUpperCase()) + '</strong>' +
                            '<em>' + escText(s.tipo || 'Sesión') + ' · ' + escText(s.modalidad || '') + ' · ' + escText(s.estado || '') + '</em></div>' +
                            '<button type="button" class="ad-cal-day-edit" title="Editar programación" aria-label="Editar sesión"' +
                            ' data-cal-day-edit' +
                            ' data-id="' + escAttr(s.id) + '"' +
                            ' data-titulo="' + escAttr(s.titulo) + '"' +
                            ' data-id_coach="' + escAttr(s.id_coach) + '"' +
                            ' data-tipo="' + escAttr(s.tipo) + '"' +
                            ' data-fecha="' + escAttr(s.fecha) + '"' +
                            ' data-hora_inicio="' + escAttr(s.hora_inicio) + '"' +
                            ' data-hora_fin="' + escAttr(s.hora_fin) + '"' +
                            ' data-modalidad="' + escAttr(s.modalidad) + '"' +
                            ' data-cupo_maximo="' + escAttr(s.cupo_maximo) + '"' +
                            ' data-estado="' + escAttr(s.estado) + '">' +
                            '<span class="ad-cal-day-edit-ico" aria-hidden="true">✎</span> Editar</button></article>';
                    });
                    (pack.eventos || []).forEach(function (e) {
                        html += '<article class="ad-cal-day-item is-special">' +
                            '<span class="ad-cal-day-time">' + escText(e.hora || '—') + '</span>' +
                            '<div class="ad-cal-day-item-main"><strong>' + escText(String(e.nombre || '').toUpperCase()) + '</strong>' +
                            '<em>Evento · ' + escText(e.modalidad || '') + '</em></div></article>';
                    });
                    if (!html) {
                        html = '<p class="ad-empty">Nada programado este día. Usa + Sesión para fijar una.</p>';
                    }
                    return html;
                }

                function renderDay(fecha) {
                    var pack = dayData[fecha];
                    if (!pack || !dayList) return;
                    if (dayTitle) dayTitle.textContent = pack.label || fecha;
                    dayList.innerHTML = buildDayHtml(pack);
                }

                function openDayModal(fecha) {
                    var pack = dayData[fecha] || { label: fecha, sesiones: [], eventos: [] };
                    activeDayFecha = fecha;
                    if (dayModalTitle) dayModalTitle.textContent = pack.label || fecha;
                    if (dayModalBody) dayModalBody.innerHTML = buildDayHtml(pack);
                    if (!dayModal) return;
                    dayModal.hidden = false;
                    dayModal.classList.add('is-open');
                    document.body.style.overflow = 'hidden';
                }

                function closeDayModal() {
                    activeDayFecha = null;
                    if (!dayModal) return;
                    dayModal.hidden = true;
                    dayModal.classList.remove('is-open');
                    document.body.style.overflow = '';
                    showView('month');
                }

                switches.forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        showView(btn.getAttribute('data-cal-switch'));
                    });
                });

                document.querySelectorAll('[data-cal-day-modal]').forEach(function (el) {
                    el.addEventListener('click', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        var fecha = el.getAttribute('data-cal-day-modal');
                        if (fecha) openDayModal(fecha);
                    });
                });

                document.querySelectorAll('[data-cal-day-modal-close]').forEach(function (b) {
                    b.addEventListener('click', closeDayModal);
                });
                dayModal?.addEventListener('click', function (e) {
                    if (e.target === dayModal) closeDayModal();
                });
                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape' && dayModal && !dayModal.hidden) closeDayModal();
                });
                dayModalAdd?.addEventListener('click', function () {
                    var fecha = activeDayFecha;
                    closeDayModal();
                    if (typeof window.__stayfitOpenSesionCreate === 'function') {
                        window.__stayfitOpenSesionCreate(fecha || '');
                    }
                });

                dayModalBody?.addEventListener('click', function (e) {
                    var btn = e.target.closest('[data-cal-day-edit]');
                    if (!btn || !dayModalBody.contains(btn)) return;
                    e.preventDefault();
                    e.stopPropagation();
                    closeDayModal();
                    openEdit(btn);
                });

                showView('month');
            })();
        })();
    </script>
@endsection
