@php $df = ! empty($withDataFields); @endphp
<div class="ad-media-upload-block">
    <div class="ad-ml-preview" data-evento-preview>
        <img data-field-preview-img {{ $df ? 'data-field=imagen_src' : '' }} hidden alt="Preview">
        <span class="ad-ml-placeholder" data-field-preview-empty>@include('partials.admin-icon', ['icon' => 'eventos'])</span>
    </div>
    <div>
        <label class="ad-ml-upload"><span>Subir foto</span><input type="file" name="imagen" accept="image/*" data-field-preview-file></label>
        <label><span>O URL</span><input type="url" name="imagen_url" {{ $df ? 'data-field=imagen_url' : '' }} placeholder="https://..." data-field-preview-url></label>
    </div>
</div>
<label><span>Nombre *</span><input type="text" name="nombre" {{ $df ? 'data-field=nombre' : '' }} required></label>
<label><span>Descripción</span><textarea name="descripcion" rows="2" {{ $df ? 'data-field=descripcion' : '' }}></textarea></label>
<div class="ad-form-row">
    <label><span>Tipo *</span>
        <select name="tipo" {{ $df ? 'data-field=tipo' : '' }} required>
            @foreach (['TALLER','RETO','CLASE_ESPECIAL','OTRO'] as $t)
                <option value="{{ $t }}">{{ $t }}</option>
            @endforeach
        </select>
    </label>
    <label><span>Modalidad *</span>
        <select name="modalidad" {{ $df ? 'data-field=modalidad' : '' }} required>
            @foreach (['VIRTUAL','PRESENCIAL','MIXTO'] as $m)
                <option value="{{ $m }}">{{ $m }}</option>
            @endforeach
        </select>
    </label>
</div>
<div class="ad-form-row">
    <label><span>Inicio *</span><input type="datetime-local" name="fecha_inicio" {{ $df ? 'data-field=inicio' : '' }} required></label>
    <label><span>Fin</span><input type="datetime-local" name="fecha_fin" {{ $df ? 'data-field=fin' : '' }}></label>
</div>
<div class="ad-form-row">
    <label><span>Cupo máximo</span><input type="number" name="cupo_maximo" min="1" {{ $df ? 'data-field=cupo' : '' }}></label>
    <label><span>Estado *</span>
        <select name="estado" {{ $df ? 'data-field=estado' : '' }} required>
            @foreach (['BORRADOR','PUBLICADO','CERRADO','CANCELADO'] as $e)
                <option value="{{ $e }}">{{ $e }}</option>
            @endforeach
        </select>
    </label>
</div>
