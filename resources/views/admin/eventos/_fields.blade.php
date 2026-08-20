@php $df = ! empty($withDataFields); @endphp
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
