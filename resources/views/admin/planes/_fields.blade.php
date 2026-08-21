<div class="ad-form-row">
    <label><span>Código</span><input type="text" name="codigo" value="{{ old('codigo', $plan->codigo) }}"></label>
    <label><span>Nombre *</span><input type="text" name="nombre" value="{{ old('nombre', $plan->nombre) }}" required></label>
</div>
<div class="ad-media-upload-block">
    <div class="ad-ml-preview" data-plan-preview>
        @if ($plan->imagenSrc())
            <img src="{{ $plan->imagenSrc() }}" alt="{{ $plan->nombre }}" data-plan-preview-img>
        @else
            <img data-plan-preview-img hidden alt="Preview">
            <span class="ad-ml-placeholder" data-plan-preview-empty>@include('partials.admin-icon', ['icon' => 'planes'])</span>
        @endif
    </div>
    <div>
        <label class="ad-ml-upload"><span>Subir foto del plan</span><input type="file" name="imagen" accept="image/*" data-plan-preview-file></label>
        <label><span>O URL</span><input type="url" name="imagen_url" value="{{ old('imagen_url', $plan->imagen_url) }}" placeholder="https://..." data-plan-preview-url></label>
    </div>
</div>
<script>
(function(){
    var file=document.querySelector('[data-plan-preview-file]');
    var url=document.querySelector('[data-plan-preview-url]');
    var img=document.querySelector('[data-plan-preview-img]');
    var empty=document.querySelector('[data-plan-preview-empty]');
    function show(src){ if(!img)return; if(src){img.src=src;img.hidden=false; if(empty)empty.hidden=true;} else {img.hidden=true; if(empty)empty.hidden=false;} }
    file?.addEventListener('change',function(){ var f=file.files&&file.files[0]; if(!f)return; var r=new FileReader(); r.onload=function(e){show(e.target.result)}; r.readAsDataURL(f); });
    url?.addEventListener('input',function(){ if(file&&file.files&&file.files.length)return; show(url.value.trim()); });
})();
</script>
<div class="ad-form-row">
    <label><span>Precio *</span><input type="number" step="0.01" name="precio" value="{{ old('precio', $plan->precio ?? 0) }}" required></label>
    <label><span>Moneda *</span><input type="text" name="moneda" value="{{ old('moneda', $plan->moneda ?? 'COP') }}" required></label>
</div>
<div class="ad-form-row">
    <label><span>Duración (días) *</span><input type="number" name="duracion_dias" value="{{ old('duracion_dias', $plan->duracion_dias ?? 30) }}" required></label>
    <label><span>Días de gracia</span><input type="number" name="dias_gracia" value="{{ old('dias_gracia', $plan->dias_gracia ?? 3) }}" min="0"></label>
</div>
<div class="ad-form-row">
    <label><span>Modalidad *</span>
        <select name="modalidad" required>
            @foreach (['VIRTUAL','PRESENCIAL','MIXTO'] as $m)
                <option value="{{ $m }}" @selected(old('modalidad', $plan->modalidad) === $m)>{{ $m }}</option>
            @endforeach
        </select>
    </label>
    <label><span>Tipo cliente *</span>
        <select name="tipo_cliente" required>
            @foreach (['INDIVIDUAL','INSTITUCIONAL','AMBOS'] as $t)
                <option value="{{ $t }}" @selected(old('tipo_cliente', $plan->tipo_cliente ?? 'INDIVIDUAL') === $t)>{{ $t }}</option>
            @endforeach
        </select>
    </label>
</div>
<div class="ad-form-block"><h3>Cupos del plan</h3>
    <div class="ad-form-row three">
        <label><span>Cupos totales *</span><input type="number" name="cupos_totales" value="{{ old('cupos_totales', $plan->cupos_totales ?? 0) }}" min="0" required></label>
        <label><span>Cupo máximo por grupo</span><input type="number" name="cupo_maximo" value="{{ old('cupo_maximo', $plan->cupo_maximo) }}" min="1"></label>
        <label><span>Orden en catálogo</span><input type="number" name="orden" value="{{ old('orden', $plan->orden ?? 0) }}"></label>
    </div>
    @if ($readonlyCupos ?? false)
        <div class="ad-form-row">
            <label><span>Cupos reservados</span><input type="number" value="{{ (int) $plan->cupos_reservados }}" disabled></label>
            <label><span>Cupos ocupados</span><input type="number" value="{{ (int) $plan->cupos_ocupados }}" disabled></label>
        </div>
    @endif
</div>
<div class="ad-form-row">
    <label><span>Estado *</span>
        <select name="estado_plan" required>
            @foreach (['ACTIVO','INACTIVO','BORRADOR'] as $e)
                <option value="{{ $e }}" @selected(old('estado_plan', $plan->estado_plan ?? 'BORRADOR') === $e)>{{ $e }}</option>
            @endforeach
        </select>
    </label>
    <label><span>Etiqueta promocional</span><input type="text" name="etiqueta" value="{{ old('etiqueta', $plan->etiqueta) }}"></label>
</div>
<label><span>Descripción</span><textarea name="descripcion" rows="2">{{ old('descripcion', $plan->descripcion) }}</textarea></label>
<label><span>Inclusiones</span><textarea name="inclusiones" rows="2">{{ old('inclusiones', $plan->inclusiones) }}</textarea></label>
<label><span>Beneficios</span><textarea name="beneficios" rows="2">{{ old('beneficios', $plan->beneficios) }}</textarea></label>
<label><span>Características (una por línea)</span><textarea name="caracteristicas" rows="4">{{ old('caracteristicas', collect($plan->caracteristicas_json ?? [])->implode("\n")) }}</textarea></label>
<div>
    <span class="ad-form-label">Módulos incluidos</span>
    <div class="ad-checks">
        @foreach ($modulos as $modulo)
            <label>
                <input type="checkbox" name="modulos[]" value="{{ $modulo->id_modulo }}" @checked(in_array($modulo->id_modulo, $selected))>
                {{ $modulo->nombre }}
            </label>
        @endforeach
    </div>
</div>
<div class="ad-checks inline">
    <label><input type="checkbox" name="requiere_coach" value="1" @checked(old('requiere_coach', $plan->requiere_coach))> Requiere coach</label>
    <label><input type="checkbox" name="renovacion_permitida" value="1" @checked(old('renovacion_permitida', $plan->renovacion_permitida ?? true))> Renovación permitida</label>
    <label><input type="checkbox" name="renovacion_automatica" value="1" @checked(old('renovacion_automatica', $plan->renovacion_automatica))> Renovación automática</label>
    <label><input type="checkbox" name="popular" value="1" @checked(old('popular', $plan->popular))> Popular</label>
    <label><input type="checkbox" name="destacado" value="1" @checked(old('destacado', $plan->destacado))> Destacado</label>
</div>
