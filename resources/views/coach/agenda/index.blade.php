@extends('layouts.coach')

@section('title', 'Agenda')

@section('content')
    <div class="ch-page">
        <div class="ch-kicker">Calendario profesional</div>
        <h1 class="ch-title">Agenda de sesiones</h1>
        <p class="ch-sub">Programa entrenamientos presenciales, virtuales o híbridos.</p>

        <div class="ch-grid-12">
            <div class="ch-span-5">
                <x-coach.card title="Programar sesión">
                    <form method="POST" action="{{ route('coach.agenda.store') }}">
                        @csrf
                        <div class="ch-field" style="margin-bottom:10px">
                            <label>Título</label>
                            <input type="text" name="titulo" required placeholder="Sesión grupal HIIT">
                        </div>
                        <div class="ch-form-row">
                            <div class="ch-field">
                                <label>Fecha</label>
                                <input type="date" name="fecha" required value="{{ now()->addDay()->toDateString() }}">
                            </div>
                            <div class="ch-field">
                                <label>Modalidad</label>
                                <select name="modalidad">
                                    <option value="PRESENCIAL">Presencial</option>
                                    <option value="VIRTUAL">Virtual</option>
                                    <option value="HIBRIDA">Híbrida</option>
                                </select>
                            </div>
                        </div>
                        <div class="ch-form-row">
                            <div class="ch-field">
                                <label>Inicio</label>
                                <input type="time" name="hora_inicio" required value="09:00">
                            </div>
                            <div class="ch-field">
                                <label>Fin</label>
                                <input type="time" name="hora_fin" required value="10:00">
                            </div>
                            <div class="ch-field">
                                <label>Cupo</label>
                                <input type="number" name="cupo_maximo" min="1" max="50" value="8">
                            </div>
                        </div>
                        <button type="submit" class="ch-btn">Programar</button>
                    </form>
                </x-coach.card>
            </div>

            <div class="ch-span-7">
                <x-coach.card title="Próximas sesiones" subtitle="{{ $proximas->count() }} programadas">
                    @forelse ($proximas as $s)
                        <div class="ch-session">
                            <div class="ch-session__date">
                                <strong>{{ optional($s->fecha)->format('d') }}</strong>
                                <small>{{ optional($s->fecha)->translatedFormat('M') }}</small>
                            </div>
                            <div style="flex:1">
                                <strong style="font-size:13px">{{ $s->titulo }}</strong>
                                <div style="font-size:11px;color:var(--ch-muted)">
                                    {{ $s->rangoHora12() }} · {{ $s->modalidad }} · {{ $s->estado }}
                                    · Cupo {{ $s->cupo_reservado }}/{{ $s->cupo_maximo }}
                                </div>
                            </div>
                            <span class="ch-badge is-cyan">{{ $s->tipo ?? 'ENTRENAMIENTO' }}</span>
                        </div>
                    @empty
                        <p class="ch-empty">No hay sesiones futuras. Programa la primera a la izquierda.</p>
                    @endforelse
                </x-coach.card>

                @if ($pasadas->isNotEmpty())
                    <x-coach.card title="Sesiones recientes" subtitle="Historial" style="margin-top:16px">
                        @foreach ($pasadas as $s)
                            <div class="ch-session">
                                <div class="ch-session__date" style="opacity:.7">
                                    <strong>{{ optional($s->fecha)->format('d') }}</strong>
                                    <small>{{ optional($s->fecha)->format('M') }}</small>
                                </div>
                                <div>
                                    <strong style="font-size:13px">{{ $s->titulo }}</strong>
                                    <div style="font-size:11px;color:var(--ch-muted)">{{ $s->rangoHora12() }} · {{ $s->estado }}</div>
                                </div>
                            </div>
                        @endforeach
                    </x-coach.card>
                @endif
            </div>
        </div>
    </div>
@endsection
