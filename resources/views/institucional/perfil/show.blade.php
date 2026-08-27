@extends('layouts.institucional')

@section('title', 'Perfil institucional')

@section('content')
    <div class="in-page">
        <section class="in-hero">
            <div class="in-org-hero-logo">
                @if ($institucion->logo_url)
                    <img src="{{ $institucion->logo_url }}" alt="">
                @else
                    {{ mb_substr($institucion->nombre, 0, 2) }}
                @endif
            </div>
            <div>
                <div class="in-kicker">Perfil empresarial</div>
                <h1 class="in-title">{{ $institucion->nombre }}</h1>
                <p class="in-sub">NIT {{ $institucion->nit ?? '—' }} · {{ $institucion->ciudad ?? 'Colombia' }}</p>
            </div>
        </section>

        <div class="in-profile-grid">
            <div class="in-info-pill"><span>Tipo</span><strong>{{ $institucion->etiquetaTipo() }}</strong></div>
            <div class="in-info-pill"><span>Estado convenio</span><strong>{{ $institucion->estado }}</strong></div>
            <div class="in-info-pill"><span>Plan</span><strong>{{ $institucion->planContratado?->nombre ?? 'Sin plan asignado' }}</strong></div>
            <div class="in-info-pill"><span>Inicio</span><strong>{{ $institucion->fecha_inicio?->format('d/m/Y') ?? '—' }}</strong></div>
            <div class="in-info-pill"><span>Fin</span><strong>{{ $institucion->fecha_fin?->format('d/m/Y') ?? '—' }}</strong></div>
            <div class="in-info-pill"><span>Cupos</span><strong>{{ $institucion->cupos_usados }} / {{ $institucion->cupos_totales ?: '∞' }}</strong></div>
        </div>

        <div class="in-grid-12">
            <div class="in-span-6">
                <x-institucional.card title="Información general">
                    <div class="in-profile-grid" style="grid-template-columns:1fr 1fr">
                        <div class="in-info-pill"><span>Dirección</span><strong>{{ $institucion->direccion ?? '—' }}</strong></div>
                        <div class="in-info-pill"><span>Teléfono</span><strong>{{ $institucion->telefono ?? '—' }}</strong></div>
                        <div class="in-info-pill"><span>Correo</span><strong>{{ $institucion->correo ?? '—' }}</strong></div>
                        <div class="in-info-pill"><span>Convenio</span><strong>{{ $institucion->convenio ?? '—' }}</strong></div>
                    </div>
                </x-institucional.card>
            </div>
            <div class="in-span-6">
                <x-institucional.card title="Contacto principal">
                    <div class="in-profile-grid" style="grid-template-columns:1fr">
                        <div class="in-info-pill"><span>Responsable</span><strong>{{ $institucion->contacto_nombre ?? '—' }}</strong></div>
                        <div class="in-info-pill"><span>Cargo</span><strong>{{ $institucion->contacto_cargo ?? '—' }}</strong></div>
                        <div class="in-info-pill"><span>Correo contacto</span><strong>{{ $institucion->correo ?? '—' }}</strong></div>
                    </div>
                </x-institucional.card>
            </div>
        </div>

        <x-institucional.card title="Actualizar información" subtitle="Campos permitidos para el responsable institucional">
            <form method="POST" action="{{ route('institucional.perfil.update') }}">
                @csrf
                @method('PUT')
                <div class="in-form-grid">
                    <div class="in-field"><label>Nombre institución</label><input type="text" name="nombre" value="{{ old('nombre', $institucion->nombre) }}" required></div>
                    <div class="in-field">
                        <label>Tipo</label>
                        <select name="tipo_institucion">
                            @foreach (['EDUCATIVA' => 'Educativa', 'EMPRESA' => 'Empresa', 'GIMNASIO' => 'Gimnasio / Wellness', 'GOBIERNO' => 'Gobierno', 'OTRO' => 'Otro'] as $k => $v)
                                <option value="{{ $k }}" @selected(old('tipo_institucion', $institucion->tipo_institucion) === $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="in-field"><label>NIT</label><input type="text" name="nit" value="{{ old('nit', $institucion->nit) }}"></div>
                    <div class="in-field"><label>Logo URL</label><input type="url" name="logo_url" value="{{ old('logo_url', $institucion->logo_url) }}" placeholder="https://..."></div>
                    <div class="in-field"><label>Dirección</label><input type="text" name="direccion" value="{{ old('direccion', $institucion->direccion) }}"></div>
                    <div class="in-field"><label>Ciudad</label><input type="text" name="ciudad" value="{{ old('ciudad', $institucion->ciudad) }}"></div>
                    <div class="in-field"><label>Teléfono</label><input type="text" name="telefono" value="{{ old('telefono', $institucion->telefono) }}"></div>
                    <div class="in-field"><label>Correo institucional</label><input type="email" name="correo" value="{{ old('correo', $institucion->correo) }}"></div>
                    <div class="in-field"><label>Responsable</label><input type="text" name="contacto_nombre" value="{{ old('contacto_nombre', $institucion->contacto_nombre) }}"></div>
                    <div class="in-field"><label>Cargo</label><input type="text" name="contacto_cargo" value="{{ old('contacto_cargo', $institucion->contacto_cargo) }}"></div>
                </div>
                <div style="margin-top:16px">
                    <button type="submit" class="in-btn">Guardar cambios</button>
                </div>
            </form>
        </x-institucional.card>
    </div>
@endsection
