@php $df = ! empty($withDataFields); @endphp

<label>
    <span>Título *</span>
    <input type="text" name="titulo" {{ $df ? 'data-field=titulo' : '' }} required maxlength="150">
</label>

<div class="ad-form-row">
    <label>
        <span>Coach *</span>
        <select name="id_coach" {{ $df ? 'data-field=id_coach' : '' }} required>
            <option value="">Selecciona…</option>
            @foreach ($coaches as $coach)
                @php
                    $label = $coach->usuario?->name
                        ?? $coach->usuario?->nombres
                        ?? ('Coach #'.$coach->id_coach);
                @endphp
                <option value="{{ $coach->id_coach }}">{{ $label }}</option>
            @endforeach
        </select>
    </label>
    <label>
        <span>Tipo *</span>
        <select name="tipo" {{ $df ? 'data-field=tipo' : '' }} required>
            @foreach ($tipos as $t)
                <option value="{{ $t }}">{{ $t }}</option>
            @endforeach
        </select>
    </label>
</div>

<div class="ad-form-row three">
    <label>
        <span>Fecha *</span>
        <input type="date" name="fecha" {{ $df ? 'data-field=fecha' : '' }} required>
    </label>
    <label>
        <span>Hora inicio *</span>
        <input type="time" name="hora_inicio" {{ $df ? 'data-field=hora_inicio' : '' }} required>
    </label>
    <label>
        <span>Hora fin *</span>
        <input type="time" name="hora_fin" {{ $df ? 'data-field=hora_fin' : '' }} required>
    </label>
</div>

<div class="ad-form-row three">
    <label>
        <span>Modalidad *</span>
        <select name="modalidad" {{ $df ? 'data-field=modalidad' : '' }} required>
            @foreach ($modalidades as $m)
                <option value="{{ $m }}">{{ $m }}</option>
            @endforeach
        </select>
    </label>
    <label>
        <span>Cupo máximo *</span>
        <input type="number" name="cupo_maximo" min="1" value="1" {{ $df ? 'data-field=cupo_maximo' : '' }} required>
    </label>
    <label>
        <span>Estado *</span>
        <select name="estado" {{ $df ? 'data-field=estado' : '' }} required>
            @foreach ($estados as $e)
                <option value="{{ $e }}">{{ $e }}</option>
            @endforeach
        </select>
    </label>
</div>
