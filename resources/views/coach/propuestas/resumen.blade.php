@extends('layouts.coach')

@section('title', $propuesta->titulo_interno)
@section('subtitle', 'Paso 3 · Revisar y enviar a administración')

@section('content')
    @include('coach.propuestas._wizard', ['propuesta' => $propuesta, 'step' => 3])

    <section class="ad-card" style="margin-bottom:16px">
        <div class="ad-card-head">
            <h2>Resumen</h2>
            <span class="ad-tag">{{ $propuesta->etiquetaEstado() }}</span>
        </div>
        <p><strong>{{ $propuesta->titulo_interno }}</strong></p>
        <p class="ad-muted">{{ $propuesta->descripcion_interna }}</p>
        <p>Coach: {{ $propuesta->coach?->usuario?->nombres }} {{ $propuesta->coach?->usuario?->apellidos }}</p>
        <p>Clases/videos: <strong>{{ $videos->where('estado','ACTIVO')->count() }}</strong></p>
        <p>Comidas: <strong>{{ $nutricion?->comidas?->count() ?? 0 }}</strong></p>
        @if ($nutricion)
            <p>Nutrición: {{ $nutricion->nombre }} · Objetivo: {{ $nutricion->objetivo ?: '—' }}</p>
        @endif
        @if ($propuesta->motivo_rechazo)
            <div class="ad-alert ad-alert-error">Motivo de rechazo: {{ $propuesta->motivo_rechazo }}</div>
        @endif
    </section>

    <div class="ad-modal-actions">
        <a class="ad-btn ad-btn-ghost" href="{{ route('coach.propuestas.entrenamiento', $propuesta) }}">Editar videos</a>
        <a class="ad-btn ad-btn-ghost" href="{{ route('coach.propuestas.nutricion', $propuesta) }}">Editar nutrición</a>
        @if ($propuesta->esEditable() || $propuesta->estado === 'BORRADOR_NUTRICION')
            <form method="POST" action="{{ route('coach.propuestas.enviar', $propuesta) }}">
                @csrf
                <button class="ad-btn" type="submit" @disabled(! $propuesta->puedeEnviarARevision())>
                    Enviar a revisión
                </button>
            </form>
        @endif
    </div>
    @unless ($propuesta->puedeEnviarARevision())
        <p class="ad-muted" style="margin-top:12px">Para enviar necesitas al menos 1 video, nombre y objetivo de nutrición, y 1 comida.</p>
    @endunless
@endsection
