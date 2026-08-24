@extends('layouts.coach')

@section('title', 'Nueva propuesta')
@section('subtitle', 'Paso 0 · Datos base del plan de contenido')

@section('content')
    <section class="ad-card">
        <form class="ad-form" method="POST" action="{{ route('coach.propuestas.store') }}">
            @csrf
            <div class="ad-form-grid">
                <label>
                    Título interno
                    <input type="text" name="titulo_interno" value="{{ old('titulo_interno') }}" required maxlength="150" placeholder="Ej. Reto 30 días full body">
                </label>
                @if ($mustPickCoach)
                    <label>
                        Coach responsable
                        <select name="id_coach" required>
                            <option value="">Selecciona…</option>
                            @foreach ($coaches as $c)
                                <option value="{{ $c->id_coach }}" @selected(old('id_coach') == $c->id_coach)>
                                    {{ $c->usuario?->nombres }} {{ $c->usuario?->apellidos }} ({{ $c->codigo_coach }})
                                </option>
                            @endforeach
                        </select>
                    </label>
                @endif
                <label class="ad-span-2">
                    Descripción interna
                    <textarea name="descripcion_interna" rows="3">{{ old('descripcion_interna') }}</textarea>
                </label>
                <label class="ad-span-2">
                    Notas para el equipo
                    <textarea name="notas_coach" rows="2">{{ old('notas_coach') }}</textarea>
                </label>
            </div>
            <div class="ad-modal-actions">
                <button class="ad-btn" type="submit">Crear y subir videos</button>
                <a class="ad-btn ad-btn-ghost" href="{{ route('coach.propuestas.index') }}">Cancelar</a>
            </div>
        </form>
    </section>
@endsection
