@extends('layouts.cliente')

@section('content')
    <x-cliente.page-head
        kicker="Agenda"
        title="Eventos y actividades"
        subtitle="Inscríbete a clases, workshops y encuentros FigueFit."
    />

    <x-cliente.card title="Próximos eventos" subtitle="Cupos limitados · confirma tu lugar">
        @forelse ($eventos as $evento)
            @php $inscrito = $misInscripciones->has($evento->id_evento); @endphp
            <article class="cv-media" style="margin-bottom:14px">
                <div class="cv-media__cover" style="aspect-ratio:16/6;min-height:72px">
                    <div class="cv-media__cover-meta">
                        @if ($evento->modalidad)
                            <span class="cv-badge is-cyan">{{ $evento->modalidad }}</span>
                        @endif
                        @if ($inscrito)
                            <span class="cv-badge is-ok">Inscrito</span>
                        @elseif ($evento->cupo_maximo && (int) $evento->cupo_inscritos >= (int) $evento->cupo_maximo)
                            <span class="cv-badge">Sin cupos</span>
                        @endif
                    </div>
                </div>
                <div class="cv-media__body">
                    <h3>{{ $evento->nombre }}</h3>
                    <p>
                        {{ optional($evento->fecha_inicio)->format('d/m/Y H:i') ?? 'Por confirmar' }}
                        @if ($evento->cupo_maximo)
                            · Cupos {{ (int) $evento->cupo_inscritos }}/{{ (int) $evento->cupo_maximo }}
                        @endif
                    </p>
                    @if ($evento->descripcion)
                        <p>{{ \Illuminate\Support\Str::limit($evento->descripcion, 140) }}</p>
                    @endif
                    <div style="margin-top:8px">
                        @if ($inscrito)
                            <form method="POST" action="{{ route('cliente.agenda.cancelar', $evento->id_evento) }}">
                                @csrf
                                <button class="cv-btn cv-btn-ghost cv-btn-sm" type="submit">Cancelar inscripción</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('cliente.agenda.inscribir', $evento->id_evento) }}">
                                @csrf
                                <button class="cv-btn cv-btn-sm" type="submit" @disabled($evento->cupo_maximo && (int) $evento->cupo_inscritos >= (int) $evento->cupo_maximo)>
                                    Inscribirme
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <p class="cv-empty">No hay eventos próximos por ahora.</p>
        @endforelse
    </x-cliente.card>

    @if ($misInscripciones->isNotEmpty())
        <x-cliente.card title="Mis inscripciones" subtitle="Eventos confirmados">
            <div class="cv-list-feed">
                @foreach ($misInscripciones as $part)
                    <div class="cv-feed-item">
                        <div class="cv-tile__ico is-pink" aria-hidden="true">📅</div>
                        <div>
                            <strong>{{ $part->evento?->nombre ?? 'Evento' }}</strong>
                            <p style="margin:4px 0 0;font-size:12px;color:var(--cv-faint)">
                                {{ optional($part->evento?->fecha_inicio)->format('d/m/Y H:i') ?? 'Fecha por confirmar' }}
                            </p>
                        </div>
                        <span class="cv-badge is-ok">{{ $part->estado }}</span>
                    </div>
                @endforeach
            </div>
        </x-cliente.card>
    @endif
@endsection
