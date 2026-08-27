@extends('layouts.coach')

@section('title', 'Biblioteca de videos')

@section('content')
    <div class="ch-page">
        <div class="ch-kicker">Contenido on-demand</div>
        <h1 class="ch-title">Biblioteca de videos</h1>
        <p class="ch-sub">{{ $totalVideos }} clases activas organizadas por categoría.</p>

        <x-coach.card title="Agregar video" subtitle="Asociar a una categoría del catálogo">
            <form method="POST" action="{{ route('coach.videos.store') }}">
                @csrf
                <div class="ch-form-row">
                    <div class="ch-field">
                        <label>Categoría</label>
                        <select name="id_categoria_video" required>
                            <option value="">Seleccionar…</option>
                            @foreach ($categorias as $cat)
                                <option value="{{ $cat->id_categoria_video }}">{{ $cat->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ch-field">
                        <label>Título</label>
                        <input type="text" name="titulo" required placeholder="Calentamiento dinámico 10 min">
                    </div>
                    <div class="ch-field">
                        <label>Duración (seg)</label>
                        <input type="number" name="duracion_segundos" min="30" placeholder="600">
                    </div>
                </div>
                <div class="ch-field" style="margin-bottom:10px">
                    <label>URL del video</label>
                    <input type="url" name="url_video" required placeholder="https://youtube.com/...">
                </div>
                <div class="ch-field" style="margin-bottom:12px">
                    <label>Descripción</label>
                    <textarea name="descripcion" rows="2" placeholder="Qué aprenderá el cliente..."></textarea>
                </div>
                <button type="submit" class="ch-btn">Publicar video</button>
            </form>
        </x-coach.card>

        @foreach ($categorias as $cat)
            @if ($cat->videos->isNotEmpty())
                <x-coach.card title="{{ $cat->nombre }}" subtitle="{{ $cat->videos->count() }} videos">
                    <div class="ch-videos">
                        @foreach ($cat->videos as $video)
                            <article class="ch-video">
                                <div class="ch-video__thumb">▶</div>
                                <div class="ch-video__body">
                                    <strong>{{ $video->titulo }}</strong>
                                    <small>
                                        @if ($video->duracion_segundos)
                                            {{ gmdate('i:s', $video->duracion_segundos) }} min
                                        @endif
                                        · Día {{ $video->dia_orden }}
                                    </small>
                                    @if ($video->descripcion)
                                        <p style="font-size:11px;color:var(--ch-muted);margin:6px 0 0">{{ Str::limit($video->descripcion, 80) }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </x-coach.card>
            @endif
        @endforeach

        @if ($categorias->every(fn ($c) => $c->videos->isEmpty()))
            <x-coach.card title="Biblioteca vacía">
                <p class="ch-empty">Agrega tu primer video con el formulario superior o crea propuestas con clases por día.</p>
            </x-coach.card>
        @endif
    </div>
@endsection
