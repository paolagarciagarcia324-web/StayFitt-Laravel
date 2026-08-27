@extends('layouts.staff')

@section('title', 'Biblioteca de videos')

@section('content')
    <div class="st-page">
        <div class="st-kicker">Material de apoyo</div>
        <h1 class="st-title">Biblioteca de videos</h1>
        <p class="st-sub">{{ $totalVideos }} referencias activas · asociar a propuestas y ejercicios.</p>

        <x-staff.card title="Agregar referencia">
            <form method="POST" action="{{ route('staff.videos.store') }}">
                @csrf
                <div class="st-form-row">
                    <div class="st-field">
                        <label>Categoría</label>
                        <select name="id_categoria_video" required>
                            @foreach ($categorias as $cat)
                                <option value="{{ $cat->id_categoria_video }}">{{ $cat->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="st-field"><label>Título</label><input name="titulo" required></div>
                    <div class="st-field"><label>Duración (seg)</label><input type="number" name="duracion_segundos" min="30"></div>
                </div>
                <div class="st-field" style="margin-bottom:10px"><label>URL</label><input type="url" name="url_video" required></div>
                <div class="st-field" style="margin-bottom:12px"><label>Descripción</label><textarea name="descripcion" rows="2"></textarea></div>
                <button type="submit" class="st-btn">Agregar</button>
            </form>
        </x-staff.card>

        @foreach ($categorias as $cat)
            @if ($cat->videos->isNotEmpty())
                <x-staff.card title="{{ $cat->nombre }}">
                    <div class="st-videos">
                        @foreach ($cat->videos as $v)
                            <article class="st-video">
                                <div class="st-video__thumb">▶</div>
                                <div class="st-video__body">
                                    <strong>{{ $v->titulo }}</strong>
                                    <small>Día {{ $v->dia_orden }} @if($v->duracion_segundos)· {{ gmdate('i:s', $v->duracion_segundos) }} @endif</small>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </x-staff.card>
            @endif
        @endforeach

        @if ($propuestasConVideo->isNotEmpty())
            <x-staff.card title="Propuestas con videos pendientes">
                @foreach ($propuestasConVideo as $p)
                    <a class="st-link" href="{{ route('coach.propuestas.entrenamiento', $p) }}">{{ $p->titulo_interno }} →</a><br>
                @endforeach
            </x-staff.card>
        @endif
    </div>
@endsection
