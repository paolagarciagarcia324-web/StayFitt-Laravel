@extends('layouts.staff')

@section('title', 'Propuestas operativas')

@section('content')
    <div class="st-page">
        <div class="st-kicker">Flujo editorial</div>
        <h1 class="st-title">Propuestas de plan</h1>

        <form method="GET" class="st-form-row" style="max-width:400px">
            <div class="st-field">
                <label>Filtrar estado</label>
                <select name="estado" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    @foreach (['BORRADOR_ENTRENAMIENTO','BORRADOR_NUTRICION','PENDIENTE_REVISION','RECHAZADO','PUBLICADO'] as $e)
                        <option value="{{ $e }}" @selected($estado === $e)>{{ $e }}</option>
                    @endforeach
                </select>
            </div>
        </form>

        <div class="st-clients">
            @forelse ($propuestas as $p)
                <article class="st-client">
                    <strong>{{ $p->titulo_interno }}</strong>
                    <small style="color:var(--st-muted)">Coach: {{ $p->coach?->usuario?->nombres }}</small>
                    <span class="st-badge is-violet">{{ $p->etiquetaEstado() }}</span>
                    @if ($p->creador)<small>Creado por: {{ $p->creador->nombres }}</small>@endif
                    <div class="st-client__actions">
                        <a class="st-btn" style="padding:8px 14px;font-size:11px" href="{{ route('coach.propuestas.entrenamiento', $p) }}">Abrir</a>
                    </div>
                </article>
            @empty
                <p class="st-empty">Sin propuestas. <a class="st-link" href="{{ route('coach.propuestas.create') }}">Crear</a></p>
            @endforelse
        </div>
    </div>
@endsection
