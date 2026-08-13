@extends('layouts.admin')

@section('title', 'Clientes')
@section('subtitle', 'Perfiles cliente vinculados a usuarios')

@section('content')
    <section class="ad-card">
        <table class="ad-table">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Modalidad</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Alta</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($clientes as $c)
                    <tr>
                        <td>
                            {{ $c->usuario?->nombres }} {{ $c->usuario?->apellidos }}<br>
                            <small style="color:var(--muted)">{{ $c->usuario?->correo }}</small>
                        </td>
                        <td>{{ $c->modalidad }}</td>
                        <td>{{ $c->tipo_cliente }}</td>
                        <td><span class="ad-badge {{ $c->estado_cliente === 'ACTIVA' ? 'ad-badge-ok' : 'ad-badge-pendiente' }}">{{ $c->estado_cliente }}</span></td>
                        <td>{{ $c->fecha_alta }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="ad-empty">Aún no hay clientes. Se crean al aprobar solicitudes.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="ad-pagination">{{ $clientes->links() }}</div>
    </section>
@endsection
