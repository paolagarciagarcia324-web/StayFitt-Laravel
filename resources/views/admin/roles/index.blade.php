@extends('layouts.admin')

@section('title', 'Roles')
@section('subtitle', 'Catálogo de roles del sistema (solo lectura)')

@section('content')
    <x-admin.panel title="Roles disponibles" subtitle="La asignación a usuarios se gestiona desde Personas → Usuarios. El rol ADMIN no se asigna desde esa pantalla." flush>
        <div class="ad-table-wrap">
            <table class="ad-table ad-users-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Usuarios</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($roles as $rol)
                        <tr>
                            <td><strong>{{ $rol->codigoDisplay() }}</strong></td>
                            <td>{{ $rol->nombreDisplay() }}</td>
                            <td class="ad-user-mail">{{ $rol->descripcion ?: '—' }}</td>
                            <td>{{ $rol->usuarios_count }}</td>
                            <td>
                                <span class="ad-badge {{ ($rol->estado ?? 'ACTIVO') === 'ACTIVO' ? 'ad-badge-ok' : 'ad-badge-bad' }}">
                                    {{ $rol->estado ?? 'ACTIVO' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-admin.panel>
@endsection
