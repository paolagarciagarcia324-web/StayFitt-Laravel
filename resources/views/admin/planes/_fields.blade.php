<div class="ad-plan-form-body">
    <section class="ad-plan-section ad-plan-identity">
        <h3 class="ad-plan-section-title">Identidad</h3>
        <div class="ad-plan-identity-grid">
            <div class="ad-media-upload-block ad-plan-media">
                <div class="ad-ml-preview" data-plan-preview>
                    @if ($plan->imagenSrc())
                        <img src="{{ $plan->imagenSrc() }}" alt="{{ $plan->nombre }}" data-plan-preview-img>
                    @else
                        <img data-plan-preview-img hidden alt="Preview">
                        <span class="ad-ml-placeholder" data-plan-preview-empty>@include('partials.admin-icon', ['icon' => 'planes'])</span>
                    @endif
                </div>
            </div>
            <div class="ad-plan-identity-fields">
                <div class="ad-form-row">
                    <label><span>Código</span><input type="text" name="codigo" value="{{ old('codigo', $plan->codigo) }}" data-field="codigo"></label>
                    <label><span>Nombre *</span><input type="text" name="nombre" value="{{ old('nombre', $plan->nombre) }}" required data-field="nombre"></label>
                </div>
                <label class="ad-ml-upload"><span>Subir foto del plan</span><input type="file" name="imagen" accept="image/*" data-plan-preview-file></label>
                <label><span>O URL</span><input type="url" name="imagen_url" value="{{ old('imagen_url', $plan->imagen_url) }}" placeholder="https://..." data-plan-preview-url data-field="imagen_url"></label>
            </div>
        </div>
    </section>

    <section class="ad-plan-section">
        <h3 class="ad-plan-section-title">Comercial</h3>
        <div class="ad-form-row">
            <label><span>Precio *</span><input type="number" step="0.01" name="precio" value="{{ old('precio', $plan->precio ?? 0) }}" required data-field="precio"></label>
            <label><span>Moneda *</span><input type="text" name="moneda" value="{{ old('moneda', $plan->moneda ?? 'COP') }}" required data-field="moneda"></label>
        </div>
        <div class="ad-form-row">
            <label><span>Duración (días) *</span><input type="number" name="duracion_dias" value="{{ old('duracion_dias', $plan->duracion_dias ?? 30) }}" required data-field="duracion_dias"></label>
            <label><span>Días de gracia</span><input type="number" name="dias_gracia" value="{{ old('dias_gracia', $plan->dias_gracia ?? 3) }}" min="0" data-field="dias_gracia"></label>
        </div>
        <div class="ad-form-row">
            <label><span>Modalidad *</span>
                <select name="modalidad" required data-field="modalidad">
                    @foreach (['VIRTUAL','PRESENCIAL','MIXTO'] as $m)
                        <option value="{{ $m }}" @selected(old('modalidad', $plan->modalidad) === $m)>{{ $m }}</option>
                    @endforeach
                </select>
            </label>
            <label><span>Tipo cliente *</span>
                <select name="tipo_cliente" required data-field="tipo_cliente">
                    @foreach (['INDIVIDUAL','INSTITUCIONAL','AMBOS'] as $t)
                        <option value="{{ $t }}" @selected(old('tipo_cliente', $plan->tipo_cliente ?? 'INDIVIDUAL') === $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </label>
        </div>
    </section>

    <section class="ad-plan-section">
        <h3 class="ad-plan-section-title">Cupos</h3>
        <div class="ad-form-row three ad-plan-cupos-row">
            <label><span>Cupos totales *</span><input type="number" name="cupos_totales" value="{{ old('cupos_totales', $plan->cupos_totales ?? 0) }}" min="0" required data-field="cupos_totales"></label>
            <label><span>Cupo máximo por grupo</span><input type="number" name="cupo_maximo" value="{{ old('cupo_maximo', $plan->cupo_maximo) }}" min="1" data-field="cupo_maximo"></label>
            <label><span>Orden en catálogo</span><input type="number" name="orden" value="{{ old('orden', $plan->orden ?? 0) }}" data-field="orden"></label>
        </div>
        <div class="ad-form-row ad-plan-cupos-readonly" data-plan-cupos-readonly @if (! ($readonlyCupos ?? false)) hidden @endif>
            <label><span>Cupos reservados</span><input type="number" value="{{ (int) ($plan->cupos_reservados ?? 0) }}" disabled data-field="cupos_reservados"></label>
            <label><span>Cupos ocupados</span><input type="number" value="{{ (int) ($plan->cupos_ocupados ?? 0) }}" disabled data-field="cupos_ocupados"></label>
        </div>
    </section>

    <section class="ad-plan-section">
        <h3 class="ad-plan-section-title">Estado y promoción</h3>
        <div class="ad-form-row">
            <label><span>Estado *</span>
                <select name="estado_plan" required data-field="estado_plan">
                    @foreach (['ACTIVO','INACTIVO','BORRADOR'] as $e)
                        <option value="{{ $e }}" @selected(old('estado_plan', $plan->estado_plan ?? 'BORRADOR') === $e)>{{ $e }}</option>
                    @endforeach
                </select>
            </label>
            <label><span>Etiqueta promocional</span><input type="text" name="etiqueta" value="{{ old('etiqueta', $plan->etiqueta) }}" data-field="etiqueta"></label>
        </div>
    </section>

    <section class="ad-plan-section">
        <h3 class="ad-plan-section-title">Contenido</h3>
        <div class="ad-plan-content-grid">
            <label><span>Descripción</span><textarea name="descripcion" rows="3" data-field="descripcion">{{ old('descripcion', $plan->descripcion) }}</textarea></label>
            <label><span>Inclusiones</span><textarea name="inclusiones" rows="3" data-field="inclusiones">{{ old('inclusiones', $plan->inclusiones) }}</textarea></label>
            <label><span>Beneficios</span><textarea name="beneficios" rows="3" data-field="beneficios">{{ old('beneficios', $plan->beneficios) }}</textarea></label>
            <label><span>Características (una por línea)</span><textarea name="caracteristicas" rows="3" data-field="caracteristicas">{{ old('caracteristicas', collect($plan->caracteristicas_json ?? [])->implode("\n")) }}</textarea></label>
        </div>
    </section>

    <section class="ad-plan-section">
        <h3 class="ad-plan-section-title">Módulos incluidos</h3>
        <div class="ad-plan-checks">
            @foreach ($modulos as $modulo)
                <label class="ad-plan-check ad-plan-chip">
                    <input type="checkbox" name="modulos[]" value="{{ $modulo->id_modulo }}" data-modulo="{{ $modulo->id_modulo }}" @checked(in_array($modulo->id_modulo, $selected))>
                    <span>{{ $modulo->nombre }}</span>
                </label>
            @endforeach
        </div>
    </section>

    <section class="ad-plan-section">
        <h3 class="ad-plan-section-title">Opciones</h3>
        <div class="ad-plan-flags">
            <label class="ad-plan-check ad-plan-chip"><input type="checkbox" name="requiere_coach" value="1" data-field="requiere_coach" @checked(old('requiere_coach', $plan->requiere_coach))><span>Requiere coach</span></label>
            <label class="ad-plan-check ad-plan-chip"><input type="checkbox" name="renovacion_permitida" value="1" data-field="renovacion_permitida" @checked(old('renovacion_permitida', $plan->renovacion_permitida ?? true))><span>Renovación permitida</span></label>
            <label class="ad-plan-check ad-plan-chip"><input type="checkbox" name="renovacion_automatica" value="1" data-field="renovacion_automatica" @checked(old('renovacion_automatica', $plan->renovacion_automatica))><span>Renovación automática</span></label>
            <label class="ad-plan-check ad-plan-chip"><input type="checkbox" name="popular" value="1" data-field="popular" @checked(old('popular', $plan->popular))><span>Popular</span></label>
            <label class="ad-plan-check ad-plan-chip"><input type="checkbox" name="destacado" value="1" data-field="destacado" @checked(old('destacado', $plan->destacado))><span>Destacado</span></label>
        </div>
    </section>
</div>
