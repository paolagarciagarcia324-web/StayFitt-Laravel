@extends('layouts.coach')

@section('title', $propuesta->titulo_interno)
@section('subtitle', 'Paso 2 · Nutrición obligatoria antes de enviar')

@section('actions')
    <a class="ad-btn ad-btn-ghost" href="{{ route('coach.propuestas.entrenamiento', $propuesta) }}">Videos</a>
    <a class="ad-btn" href="{{ route('coach.propuestas.resumen', $propuesta) }}">Ir a resumen</a>
@endsection

@section('content')
    @include('coach.propuestas._wizard', ['propuesta' => $propuesta, 'step' => 2])

    @if ($propuesta->esEditable())
        <section class="ad-card" style="margin-bottom:16px">
            <div class="ad-card-head"><h2>Plan nutricional</h2></div>
            <form class="ad-form" method="POST" action="{{ route('coach.propuestas.nutricion.update', $propuesta) }}">
                @csrf
                @method('PUT')
                <div class="ad-form-grid">
                    <label>
                        Nombre
                        <input type="text" name="nombre" value="{{ old('nombre', $nutricion?->nombre) }}" required maxlength="120">
                    </label>
                    <label>
                        Objetivo
                        <input type="text" name="objetivo" value="{{ old('objetivo', $nutricion?->objetivo) }}" required maxlength="120" placeholder="Ej. Déficit controlado">
                    </label>
                    <label>
                        Kcal objetivo
                        <input type="number" name="kcal_objetivo" value="{{ old('kcal_objetivo', $nutricion?->kcal_objetivo) }}" min="0">
                    </label>
                    <label>
                        Proteínas (g)
                        <input type="number" step="0.01" name="proteinas_g" value="{{ old('proteinas_g', $nutricion?->proteinas_g) }}">
                    </label>
                    <label>
                        Carbohidratos (g)
                        <input type="number" step="0.01" name="carbohidratos_g" value="{{ old('carbohidratos_g', $nutricion?->carbohidratos_g) }}">
                    </label>
                    <label>
                        Grasas (g)
                        <input type="number" step="0.01" name="grasas_g" value="{{ old('grasas_g', $nutricion?->grasas_g) }}">
                    </label>
                    <label class="ad-span-2">
                        Recomendaciones
                        <textarea name="recomendaciones" rows="3">{{ old('recomendaciones', $nutricion?->recomendaciones) }}</textarea>
                    </label>
                </div>
                <button class="ad-btn" type="submit">Guardar nutrición</button>
            </form>
        </section>

        <section class="ad-card" style="margin-bottom:16px">
            <div class="ad-card-head"><h2>Agregar comida</h2></div>
            <form class="ad-form" method="POST" action="{{ route('coach.propuestas.comidas.store', $propuesta) }}">
                @csrf
                <div class="ad-form-grid">
                    <label>
                        Tipo
                        <select name="tipo_comida" required>
                            @foreach (['DESAYUNO','ALMUERZO','CENA','SNACK','MERIENDA','OTRO'] as $t)
                                <option value="{{ $t }}" @selected(old('tipo_comida') === $t)>{{ $t }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        Nombre
                        <input type="text" name="nombre" value="{{ old('nombre') }}" required maxlength="150">
                    </label>
                    <label>
                        Porción
                        <input type="text" name="porcion" value="{{ old('porcion') }}">
                    </label>
                    <label>
                        Calorías
                        <input type="number" step="0.01" name="calorias" value="{{ old('calorias') }}">
                    </label>
                    <label class="ad-span-2">
                        Receta / preparación
                        <textarea name="receta" rows="2">{{ old('receta') }}</textarea>
                    </label>
                </div>
                <button class="ad-btn" type="submit">Agregar comida</button>
            </form>
        </section>
    @endif

    <section class="ad-card">
        <div class="ad-card-head">
            <h2>Comidas ({{ $nutricion?->comidas?->count() ?? 0 }})</h2>
        </div>
        <div class="ad-table-wrap">
            <table class="ad-table">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Nombre</th>
                        <th>Porción</th>
                        <th>Kcal</th>
                        @if ($propuesta->esEditable())
                            <th></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse (($nutricion?->comidas ?? collect()) as $c)
                        <tr>
                            <td>{{ $c->tipo_comida }}</td>
                            <td>{{ $c->nombre }}</td>
                            <td>{{ $c->porcion ?: '—' }}</td>
                            <td>{{ $c->calorias ?: '—' }}</td>
                            @if ($propuesta->esEditable())
                                <td>
                                    <form method="POST" action="{{ route('coach.propuestas.comidas.destroy', [$propuesta, $c->id_comida]) }}" onsubmit="return confirm('¿Eliminar comida?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="ad-btn ad-btn-ghost" type="submit">Quitar</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="5">Agrega al menos una comida para poder enviar la propuesta.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
