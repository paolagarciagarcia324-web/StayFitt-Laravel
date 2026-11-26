@php $df = ! empty($withDataFields); @endphp

<section class="ad-evento-section">
    <h3 class="ad-evento-section-title">Portada</h3>
    <div class="ad-evento-cover" data-evento-cover>
        <div class="ad-evento-cover-preview" data-evento-preview tabindex="0" role="button" aria-label="Vista previa de portada">
            <img data-field-preview-img {{ $df ? 'data-field=imagen_src' : '' }} hidden alt="Preview">
            <div class="ad-evento-cover-empty" data-field-preview-empty>
                <span class="ad-evento-cover-icon" aria-hidden="true">@include('partials.admin-icon', ['icon' => 'eventos'])</span>
                <strong>Portada</strong>
                <em>Arrastra o sube</em>
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
    <label><span>Descripción *</span><textarea name="descripcion" rows="2" {{ $df ? 'data-field=descripcion' : '' }} required></textarea></label>
</section>

<section class="ad-evento-section">
    <h3 class="ad-evento-section-title">Clasificación</h3>
    <div class="ad-form-row three ad-evento-class-row">
        <div class="ad-sf-select" data-sf-select>
            <span class="ad-sf-select-label">Tipo *</span>
            <input type="hidden" name="tipo" value="TALLER" {{ $df ? 'data-field=tipo' : '' }} required>
            <button type="button" class="ad-sf-select-trigger" data-sf-select-trigger aria-haspopup="listbox">
                <strong data-sf-select-display>TALLER</strong>
                <span class="ad-sf-select-chev" aria-hidden="true">@include('partials.admin-icon', ['icon' => 'chevron-down'])</span>
            </button>
            <div class="ad-sf-select-panel" data-sf-select-panel hidden role="listbox">
                @foreach (['TALLER','RETO','CLASE_ESPECIAL','OTRO'] as $t)
                    <button type="button" class="ad-sf-select-opt {{ $t === 'TALLER' ? 'is-on' : '' }}" data-sf-select-opt data-value="{{ $t }}">{{ $t }}</button>
                @endforeach
            </div>
        </div>
        <div class="ad-sf-select" data-sf-select>
            <span class="ad-sf-select-label">Modalidad *</span>
            <input type="hidden" name="modalidad" value="PRESENCIAL" {{ $df ? 'data-field=modalidad' : '' }} required>
            <button type="button" class="ad-sf-select-trigger" data-sf-select-trigger aria-haspopup="listbox">
                <strong data-sf-select-display>PRESENCIAL</strong>
                <span class="ad-sf-select-chev" aria-hidden="true">@include('partials.admin-icon', ['icon' => 'chevron-down'])</span>
            </button>
            <div class="ad-sf-select-panel" data-sf-select-panel hidden role="listbox">
                @foreach (['VIRTUAL','PRESENCIAL','MIXTO'] as $m)
                    <button type="button" class="ad-sf-select-opt {{ $m === 'PRESENCIAL' ? 'is-on' : '' }}" data-sf-select-opt data-value="{{ $m }}">{{ $m }}</button>
                @endforeach
            </div>
        </div>
        <div class="ad-sf-select" data-sf-select>
            <span class="ad-sf-select-label">Estado *</span>
            <input type="hidden" name="estado" value="PUBLICADO" {{ $df ? 'data-field=estado' : '' }} required>
            <button type="button" class="ad-sf-select-trigger" data-sf-select-trigger aria-haspopup="listbox">
                <strong data-sf-select-display>PUBLICADO</strong>
                <span class="ad-sf-select-chev" aria-hidden="true">@include('partials.admin-icon', ['icon' => 'chevron-down'])</span>
            </button>
            <div class="ad-sf-select-panel" data-sf-select-panel hidden role="listbox">
                @foreach (['BORRADOR','PUBLICADO','CERRADO','CANCELADO'] as $e)
                    <button type="button" class="ad-sf-select-opt {{ $e === 'PUBLICADO' ? 'is-on' : '' }}" data-sf-select-opt data-value="{{ $e }}">{{ $e }}</button>
                @endforeach
            </div>
        </div>
    </div>
