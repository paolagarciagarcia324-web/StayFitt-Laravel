@extends('layouts.cliente')

@section('content')
    <x-cliente.page-head
        kicker="Rutina"
        :title="$rutina->nombre"
        :subtitle="$rutina->planEntrenamiento?->nombre"
    >
        <x-slot:actions>
            <a class="cv-btn cv-btn-ghost cv-btn-sm" href="{{ route('cliente.entrenamiento.index') }}">Volver</a>
        </x-slot:actions>
    </x-cliente.page-head>

    <div class="cv-grid-12">
        <div class="cv-span-7">
            <x-cliente.card title="Ejercicios" :subtitle="$rutina->descripcion ?: 'Sigue el orden indicado por tu coach'">
                <div class="cv-list-feed">
                    @forelse ($rutina->ejercicios as $item)
                        <article class="cv-feed-item">
                            <div class="cv-tile__ico" aria-hidden="true">{{ $item->orden }}</div>
                            <div>
                                <strong>{{ $item->ejercicio?->nombre ?? 'Ejercicio' }}</strong>
                                <p style="margin:4px 0 0;color:var(--cv-muted);font-size:12.5px;line-height:1.45">
                                    @if ($item->series)
                                        {{ $item->series }} series
                                    @endif
                                    @if ($item->repeticiones)
                                        · {{ $item->repeticiones }} reps
                                    @endif
                                    @if ($item->descanso_seg)
                                        · descanso {{ $item->descanso_seg }}s
                                    @endif
                                    @if ($item->peso_sugerido_kg)
                                        · {{ $item->peso_sugerido_kg }} kg
                                    @endif
                                </p>
                                @if ($item->notas)
                                    <p style="margin:6px 0 0;font-size:12px;color:var(--cv-faint)">{{ $item->notas }}</p>
                                @endif
                            </div>
                            <span class="cv-badge is-cyan">#{{ $item->orden }}</span>
                        </article>
                    @empty
                        <p class="cv-empty">Sin ejercicios cargados.</p>
                    @endforelse
                </div>
            </x-cliente.card>
        </div>

        <div class="cv-span-5 cv-profile-rail">
            <x-cliente.card title="Marcar completada" subtitle="Registra cómo te fue la sesión">
                <form method="POST" action="{{ route('cliente.entrenamiento.completar', $rutina->id_rutina) }}" class="cv-form">
                    @csrf
                    <label>Dificultad (1-10)
                        <input type="number" name="dificultad" min="1" max="10" value="{{ old('dificultad', 5) }}">
                    </label>
                    <label class="cv-span-2">Observación
                        <textarea name="observacion_cliente" rows="3">{{ old('observacion_cliente') }}</textarea>
                    </label>
                    <div class="cv-span-2">
                        <button class="cv-btn" type="submit">Completar rutina</button>
                    </div>
                </form>
            </x-cliente.card>

            <x-cliente.card title="Historial" subtitle="Sesiones anteriores de esta rutina">
                <div class="cv-list-feed">
                    @forelse ($historial as $h)
                        <div class="cv-feed-item">
                            <div class="cv-tile__ico is-pink" aria-hidden="true">✓</div>
                            <div>
                                <strong>{{ optional($h->fecha_ejecucion)->format('d/m/Y') }}</strong>
                                @if ($h->observacion_cliente)
                                    <p style="margin:4px 0 0;font-size:12px;color:var(--cv-faint)">{{ \Illuminate\Support\Str::limit($h->observacion_cliente, 80) }}</p>
                                @endif
                            </div>
                            <span class="cv-badge {{ $h->dificultad ? 'is-pink' : 'is-ok' }}">
                                {{ $h->dificultad ? 'Dif. '.$h->dificultad : 'OK' }}
                            </span>
                        </div>
                    @empty
                        <p class="cv-empty">Primera vez con esta rutina. ¡Dale con todo!</p>
                    @endforelse
                </div>
            </x-cliente.card>
        </div>
    </div>
@endsection
