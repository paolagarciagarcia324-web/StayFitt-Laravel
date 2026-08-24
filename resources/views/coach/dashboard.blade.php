@extends('layouts.coach')

@section('title', 'Dashboard coach')
@section('subtitle', 'Propuestas de plan y contenido')

@section('actions')
    <a class="ad-btn" href="{{ route('coach.propuestas.create') }}">Nueva propuesta</a>
@endsection

@section('content')
    <div class="ad-kpis">
        <div class="ad-kpi accent-pink">
            <div class="ad-kpi-copy">
                <strong>{{ $stats['borradores'] }}</strong>
                <span>Borradores</span>
            </div>
        </div>
        <div class="ad-kpi accent-cyan">
            <div class="ad-kpi-copy">
                <strong>{{ $stats['pendientes'] }}</strong>
                <span>En revisión</span>
            </div>
        </div>
        <div class="ad-kpi accent-pink">
            <div class="ad-kpi-copy">
                <strong>{{ $stats['publicados'] }}</strong>
                <span>Publicados</span>
            </div>
        </div>
        <div class="ad-kpi accent-cyan">
            <div class="ad-kpi-copy">
                <strong>{{ $stats['rechazados'] }}</strong>
                <span>Rechazados</span>
            </div>
        </div>
    </div>

    <section class="ad-card">
        <div class="ad-card-head">
            <h2>Propuestas recientes</h2>
            <a class="ad-btn ad-btn-ghost" href="{{ route('coach.propuestas.index') }}">Ver todas</a>
        </div>
        <div class="ad-table-wrap">
            <table class="ad-table">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recientes as $p)
                        <tr>
                            <td>{{ $p->titulo_interno }}</td>
                            <td><span class="ad-tag">{{ $p->etiquetaEstado() }}</span></td>
                            <td>
                                <a href="{{ route('coach.propuestas.entrenamiento', $p) }}">Abrir</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3">Aún no hay propuestas. Crea la primera con videos por día y nutrición.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
