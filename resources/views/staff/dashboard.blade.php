@extends('layouts.staff')

@section('title', 'Panel asistente')
@section('subtitle', 'Apoyo operativo: propuestas y novedades')

@section('content')
    <div class="ad-kpis">
        <a class="ad-kpi accent-pink" href="{{ route('coach.propuestas.index') }}">
            <div class="ad-kpi-copy">
                <strong>{{ $stats['propuestas_borrador'] }}</strong>
                <span>Propuestas en borrador</span>
            </div>
        </a>
        <a class="ad-kpi accent-cyan" href="{{ route('coach.propuestas.index', ['estado' => 'PENDIENTE_REVISION']) }}">
            <div class="ad-kpi-copy">
                <strong>{{ $stats['propuestas_pendientes'] }}</strong>
                <span>En revisión admin</span>
            </div>
        </a>
        <a class="ad-kpi accent-pink" href="{{ route('staff.novedades.index') }}">
            <div class="ad-kpi-copy">
                <strong>{{ $stats['novedades_activas'] }}</strong>
                <span>Novedades activas</span>
            </div>
        </a>
    </div>

    <section class="ad-card">
        <div class="ad-card-head"><h2>Accesos rápidos</h2></div>
        <div class="ad-modal-actions">
            <a class="ad-btn" href="{{ route('coach.propuestas.create') }}">Nueva propuesta</a>
            <a class="ad-btn" href="{{ route('staff.novedades.create') }}">Nueva novedad</a>
        </div>
    </section>
@endsection
