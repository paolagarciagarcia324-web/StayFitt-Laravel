@extends('layouts.coach')

@section('title', $propuesta->titulo_interno)
@section('subtitle', 'Paso 1 · Videos: una clase por día')

@section('actions')
    @if ($propuesta->puedeAvanzarANutricion())
        <a class="ad-btn" href="{{ route('coach.propuestas.nutricion', $propuesta) }}">Continuar a nutrición</a>
    @endif
@endsection

@section('content')
    @include('coach.propuestas._wizard', ['propuesta' => $propuesta, 'step' => 1])

    <section class="ad-card" style="margin-bottom:16px">
        <form class="ad-form" method="POST" action="{{ route('coach.propuestas.meta', $propuesta) }}">
            @csrf
            @method('PUT')
            <div class="ad-form-grid">
                <label>
                    Título interno
                    <input type="text" name="titulo_interno" value="{{ old('titulo_interno', $propuesta->titulo_interno) }}" required>
                </label>
                <label>
                    Estado
                    <input type="text" value="{{ $propuesta->etiquetaEstado() }}" disabled>
                </label>
                <label class="ad-span-2">
                    Descripción
                    <textarea name="descripcion_interna" rows="2">{{ old('descripcion_interna', $propuesta->descripcion_interna) }}</textarea>
                </label>
            </div>
            @if ($propuesta->esEditable())
                <button class="ad-btn ad-btn-ghost" type="submit">Guardar datos</button>
            @endif
        </form>
    </section>

    @if ($propuesta->esEditable())
        <section class="ad-card" style="margin-bottom:16px">
            <div class="ad-card-head"><h2>Agregar clase (día)</h2></div>
            <form class="ad-form" method="POST" action="{{ route('coach.propuestas.videos.store', $propuesta) }}" enctype="multipart/form-data" data-video-form>
                @csrf
                <div class="ad-form-grid">
                    <label>
                        Día / orden
                        <input type="number" name="dia_orden" min="1" value="{{ old('dia_orden', ($videos->max('dia_orden') ?? 0) + 1) }}">
                    </label>
                    <label>
                        Título de la clase
                        <input type="text" name="titulo" value="{{ old('titulo') }}" required maxlength="150">
                    </label>
                    <label>
                        Fuente
                        <select name="tipo_fuente" data-tipo-fuente>
                            <option value="URL" @selected(old('tipo_fuente', 'URL') === 'URL')>URL (YouTube, Vimeo…)</option>
                            <option value="ARCHIVO" @selected(old('tipo_fuente') === 'ARCHIVO')>Archivo (mp4/webm)</option>
                        </select>
                    </label>
                    <label data-field-url>
                        URL del video
                        <input type="url" name="url_video" value="{{ old('url_video') }}" placeholder="https://…">
                    </label>
                    <label data-field-archivo hidden>
                        Archivo de video (máx. ~200 MB)
                        <input type="file" name="archivo_video" accept="video/mp4,video/webm,video/quicktime">
                    </label>
                    <label class="ad-span-2">
                        Descripción
                        <textarea name="descripcion" rows="2">{{ old('descripcion') }}</textarea>
                    </label>
                </div>
                <button class="ad-btn" type="submit">Agregar clase</button>
            </form>
        </section>
    @endif

    <section class="ad-card">
        <div class="ad-card-head">
            <h2>Clases ({{ $videos->where('estado', 'ACTIVO')->count() }})</h2>
        </div>
        <div class="ad-table-wrap">
            <table class="ad-table">
                <thead>
                    <tr>
                        <th>Día</th>
                        <th>Título</th>
                        <th>Fuente</th>
                        @if ($propuesta->esEditable())
                            <th></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($videos->where('estado', 'ACTIVO') as $v)
                        <tr>
                            <td>{{ $v->dia_orden }}</td>
                            <td>
                                <strong>{{ $v->titulo }}</strong>
                                @if ($v->descripcion)
                                    <div class="ad-muted">{{ \Illuminate\Support\Str::limit($v->descripcion, 90) }}</div>
                                @endif
                            </td>
                            <td>
                                @php
                                    $isFile = ! str_starts_with((string) $v->url_video, 'http');
                                @endphp
                                @if ($isFile)
                                    Archivo
                                @else
                                    <a href="{{ $v->url_video }}" target="_blank" rel="noopener">URL</a>
                                @endif
                            </td>
                            @if ($propuesta->esEditable())
                                <td>
                                    <form method="POST" action="{{ route('coach.propuestas.videos.destroy', [$propuesta, $v->id_video]) }}" onsubmit="return confirm('¿Eliminar esta clase?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="ad-btn ad-btn-ghost" type="submit">Quitar</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="4">Sin clases aún. Sube el video del día 1.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection

@push('scripts')
<script>
(function () {
    var form = document.querySelector('[data-video-form]');
    if (!form) return;
    var select = form.querySelector('[data-tipo-fuente]');
    var urlField = form.querySelector('[data-field-url]');
    var fileField = form.querySelector('[data-field-archivo]');
    function sync() {
        var isFile = select.value === 'ARCHIVO';
        urlField.hidden = isFile;
        fileField.hidden = !isFile;
    }
    select.addEventListener('change', sync);
    sync();
})();
</script>
@endpush
