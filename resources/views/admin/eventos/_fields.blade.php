@php $df = ! empty($withDataFields); @endphp

<section class="ad-evento-section">
    <h3 class="ad-evento-section-title">Portada</h3>
    <div class="ad-evento-cover" data-evento-cover>
        <div class="ad-evento-cover-preview" data-evento-preview tabindex="0" role="button" aria-label="Vista previa de portada">
            <img data-field-preview-img {{ $df ? 'data-field=imagen_src' : '' }} hidden alt="Preview">
            <div class="ad-evento-cover-empty" data-field-preview-empty>
                <span class="ad-evento-cover-icon" aria-hidden="true">@include('partials.admin-icon', ['icon' => 'eventos'])</span>
                <strong>Portada del evento</strong>
                <em>Arrastra una imagen o usa las opciones</em>
            </div>
        </div>
        <div class="ad-evento-cover-actions">
            <label class="ad-evento-upload-btn">
                <span class="ad-evento-upload-ico" aria-hidden="true">@include('partials.admin-icon', ['icon' => 'upload'])</span>
                <span class="ad-evento-upload-text">
                    <strong>Subir foto *</strong>
                    <em>JPG, PNG o WEBP</em>
                </span>
                <input type="file" name="imagen" accept="image/*" data-field-preview-file>
            </label>
            <label class="ad-evento-url-field">
                <span>O pega una URL *</span>
                <input type="url" name="imagen_url" {{ $df ? 'data-field=imagen_url' : '' }} placeholder="https://…" data-field-preview-url data-cover-url required>
            </label>
        </div>
    </div>
</section>

<section class="ad-evento-section">
    <h3 class="ad-evento-section-title">Información</h3>
    <label><span>Nombre *</span><input type="text" name="nombre" {{ $df ? 'data-field=nombre' : '' }} required></label>
    <label><span>Descripción *</span><textarea name="descripcion" rows="3" {{ $df ? 'data-field=descripcion' : '' }} required></textarea></label>
</section>

<section class="ad-evento-section">
    <h3 class="ad-evento-section-title">Clasificación</h3>
    <div class="ad-form-row three">
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
        <label><span>Estado *</span>
            <select name="estado" {{ $df ? 'data-field=estado' : '' }} required>
                @foreach (['BORRADOR','PUBLICADO','CERRADO','CANCELADO'] as $e)
                    <option value="{{ $e }}">{{ $e }}</option>
                @endforeach
            </select>
        </label>
    </div>
</section>

<section class="ad-evento-section">
    <h3 class="ad-evento-section-title">Agenda y cupo</h3>
    <div class="ad-form-row three">
        <label><span>Inicio *</span><input type="datetime-local" name="fecha_inicio" {{ $df ? 'data-field=inicio' : '' }} required></label>
        <label><span>Fecha de vencimiento *</span><input type="datetime-local" name="fecha_fin" {{ $df ? 'data-field=fin' : '' }} required></label>
        <label><span>Cupo máximo *</span><input type="number" name="cupo_maximo" min="1" {{ $df ? 'data-field=cupo' : '' }} required></label>
    </div>
</section>
