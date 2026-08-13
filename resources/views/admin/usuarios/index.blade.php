@extends('layouts.admin')

@section('title', 'Usuarios')
@section('subtitle', 'Cuentas y roles del sistema')

@section('content')
    <section class="ad-card">
        <table class="ad-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Estado</th>
                    <th>Roles</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($usuarios as $u)
                    <tr>
                        <td>{{ $u->nombres }} {{ $u->apellidos }}</td>
                        <td>{{ $u->correo }}</td>
                        <td><span class="ad-badge {{ $u->estado === 'ACTIVO' ? 'ad-badge-ok' : 'ad-badge-bad' }}">{{ $u->estado }}</span></td>
                        <td>{{ $u->roles->pluck('codigo')->join(', ') ?: '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="ad-pagination">{{ $usuarios->links() }}</div>
    </section>
@endsection
