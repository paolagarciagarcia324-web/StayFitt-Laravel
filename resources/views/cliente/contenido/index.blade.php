@extends('layouts.cliente')

@section('content')
    <x-cliente.page-head
        kicker="Streaming"
        title="Contenido virtual"
        subtitle="Videos y clases de tu programa FigueFit."
    />

    <div class="cv-metrics">
        <x-cliente.metric label="Videos disponibles" :value="$videos->count()" />
        <x-cliente.metric label="Completados" :value="$completados" hint="De tu biblioteca asignada" />
    </div>

    @if ($videos->isNotEmpty())
        <div class="cv-media-grid">
            @foreach ($videos as $video)
                @php
                    $prog = $progresos->get($video->id_video);
                    $pct = (int) ($prog?->porcentaje ?? 0);
                    $estado = $prog?->estado ?? 'PENDIENTE';
                    $dur = $video->duracion_segundos;
                    $durLabel = $dur ? sprintf('%d:%02d', intdiv((int) $dur, 60), (int) $dur % 60) : null;
                @endphp
                <a href="{{ route('cliente.contenido.show', $video->id_video) }}" class="cv-media" style="text-decoration:none;color:inherit">
                    <div class="cv-media__cover">
                        <div class="cv-media__cover-meta">
                            @if ($durLabel)
                                <span class="cv-badge">{{ $durLabel }}</span>
                            @endif
                            <span class="cv-badge {{ $estado === 'COMPLETADO' ? 'is-ok' : ($estado === 'EN_PROGRESO' ? 'is-cyan' : '') }}">
                                {{ $estado === 'COMPLETADO' ? 'Completado' : ($estado === 'EN_PROGRESO' ? 'En curso' : 'Pendiente') }}
                            </span>
                        </div>
                    </div>
                    <div class="cv-media__body">
                        <h3>{{ $video->titulo }}</h3>
                        <p>
                            {{ $video->categoriaVideo?->nombre ?? 'General' }}
                            @if ($video->dia_orden)
                                · Día {{ $video->dia_orden }}
                            @endif
                        </p>
                        @if ($video->descripcion)
                            <p>{{ \Illuminate\Support\Str::limit($video->descripcion, 90) }}</p>
                        @endif
                        <div class="cv-progress" aria-label="Progreso {{ $pct }}%">
                            <i style="width: {{ max($estado === 'COMPLETADO' ? 100 : $pct, $estado === 'COMPLETADO' ? 100 : 0) }}%"></i>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <x-cliente.card title="Biblioteca vacía" subtitle="Tu coach aún no publicó videos">
            <p class="cv-empty">No hay videos disponibles todavía.</p>
        </x-cliente.card>
    @endif
@endsection
