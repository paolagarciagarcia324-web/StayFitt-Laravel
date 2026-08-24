@extends('layouts.admin')

@section('title', 'Revisar propuesta')
@section('subtitle', $propuesta->titulo_interno)

@section('actions')
    <a class="ad-btn ad-btn-ghost" href="{{ route('admin.propuestas.index') }}">Volver</a>
    @if (in_array($propuesta->estado, ['PENDIENTE_REVISION', 'APROBADO'], true))
        <a class="ad-btn" href="{{ route('admin.propuestas.publicar.form', $propuesta) }}">Publicar en welcome</a>
    @endif
@endsection

@section('content')
    <section class="ad-card" style="margin-bottom:16px">
        <div class="ad-card-head">
            <h2>Detalle</h2>
            <span class="ad-tag">{{ $propuesta->etiquetaEstado() }}</span>
        </div>
        <p>{{ $propuesta->descripcion_interna }}</p>
        <p>Coach: <strong>{{ $propuesta->coach?->usuario?->nombres }} {{ $propuesta->coach?->usuario?->apellidos }}</strong></p>
        <p>Videos: {{ $videos->where('estado','ACTIVO')->count() }} · Comidas: {{ $propuesta->planNutricional?->comidas?->count() ?? 0 }}</p>
        @if ($propuesta->planPublicado)
            <p>Plan publicado: <a href="{{ route('admin.planes.edit', $propuesta->planPublicado) }}">{{ $propuesta->planPublicado->nombre }}</a></p>
        @endif
    </section>

    <div class="ad-split">
        <section class="ad-card">
            <div class="ad-card-head"><h2>Entrenamiento</h2></div>
            <ul class="ad-list">
                @forelse ($videos->where('estado','ACTIVO') as $v)
                    <li><strong>Día {{ $v->dia_orden }}:</strong> {{ $v->titulo }}</li>
                @empty
                    <li>Sin videos</li>
                @endforelse
            </ul>
        </section>
        <section class="ad-card">
            <div class="ad-card-head"><h2>Nutrición</h2></div>
            @if ($propuesta->planNutricional)
                <p><strong>{{ $propuesta->planNutricional->nombre }}</strong></p>
                <p>Objetivo: {{ $propuesta->planNutricional->objetivo }}</p>
                <p>Kcal: {{ $propuesta->planNutricional->kcal_objetivo ?: '—' }}</p>
                <ul class="ad-list">
                    @foreach ($propuesta->planNutricional->comidas as $c)
                        <li>{{ $c->tipo_comida }} · {{ $c->nombre }}</li>
                    @endforeach
                </ul>
            @else
                <p>Sin nutrición</p>
            @endif
        </section>
    </div>

    @if ($propuesta->estado === 'PENDIENTE_REVISION')
        <section class="ad-card" style="margin-top:16px">
            <div class="ad-card-head"><h2>Rechazar</h2></div>
            <form class="ad-form" method="POST" action="{{ route('admin.propuestas.rechazar', $propuesta) }}">
                @csrf
                <label>
                    Motivo (obligatorio)
                    <textarea name="motivo_rechazo" rows="3" required>{{ old('motivo_rechazo') }}</textarea>
                </label>
                <button class="ad-btn ad-btn-ghost" type="submit">Rechazar y devolver al coach</button>
            </form>
        </section>
    @endif
@endsection
