@extends('layouts.admin')

@section('title', $novedad->exists ? 'Editar novedad' : 'Nueva novedad')
@section('subtitle', 'Foto, vigencia y destino en el welcome')

@section('content')
    <section class="ad-card ad-glass-card">
        <form class="ad-form" method="POST"
              action="{{ $novedad->exists ? route('admin.novedades.update', $novedad) : route('admin.novedades.store') }}"
              enctype="multipart/form-data">
            @csrf
            @if ($novedad->exists) @method('PUT') @endif

            <div class="ad-form-grid">
                <label>
                    Título
                    <input type="text" name="titulo" value="{{ old('titulo', $novedad->titulo) }}" required maxlength="150">
                </label>
                <label>
                    Tipo
                    <select name="tipo" required>
                        @foreach (['NUEVO_PLAN' => 'Nuevo plan', 'DESCUENTO' => 'Descuento', 'ACTUALIZACION' => 'Actualización', 'GENERAL' => 'General'] as $val => $label)
                            <option value="{{ $val }}" @selected(old('tipo', $novedad->tipo) === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="ad-span-2">
                    Subtítulo
                    <input type="text" name="subtitulo" value="{{ old('subtitulo', $novedad->subtitulo) }}" maxlength="255">
                </label>
                <label class="ad-span-2">
                    Descripción
                    <textarea name="descripcion" rows="3">{{ old('descripcion', $novedad->descripcion) }}</textarea>
                </label>
                <label>
                    Foto (archivo)
                    <input type="file" name="imagen" accept="image/*" @required(! $novedad->exists)>
                </label>
                <label>
                    o URL de imagen
                    <input type="url" name="imagen_url" value="{{ old('imagen_url', str_starts_with((string) $novedad->imagen_url, 'http') ? $novedad->imagen_url : '') }}" placeholder="https://…">
                </label>
                @if ($novedad->imagen_url)
                    <div class="ad-span-2">
                        <img src="{{ $novedad->imagenSrc() }}" alt="Preview" style="max-width:280px;border-radius:12px">
                    </div>
                @endif
                <label>
                    Texto del botón
                    <input type="text" name="boton_texto" value="{{ old('boton_texto', $novedad->boton_texto) }}" required>
                </label>
                <label>
                    Destino (URL o #planes)
                    <input type="text" name="url_destino" value="{{ old('url_destino', $novedad->url_destino) }}" placeholder="#planes">
                </label>
                <label>
                    Plan vinculado (opcional)
                    <select name="id_plan">
                        <option value="">Ninguno</option>
                        @foreach ($planes as $p)
                            <option value="{{ $p->id_plan }}" @selected(old('id_plan', $novedad->id_plan) == $p->id_plan)>{{ $p->nombre }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    Orden
                    <input type="number" name="orden" value="{{ old('orden', $novedad->orden ?? 0) }}" min="0">
                </label>
                <label>
                    Inicia
                    <input type="datetime-local" name="inicia_en" value="{{ old('inicia_en', optional($novedad->inicia_en)->format('Y-m-d\TH:i')) }}">
                </label>
                <label>
                    Termina
                    <input type="datetime-local" name="termina_en" value="{{ old('termina_en', optional($novedad->termina_en)->format('Y-m-d\TH:i')) }}">
                </label>
                <label class="ad-check">
                    <input type="checkbox" name="activo" value="1" @checked(old('activo', $novedad->activo ?? true))>
                    Activa en welcome
                </label>
            </div>

            <div class="ad-modal-actions">
                <button class="ad-btn" type="submit">Guardar</button>
                <a class="ad-btn ad-btn-ghost" href="{{ route('admin.novedades.index') }}">Cancelar</a>
            </div>
        </form>
    </section>
@endsection
