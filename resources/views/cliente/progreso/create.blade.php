@extends('layouts.cliente')

@section('content')
    <x-cliente.page-head
        kicker="Progreso"
        title="Nuevo registro"
        subtitle="Registra cómo te sientes y tus medidas de hoy."
    >
        <x-slot:actions>
            <a class="cv-btn cv-btn-ghost cv-btn-sm" href="{{ route('cliente.progreso.index') }}">Cancelar</a>
        </x-slot:actions>
    </x-cliente.page-head>

    <x-cliente.card title="Medidas y bienestar" subtitle="Todos los campos son opcionales excepto la fecha">
        <form method="POST" action="{{ route('cliente.progreso.store') }}" class="cv-form">
            @csrf
            <label>Fecha
                <input type="date" name="fecha_registro" value="{{ old('fecha_registro', now()->toDateString()) }}" required>
            </label>
            <label>Peso (kg)
                <input type="number" step="0.1" name="peso_kg" value="{{ old('peso_kg') }}">
            </label>
            <label>Estatura (cm)
                <input type="number" step="0.1" name="estatura_cm" value="{{ old('estatura_cm', $cliente->estatura_cm) }}">
            </label>
            <label>% grasa
                <input type="number" step="0.1" name="porcentaje_grasa" value="{{ old('porcentaje_grasa') }}">
            </label>
            <label>Masa muscular (kg)
                <input type="number" step="0.1" name="masa_muscular_kg" value="{{ old('masa_muscular_kg') }}">
            </label>
            <label>Cintura (cm)
                <input type="number" step="0.1" name="cintura_cm" value="{{ old('cintura_cm') }}">
            </label>
            <label>Cadera (cm)
                <input type="number" step="0.1" name="cadera_cm" value="{{ old('cadera_cm') }}">
            </label>
            <label>Brazo (cm)
                <input type="number" step="0.1" name="brazo_cm" value="{{ old('brazo_cm') }}">
            </label>
            <label>Energía (1-10)
                <input type="number" min="1" max="10" name="nivel_energia" value="{{ old('nivel_energia') }}">
            </label>
            <label>Sueño (1-10)
                <input type="number" min="1" max="10" name="calidad_sueno" value="{{ old('calidad_sueno') }}">
            </label>
            <label>Estrés (1-10)
                <input type="number" min="1" max="10" name="nivel_estres" value="{{ old('nivel_estres') }}">
            </label>
            <label>Agua (L)
                <input type="number" step="0.1" name="agua_litros" value="{{ old('agua_litros') }}">
            </label>
            <div class="cv-span-2">
                <button class="cv-btn" type="submit">Guardar registro</button>
            </div>
        </form>
    </x-cliente.card>
@endsection
