@extends('layouts.coach')

@section('title', 'Entrenamiento')

@section('content')
    <div class="ch-page">
        <div class="ch-kicker">Planificación</div>
        <h1 class="ch-title">Gestión de entrenamiento</h1>
        <p class="ch-sub">Crea planes, rutinas y asigna ejercicios a tus clientes.</p>

        <x-coach.card title="Nuevo plan de entrenamiento" subtitle="Asignar a un cliente de tu cartera">
            <form method="POST" action="{{ route('coach.entrenamiento.planes.store') }}">
                @csrf
                <div class="ch-form-row">
                    <div class="ch-field">
                        <label>Cliente</label>
                        <select name="id_cliente" required>
                            <option value="">Seleccionar…</option>
                            @foreach ($clientes as $c)
                                <option value="{{ $c->id_cliente }}" @selected(request('cliente') == $c->id_cliente)>
                                    {{ trim(($c->usuario?->nombres ?? '').' '.($c->usuario?->apellidos ?? '')) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ch-field">
                        <label>Nombre del plan</label>
                        <input type="text" name="nombre" required placeholder="Plan mensual full body">
                    </div>
                    <div class="ch-field">
                        <label>Objetivo / nivel</label>
                        <input type="text" name="objetivo" placeholder="Intermedio · Fuerza">
                    </div>
                </div>
                <button type="submit" class="ch-btn">Crear plan</button>
            </form>
        </x-coach.card>

        <x-coach.card title="Planes activos" subtitle="{{ $planes->count() }} planes en tu biblioteca">
            @if ($planes->isEmpty())
                <p class="ch-empty">Aún no has creado planes. Usa el formulario superior para comenzar.</p>
            @else
                <div class="ch-clients">
                    @foreach ($planes as $plan)
                        @php $cu = $plan->cliente?->usuario; @endphp
                        <article class="ch-client">
                            <strong style="font-size:14px">{{ $plan->nombre }}</strong>
                            <small style="color:var(--ch-muted)">
                                {{ trim(($cu?->nombres ?? 'Cliente').' '.($cu?->apellidos ?? '')) }}
                                · {{ $plan->rutinas->count() }} rutinas
                            </small>
                            <span class="ch-badge is-cyan">{{ $plan->estado }}</span>
                            <div class="ch-client__actions">
                                <a class="ch-btn" style="padding:8px 14px;font-size:11px" href="{{ route('coach.entrenamiento.show', $plan->id_plan_entrenamiento) }}">Gestionar</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </x-coach.card>
    </div>
@endsection
