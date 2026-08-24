@extends('layouts.coach')

@section('title', 'Propuestas de plan')
@section('subtitle', 'Entrenamiento + nutrición → revisión admin')

@section('actions')
    <a class="ad-btn" href="{{ route('coach.propuestas.create') }}">Nueva propuesta</a>
@endsection

@section('content')
    <section class="ad-card">
        <form class="ad-toolbar" method="GET">
            <input type="search" name="q" value="{{ $q }}" placeholder="Buscar por título…">
            <select name="estado">
                <option value="">Todos los estados</option>
                @foreach ([
                    'BORRADOR_ENTRENAMIENTO' => 'Borrador entrenamiento',
                    'BORRADOR_NUTRICION' => 'Borrador nutrición',
                    'PENDIENTE_REVISION' => 'Pendiente revisión',
                    'RECHAZADO' => 'Rechazado',
                    'PUBLICADO' => 'Publicado',
                ] as $val => $label)
                    <option value="{{ $val }}" @selected($estado === $val)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="ad-btn" type="submit">Filtrar</button>
        </form>

        <div class="ad-table-wrap">
            <table class="ad-table">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Coach</th>
                        <th>Estado</th>
                        <th>Videos</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($propuestas as $p)
                        <tr>
                            <td>
                                <strong>{{ $p->titulo_interno }}</strong>
                                @if ($p->motivo_rechazo)
                                    <div class="ad-muted">Rechazo: {{ \Illuminate\Support\Str::limit($p->motivo_rechazo, 80) }}</div>
                                @endif
                            </td>
                            <td>{{ $p->coach?->usuario?->nombres }} {{ $p->coach?->usuario?->apellidos }}</td>
                            <td><span class="ad-tag">{{ $p->etiquetaEstado() }}</span></td>
                            <td>{{ $p->conteoVideos() }}</td>
                            <td class="ad-actions">
                                @if ($p->esEditable() || $p->estado === 'BORRADOR_ENTRENAMIENTO')
                                    <a class="ad-btn ad-btn-ghost" href="{{ route('coach.propuestas.entrenamiento', $p) }}">Continuar</a>
                                @elseif ($p->estado === 'BORRADOR_NUTRICION')
                                    <a class="ad-btn ad-btn-ghost" href="{{ route('coach.propuestas.nutricion', $p) }}">Nutrición</a>
                                @else
                                    <a class="ad-btn ad-btn-ghost" href="{{ route('coach.propuestas.resumen', $p) }}">Ver</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5">No hay propuestas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
