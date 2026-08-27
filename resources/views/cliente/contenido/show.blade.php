@extends('layouts.cliente')

@section('content')
    <x-cliente.page-head
        kicker="Video"
        :title="$video->titulo"
        :subtitle="$video->categoriaVideo?->nombre ?? 'Clase virtual'"
    >
        <x-slot:actions>
            <a class="cv-btn cv-btn-ghost cv-btn-sm" href="{{ route('cliente.contenido.index') }}">Volver</a>
        </x-slot:actions>
    </x-cliente.page-head>

    <x-cliente.card title="Reproducción" subtitle="Disfruta la clase y marca tu avance">
        @php
            $url = trim((string) $video->url_video);
            $embed = $url;
            if (str_contains($url, 'watch?v=')) {
                $embed = preg_replace('/.*watch\?v=([^&]+).*/', 'https://www.youtube.com/embed/$1', $url);
            } elseif (str_contains($url, 'youtu.be/')) {
                $embed = preg_replace('/.*youtu\.be\/([^?]+).*/', 'https://www.youtube.com/embed/$1', $url);
            }
            $pct = (int) $progreso->porcentaje;
        @endphp

        @if ($embed)
            <iframe class="ad-video-frame" src="{{ $embed }}" allowfullscreen loading="lazy" title="{{ $video->titulo }}"></iframe>
        @else
            <div class="cv-media__cover" style="aspect-ratio:16/9;border-radius:14px">
                <p class="cv-empty" style="position:absolute;inset:0;display:grid;place-items:center">Video sin URL configurada.</p>
            </div>
        @endif

        @if ($video->descripcion)
            <p style="margin:14px 0 0;color:var(--cv-muted);font-size:13px;line-height:1.55">{{ $video->descripcion }}</p>
        @endif

        <div style="margin-top:16px;display:flex;flex-wrap:wrap;gap:10px;align-items:center">
            <span class="cv-badge {{ $progreso->estado === 'COMPLETADO' ? 'is-ok' : 'is-cyan' }}">
                {{ $progreso->estado === 'COMPLETADO' ? 'Completado' : ($progreso->estado === 'EN_PROGRESO' ? 'En curso' : $progreso->estado) }}
                · {{ $pct }}%
            </span>
            <div class="cv-progress" style="flex:1;min-width:120px;max-width:240px">
                <i style="width: {{ $progreso->estado === 'COMPLETADO' ? 100 : $pct }}%"></i>
            </div>
            @if ($progreso->estado !== 'COMPLETADO')
                <form method="POST" action="{{ route('cliente.contenido.completar', $video->id_video) }}">
                    @csrf
                    <button class="cv-btn cv-btn-sm" type="submit">Marcar completado</button>
                </form>
            @endif
        </div>
    </x-cliente.card>
@endsection
