@extends('layouts.staff')

@section('title', 'Gestión de rutinas')

@section('content')
    <div class="st-page">
        <div class="st-kicker">Apoyo en entrenamiento</div>
        <h1 class="st-title">Rutinas y borradores</h1>
        <p class="st-sub">Crea propuestas iniciales — el coach aprueba antes de publicar al cliente.</p>

        <x-staff.card title="Nuevo borrador de plan">
            <form method="POST" action="{{ route('staff.rutinas.borrador.store') }}">
                @csrf
                <div class="st-form-row">
                    <div class="st-field">
                        <label>Cliente</label>
                        <select name="id_cliente" required>
                            @foreach ($clientes as $c)
                                <option value="{{ $c->id_cliente }}">{{ $c->usuario?->nombres }} {{ $c->usuario?->apellidos }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="st-field"><label>Nombre</label><input name="nombre" required placeholder="Plan fuerza 4 sem"></div>
                    <div class="st-field"><label>Objetivo / nivel</label><input name="objetivo" placeholder="Intermedio"></div>
                </div>
                <button type="submit" class="st-btn">Crear borrador</button>
            </form>
        </x-staff.card>

        <x-staff.card title="Propuestas en flujo" subtitle="Estados: borrador → revisión → aprobada">
            <div class="st-clients">
                @forelse ($propuestas as $p)
                    <article class="st-client">
                        <strong>{{ $p->titulo_interno }}</strong>
                        <span class="st-badge is-violet">{{ $p->etiquetaEstado() }}</span>
                        <div class="st-client__actions">
                            <a class="st-link" href="{{ route('coach.propuestas.entrenamiento', $p) }}">Editar →</a>
                            @if (in_array($p->estado, ['BORRADOR_ENTRENAMIENTO', 'BORRADOR_NUTRICION']))
                                <a class="st-link" href="{{ route('coach.propuestas.resumen', $p) }}">Enviar revisión</a>
                            @endif
                        </div>
                    </article>
                @empty
                    <p class="st-empty">Sin propuestas. <a class="st-link" href="{{ route('coach.propuestas.create') }}">Crear propuesta</a></p>
                @endforelse
            </div>
        </x-staff.card>

        <x-staff.card title="Planes de entrenamiento">
            @forelse ($planes as $plan)
                <div class="st-routine">
                    <h3>{{ $plan->nombre }} <span class="st-badge is-cyan">{{ $plan->estado }}</span></h3>
                    <small>{{ $plan->cliente?->usuario?->nombres }} · {{ $plan->rutinas->count() }} rutinas</small>
                </div>
            @empty
                <p class="st-empty">Sin planes registrados.</p>
            @endforelse
        </x-staff.card>
    </div>
@endsection
