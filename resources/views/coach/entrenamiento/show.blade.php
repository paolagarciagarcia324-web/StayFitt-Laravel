@extends('layouts.coach')

@section('title', $plan->nombre)

@section('content')
    @php $cu = $plan->cliente?->usuario; @endphp

    <div class="ch-page">
        <div style="margin-bottom:8px">
            <a class="ch-link" href="{{ route('coach.entrenamiento.index') }}">← Entrenamiento</a>
        </div>

        <section class="ch-hero" style="grid-template-columns:1fr auto">
            <div>
                <div class="ch-kicker">Plan de entrenamiento</div>
                <h1 class="ch-title">{{ $plan->nombre }}</h1>
                <p class="ch-sub">
                    {{ trim(($cu?->nombres ?? 'Cliente').' '.($cu?->apellidos ?? '')) }}
                    · {{ $plan->objetivo ?? 'Sin objetivo' }} · {{ $plan->modalidad }}
                </p>
            </div>
            <span class="ch-badge is-ok">{{ $plan->estado }}</span>
        </section>

        <div class="ch-grid-12">
            <div class="ch-span-7">
                <x-coach.card title="Rutinas del plan" subtitle="{{ $plan->rutinas->count() }} sesiones configuradas">
                    @forelse ($plan->rutinas as $rutina)
                        <div class="ch-routine">
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px">
                                <h3>{{ $rutina->nombre }}</h3>
                                @if ($rutina->duracion_min)
                                    <span class="ch-badge is-cyan">{{ $rutina->duracion_min }} min</span>
                                @endif
                            </div>
                            @if ($rutina->descripcion)
                                <p style="font-size:12px;color:var(--ch-muted);margin:0 0 8px">{{ $rutina->descripcion }}</p>
                            @endif
                            @forelse ($rutina->ejercicios as $re)
                                <div class="ch-exercise">
                                    <span>{{ $re->ejercicio?->nombre ?? 'Ejercicio #'.$re->id_ejercicio }}</span>
                                    <span>{{ $re->series ?? '—' }}×{{ $re->repeticiones ?? '—' }} · desc. {{ $re->descanso_seg ?? 60 }}s</span>
                                </div>
                            @empty
                                <p class="ch-empty">Sin ejercicios en esta rutina.</p>
                            @endforelse
                        </div>
                    @empty
                        <p class="ch-empty">Agrega la primera rutina con el formulario.</p>
                    @endforelse
                </x-coach.card>
            </div>

            <div class="ch-span-5">
                <x-coach.card title="Agregar rutina" subtitle="Nombre, nivel y ejercicios">
                    <form method="POST" action="{{ route('coach.entrenamiento.rutinas.store', $plan->id_plan_entrenamiento) }}">
                        @csrf
                        <div class="ch-field" style="margin-bottom:10px">
                            <label>Nombre</label>
                            <input type="text" name="nombre" required placeholder="Día A — Tren superior">
                        </div>
                        <div class="ch-form-row">
                            <div class="ch-field">
                                <label>Nivel</label>
                                <select name="nivel">
                                    <option value="">—</option>
                                    <option>Principiante</option>
                                    <option>Intermedio</option>
                                    <option>Avanzado</option>
                                </select>
                            </div>
                            <div class="ch-field">
                                <label>Duración (min)</label>
                                <input type="number" name="duracion_min" min="5" max="240" placeholder="45">
                            </div>
                        </div>
                        <div class="ch-field" style="margin-bottom:10px">
                            <label>Descripción</label>
                            <textarea name="descripcion" rows="2" placeholder="Enfoque del día, calentamiento..."></textarea>
                        </div>

                        <p style="font-size:11px;color:var(--ch-muted);margin:12px 0 8px;text-transform:uppercase;letter-spacing:.06em">Ejercicio principal (opcional)</p>
                        <div class="ch-form-row">
                            <div class="ch-field">
                                <label>Del catálogo</label>
                                <select name="ejercicios[0][id_ejercicio]">
                                    <option value="">Nuevo / manual</option>
                                    @foreach ($ejerciciosCatalogo as $ej)
                                        <option value="{{ $ej->id_ejercicio }}">{{ $ej->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="ch-field">
                                <label>O nombre nuevo</label>
                                <input type="text" name="ejercicios[0][nombre_ejercicio]" placeholder="Press banca">
                            </div>
                        </div>
                        <div class="ch-form-row">
                            <div class="ch-field">
                                <label>Series</label>
                                <input type="number" name="ejercicios[0][series]" value="3" min="1">
                            </div>
                            <div class="ch-field">
                                <label>Repeticiones</label>
                                <input type="text" name="ejercicios[0][repeticiones]" value="12">
                            </div>
                            <div class="ch-field">
                                <label>Descanso (s)</label>
                                <input type="number" name="ejercicios[0][descanso_seg]" value="60">
                            </div>
                        </div>
                        <button type="submit" class="ch-btn" style="margin-top:12px">Agregar rutina</button>
                    </form>
                </x-coach.card>
            </div>
        </div>
    </div>
@endsection
