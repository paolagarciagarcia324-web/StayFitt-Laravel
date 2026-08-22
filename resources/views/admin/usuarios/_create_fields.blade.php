<section class="ad-user-create-section ad-user-create-perfil">
    <div class="ad-user-create-perfil-card">
        <div class="ad-user-create-perfil-preview" data-user-create-preview>
            <img data-user-create-img hidden alt="Preview">
            <span class="ad-user-create-perfil-empty" data-user-create-empty>@include('partials.admin-icon', ['icon' => 'personas'])</span>
            <span class="ad-user-create-perfil-ring" aria-hidden="true"></span>
        </div>
        <div class="ad-user-create-perfil-content">
            <header class="ad-user-create-perfil-head">
                <span class="ad-user-create-perfil-tag">Perfil</span>
                <strong>Foto de identificación</strong>
                <p>Para la ficha presencial y reconocimiento en sala. Formatos JPG, PNG o WEBP.</p>
            </header>
            <div class="ad-user-create-perfil-grid">
                <label class="ad-user-create-perfil-drop">
                    <span class="ad-user-create-perfil-drop-ico" aria-hidden="true">@include('partials.admin-icon', ['icon' => 'upload'])</span>
                    <span class="ad-user-create-perfil-drop-text">
                        <strong>Subir archivo</strong>
                        <em>Arrastra o haz clic</em>
                    </span>
                    <input type="file" name="foto" accept="image/*" data-user-create-file>
                </label>
                <div class="ad-user-create-perfil-split" aria-hidden="true"><span>o</span></div>
                <label class="ad-user-create-perfil-url">
                    <span>Enlace externo</span>
                    <input type="url" name="foto_perfil_url" value="{{ old('foto_perfil_url') }}" placeholder="https://tu-imagen.com/foto.jpg" data-user-create-url>
                </label>
            </div>
        </div>
    </div>
</section>

<section class="ad-user-create-section">
    <h3 class="ad-user-create-title">Identidad</h3>
    <div class="ad-form-row">
        <label><span>Nombres *</span><input type="text" name="nombres" value="{{ old('nombres') }}" required></label>
        <label><span>Apellidos *</span><input type="text" name="apellidos" value="{{ old('apellidos') }}" required></label>
    </div>
    <div class="ad-form-row">
        <label>
            <span>Tipo de documento *</span>
            <select name="tipo_documento" required>
                @foreach (['CC' => 'Cédula', 'CE' => 'Cédula extranjería', 'TI' => 'Tarjeta identidad', 'PASAPORTE' => 'Pasaporte', 'OTRO' => 'Otro'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('tipo_documento', 'CC') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label><span>Documento *</span><input type="text" name="documento_identidad" value="{{ old('documento_identidad') }}" required></label>
    </div>
    <div class="ad-form-row">
        <label><span>Correo *</span><input type="email" name="correo" value="{{ old('correo') }}" required></label>
        <label><span>Teléfono *</span><input type="text" name="telefono" value="{{ old('telefono') }}" required></label>
    </div>
    <div class="ad-form-row three">
        <label><span>Ciudad *</span><input type="text" name="ciudad" value="{{ old('ciudad') }}" required></label>
        <label><span>Nacimiento *</span><input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" required></label>
        <label>
            <span>Género *</span>
            <select name="genero" required>
                <option value="FEMENINO" @selected(old('genero', 'FEMENINO') === 'FEMENINO')">Femenino</option>
            </select>
        </label>
    </div>
    <label><span>Objetivo *</span><input type="text" name="objetivo_principal" value="{{ old('objetivo_principal') }}" placeholder="Ej. fuerza, recomposición, movilidad" required></label>
</section>

<section class="ad-user-create-section">
    <h3 class="ad-user-create-title">Antropometría</h3>
    <div class="ad-user-create-metrics">
        <label>
            <span>Peso</span>
            <input type="number" step="0.01" name="peso_kg" value="{{ old('peso_kg') }}" required>
            <em>kg</em>
        </label>
        <label>
            <span>Estatura</span>
            <input type="number" step="0.01" name="estatura_cm" value="{{ old('estatura_cm') }}" required>
            <em>cm</em>
        </label>
        <label>
            <span>Cintura</span>
            <input type="number" step="0.01" name="cintura_cm" value="{{ old('cintura_cm') }}" required>
            <em>cm</em>
        </label>
        <label>
            <span>Cadera</span>
            <input type="number" step="0.01" name="cadera_cm" value="{{ old('cadera_cm') }}" required>
            <em>cm</em>
        </label>
        <label>
            <span>Brazo</span>
            <input type="number" step="0.01" name="brazo_cm" value="{{ old('brazo_cm') }}" required>
            <em>cm</em>
        </label>
        <label>
            <span>Pierna</span>
            <input type="number" step="0.01" name="pierna_cm" value="{{ old('pierna_cm') }}" required>
            <em>cm</em>
        </label>
    </div>
</section>

<section class="ad-user-create-section">
    <h3 class="ad-user-create-title">Salud y emergencia</h3>
    <div class="ad-form-row">
        <label><span>Alergias</span><textarea name="alergias" rows="2" placeholder="Ninguna">{{ old('alergias') }}</textarea></label>
        <label><span>Condiciones médicas</span><textarea name="condiciones_medicas" rows="2">{{ old('condiciones_medicas') }}</textarea></label>
    </div>
    <div class="ad-form-row">
        <label><span>Lesiones</span><textarea name="lesiones" rows="2">{{ old('lesiones') }}</textarea></label>
        <label><span>Medicamentos</span><textarea name="medicamentos" rows="2">{{ old('medicamentos') }}</textarea></label>
    </div>
    <div class="ad-form-row">
        <label><span>Contacto de emergencia *</span><input type="text" name="contacto_emergencia_nombre" value="{{ old('contacto_emergencia_nombre') }}" required></label>
        <label><span>Teléfono de emergencia *</span><input type="text" name="contacto_emergencia_telefono" value="{{ old('contacto_emergencia_telefono') }}" required></label>
    </div>
</section>

<section class="ad-user-create-section">
    <h3 class="ad-user-create-title">Cupo horario</h3>
    <div class="ad-horario-pick ad-user-create-horarios">
        @forelse ($horarios as $horario)
            <label class="ad-horario-opt">
                <input type="radio" name="id_horario" value="{{ $horario->id_horario }}" @checked((string) old('id_horario') === (string) $horario->id_horario) required>
                <span>
                    <strong>{{ $horario->nombre }}</strong>
                    <em>{{ $horario->dias }} · {{ $horario->rangoHora12() }}</em>
                </span>
            </label>
        @empty
            <p class="ad-empty">No hay horarios activos.</p>
        @endforelse
    </div>
    <label><span>Observaciones</span><textarea name="observacion_usuario" rows="2" placeholder="Opcional">{{ old('observacion_usuario') }}</textarea></label>
</section>
