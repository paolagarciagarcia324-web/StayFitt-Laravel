@extends('layouts.admin')

@section('title', 'Propuestas de plan')
@section('subtitle', 'Contenido del coach listo para datos comerciales')

@section('content')
    <section class="ad-card">
        <form class="ad-toolbar" method="GET">
            <input type="search" name="q" value="{{ $q }}" placeholder="Buscar…">
            <select name="estado">
                <option value="TODOS" @selected($estado === 'TODOS')>Todos</option>
                @foreach ([
                    'PENDIENTE_REVISION' => 'Pendiente revisión',
                    'BORRADOR_ENTRENAMIENTO' => 'Borrador entrenamiento',
                    'BORRADOR_NUTRICION' => 'Borrador nutrición',
                    'RECHAZADO' => 'Rechazado',
                    'PUBLICADO' => 'Publicado',
                ] as $val => $label)
                    <option value="{{ $val }}" @selected($estado === $val)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="ad-btn" type="submit">Filtrar</button>
            @if ($pendientes > 0)
                <span class="ad-tag">{{ $pendientes }} pendientes</span>
            @endif
        </form>

        <div class="ad-table-wrap">
            <table class="ad-table">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Coach</th>
                        <th>Estado</th>
                        <th>Enviado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($propuestas as $p)
                        <tr>
                            <td><strong>{{ $p->titulo_interno }}</strong></td>
                            <td>{{ $p->coach?->usuario?->nombres }} {{ $p->coach?->usuario?->apellidos }}</td>
                            <td><span class="ad-tag">{{ $p->etiquetaEstado() }}</span></td>
                            <td>{{ $p->enviado_en?->format('d/m/Y H:i') ?: '—' }}</td>
                            <td>
                                <a class="ad-btn ad-btn-ghost" href="{{ route('admin.propuestas.show', $p) }}">Revisar</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5">No hay propuestas con ese filtro.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
