@extends('layouts.cliente')

@section('content')
    <x-cliente.page-head
        kicker="Entrenamiento"
        title="Tus rutinas"
        subtitle="Planes asignados por tu coach y seguimiento semanal."
    />

    <div class="cv-metrics">
        <x-cliente.metric label="Planes asignados" :value="$planes->count()" />
        <x-cliente.metric label="Completadas (7 días)" :value="$completadasSemana" hint="Rutinas marcadas esta semana" />
    </div>

    @forelse ($planes as $plan)
        <x-cliente.card
            :title="$plan->nombre"
            :subtitle="($plan->objetivo ?: 'Plan de entrenamiento').' · Coach: '.($plan->coach?->usuario?->nombres ?? '—')"
        >
            @if ($plan->rutinas->isEmpty())
                <p class="cv-empty">Este plan aún no tiene rutinas publicadas.</p>
            @else
                <div class="cv-media-grid">
                    @foreach ($plan->rutinas as $rutina)
                        <a href="{{ route('cliente.entrenamiento.rutina', $rutina->id_rutina) }}" class="cv-media" style="text-decoration:none;color:inherit">
                            <div class="cv-media__cover">
                                <div class="cv-media__cover-meta">
                                    @if ($rutina->dia_semana)
                                        <span class="cv-badge is-cyan">Día {{ $rutina->dia_semana }}</span>
                                    @endif
                                    @if ($rutina->duracion_min)
                                        <span class="cv-badge">{{ $rutina->duracion_min }} min</span>
                                    @endif
                                </div>
                            </div>
                            <div class="cv-media__body">
                                <h3>{{ $rutina->nombre }}</h3>
                                <p>{{ $rutina->ejercicios->count() }} ejercicios · {{ $rutina->duracion_min ? $rutina->duracion_min.' min' : 'Duración libre' }}</p>
                                <span class="cv-btn cv-btn-sm cv-btn-cyan" style="width:fit-content;margin-top:4px">Abrir rutina</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </x-cliente.card>
    @empty
        <x-cliente.card title="Sin plan de entrenamiento" subtitle="Tu coach aún no te ha asignado rutinas">
            <p class="cv-empty">Mientras tanto explora videos o revisa la agenda de eventos.</p>
            <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:12px">
                <a class="cv-btn cv-btn-ghost cv-btn-sm" href="{{ route('cliente.contenido.index') }}">Ver videos</a>
                <a class="cv-btn cv-btn-ghost cv-btn-sm" href="{{ route('cliente.agenda.index') }}">Agenda</a>
            </div>
        </x-cliente.card>
    @endforelse

    <x-cliente.card title="Actividad reciente" subtitle="Últimas sesiones registradas">
        <div class="cv-list-feed">
            @forelse ($registrosRecientes as $reg)
                <div class="cv-feed-item">
                    <div class="cv-tile__ico is-pink" aria-hidden="true">💪</div>
                    <div>
                        <strong>{{ $reg->rutina?->nombre ?? 'Rutina' }}</strong>
                        <p style="margin:4px 0 0;color:var(--cv-muted);font-size:12px">
                            {{ $reg->completada ? 'Completada' : 'Registrada' }}
                            @if ($reg->dificultad)
                                · Dificultad {{ $reg->dificultad }}/10
                            @endif
                        </p>
                    </div>
                    <span class="cv-badge">{{ optional($reg->fecha_ejecucion)->format('d/m/Y') }}</span>
                </div>
            @empty
                <p class="cv-empty">Aún no registras rutinas. ¡Empieza hoy!</p>
            @endforelse
        </div>
    </x-cliente.card>
@endsection
