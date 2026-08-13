@extends('layouts.admin')

@section('title', $plan->exists ? 'Editar plan' : 'Nuevo plan')
@section('subtitle', 'Configuración comercial y módulos')

@section('content')
    <section class="ad-card">
        <form class="ad-form" method="POST" action="{{ $plan->exists ? route('admin.planes.update', $plan) : route('admin.planes.store') }}">
            @csrf
            @if ($plan->exists) @method('PUT') @endif

            <div class="ad-form-row">
                <label>
                    <span>Nombre</span>
                    <input type="text" name="nombre" value="{{ old('nombre', $plan->nombre) }}" required>
                </label>
                <label>
                    <span>Precio</span>
                    <input type="number" step="0.01" name="precio" value="{{ old('precio', $plan->precio ?? 0) }}" required>
                </label>
            </div>

            <div class="ad-form-row">
                <label>
                    <span>Moneda</span>
                    <input type="text" name="moneda" value="{{ old('moneda', $plan->moneda ?? 'COP') }}" required>
                </label>
                <label>
                    <span>Duración (días)</span>
                    <input type="number" name="duracion_dias" value="{{ old('duracion_dias', $plan->duracion_dias ?? 30) }}" required>
                </label>
            </div>

            <div class="ad-form-row">
                <label>
                    <span>Modalidad</span>
                    <select name="modalidad" required>
                        @foreach (['VIRTUAL','PRESENCIAL','MIXTO'] as $m)
                            <option value="{{ $m }}" @selected(old('modalidad', $plan->modalidad) === $m)>{{ $m }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span>Tipo cliente</span>
                    <select name="tipo_cliente" required>
                        @foreach (['INDIVIDUAL','INSTITUCIONAL','AMBOS'] as $t)
                            <option value="{{ $t }}" @selected(old('tipo_cliente', $plan->tipo_cliente ?? 'INDIVIDUAL') === $t)>{{ $t }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div class="ad-form-row">
                <label>
                    <span>Estado</span>
                    <select name="estado_plan" required>
                        @foreach (['ACTIVO','INACTIVO','BORRADOR'] as $e)
                            <option value="{{ $e }}" @selected(old('estado_plan', $plan->estado_plan ?? 'BORRADOR') === $e)>{{ $e }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span>Etiqueta</span>
                    <input type="text" name="etiqueta" value="{{ old('etiqueta', $plan->etiqueta) }}">
                </label>
            </div>

            <label>
                <span>Descripción</span>
                <textarea name="descripcion" rows="3">{{ old('descripcion', $plan->descripcion) }}</textarea>
            </label>

            <label>
                <span>Características (una por línea)</span>
                <textarea name="caracteristicas" rows="5">{{ old('caracteristicas', collect($plan->caracteristicas_json ?? [])->implode("\n")) }}</textarea>
            </label>

            <div>
                <span style="display:block;margin-bottom:8px;font-size:12px;color:var(--muted)">Módulos incluidos</span>
                <div class="ad-checks">
                    @php $selected = old('modulos', $plan->exists ? $plan->modulos->pluck('id_modulo')->all() : []); @endphp
                    @foreach ($modulos as $modulo)
                        <label>
                            <input type="checkbox" name="modulos[]" value="{{ $modulo->id_modulo }}" @checked(in_array($modulo->id_modulo, $selected))>
                            {{ $modulo->nombre }}
                        </label>
                    @endforeach
                </div>
            </div>

            <label style="display:flex;gap:8px;align-items:center;color:var(--muted);font-size:13px">
                <input type="checkbox" name="popular" value="1" @checked(old('popular', $plan->popular))>
                Marcar como popular
            </label>

            <div class="ad-actions">
                <button class="ad-btn" type="submit">Guardar</button>
                <a class="ad-btn ad-btn-ghost" href="{{ route('admin.planes.index') }}">Cancelar</a>
            </div>
        </form>
    </section>
@endsection