</section>

<section class="ad-evento-section">
    <h3 class="ad-evento-section-title">Agenda y cupo</h3>
    <div class="ad-form-row three ad-evento-agenda-row">
        <div class="ad-sf-dt" data-sf-dt data-sf-for="inicio">
            <span class="ad-sf-dt-label">Inicio *</span>
            <input type="hidden" name="fecha_inicio" {{ $df ? 'data-field=inicio' : '' }} required>
            <button type="button" class="ad-sf-dt-trigger" data-sf-dt-trigger aria-haspopup="dialog">
                <strong data-sf-dt-display>— / — / — —:—</strong>
                <i aria-hidden="true">@include('partials.admin-icon', ['icon' => 'eventos'])</i>
            </button>
            <div class="ad-sf-dt-panel" data-sf-dt-panel hidden>
                <div class="ad-sf-dt-cal">
                    <header class="ad-sf-dt-cal-head">
                        <button type="button" data-sf-dt-prev aria-label="Mes anterior">‹</button>
                        <strong data-sf-dt-month>—</strong>
                        <button type="button" data-sf-dt-next aria-label="Mes siguiente">›</button>
                    </header>
                    <div class="ad-sf-dt-dow" aria-hidden="true"><em>L</em><em>M</em><em>X</em><em>J</em><em>V</em><em>S</em><em>D</em></div>
                    <div class="ad-sf-dt-grid" data-sf-dt-grid></div>
                    <div class="ad-sf-dt-cal-foot">
                        <button type="button" data-sf-dt-clear>Borrar</button>
                        <button type="button" data-sf-dt-today>Hoy</button>
                    </div>
                </div>
                <div class="ad-sf-dt-time">
                    <div class="ad-sf-dt-cols">
                        <div class="ad-sf-dt-col" data-sf-dt-col="h"></div>
                        <div class="ad-sf-dt-col" data-sf-dt-col="m"></div>
                        <div class="ad-sf-dt-col" data-sf-dt-col="ap"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="ad-sf-dt" data-sf-dt data-sf-for="fin">
            <span class="ad-sf-dt-label">Vencimiento *</span>
            <input type="hidden" name="fecha_fin" {{ $df ? 'data-field=fin' : '' }} required>
            <button type="button" class="ad-sf-dt-trigger" data-sf-dt-trigger aria-haspopup="dialog">
                <strong data-sf-dt-display>— / — / — —:—</strong>
                <i aria-hidden="true">@include('partials.admin-icon', ['icon' => 'eventos'])</i>
            </button>
            <div class="ad-sf-dt-panel" data-sf-dt-panel hidden>
                <div class="ad-sf-dt-cal">
                    <header class="ad-sf-dt-cal-head">
                        <button type="button" data-sf-dt-prev aria-label="Mes anterior">‹</button>
                        <strong data-sf-dt-month>—</strong>
                        <button type="button" data-sf-dt-next aria-label="Mes siguiente">›</button>
                    </header>
                    <div class="ad-sf-dt-dow" aria-hidden="true"><em>L</em><em>M</em><em>X</em><em>J</em><em>V</em><em>S</em><em>D</em></div>
                    <div class="ad-sf-dt-grid" data-sf-dt-grid></div>
                    <div class="ad-sf-dt-cal-foot">
                        <button type="button" data-sf-dt-clear>Borrar</button>
                        <button type="button" data-sf-dt-today>Hoy</button>
                    </div>
                </div>
                <div class="ad-sf-dt-time">
                    <div class="ad-sf-dt-cols">
                        <div class="ad-sf-dt-col" data-sf-dt-col="h"></div>
                        <div class="ad-sf-dt-col" data-sf-dt-col="m"></div>
                        <div class="ad-sf-dt-col" data-sf-dt-col="ap"></div>
                    </div>
                </div>
            </div>
        </div>
        <label class="ad-evento-cupo-field">
            <span>Cupo máximo *</span>
            <input type="number" name="cupo_maximo" min="1" {{ $df ? 'data-field=cupo' : '' }} required>
        </label>
    </div>
</section>
