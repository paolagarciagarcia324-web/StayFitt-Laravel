@extends('layouts.staff')

@section('title', 'Novedades welcome')
@section('subtitle', 'Anuncios con foto obligatoria cerca del login')

@section('actions')
    <a class="ad-btn" href="{{ route('staff.novedades.create') }}">Nueva novedad</a>
@endsection

@section('content')
    <section class="ad-card">
        <div class="ad-table-wrap">
            <table class="ad-table">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Título</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Orden</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($novedades as $n)
                        <tr>
                            <td>
                                <img src="{{ $n->imagenSrc() }}" alt="" style="width:64px;height:40px;object-fit:cover;border-radius:8px">
                            </td>
                            <td>
                                <strong>{{ $n->titulo }}</strong>
                                @if ($n->subtitulo)
                                    <div class="ad-muted">{{ $n->subtitulo }}</div>
                                @endif
                            </td>
                            <td>{{ $n->etiquetaTipo() }}</td>
                            <td>{{ $n->activo ? 'Activa' : 'Inactiva' }}</td>
                            <td>{{ $n->orden }}</td>
                            <td class="ad-actions">
                                <a class="ad-btn ad-btn-ghost" href="{{ route('staff.novedades.edit', $n) }}">Editar</a>
                                <form method="POST" action="{{ route('staff.novedades.destroy', $n) }}" onsubmit="return confirm('¿Eliminar novedad?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="ad-btn ad-btn-ghost" type="submit">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No hay novedades. Crea una con foto para enganchar en el welcome.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
