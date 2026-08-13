@extends('layouts.admin')

@section('title', 'Instituciones')
@section('subtitle', 'Convenios y sedes institucionales')

@section('content')
    <section class="ad-card">
        <h2>Nueva institución</h2>
        <form class="ad-form" method="POST" action="{{ route('admin.instituciones.store') }}">
            @csrf
            <div class="ad-form-row">
                <label><span>Nombre</span><input type="text" name="nombre" required></label>
                <label><span>Código</span><input type="text" name="codigo_institucion"></label>
            </div>
            <div class="ad-form-row">
                <label><span>NIT</span><input type="text" name="nit"></label>
                <label><span>Ciudad</span><input type="text" name="ciudad"></label>
            </div>
            <div class="ad-form-row">
                <label><span>Correo</span><input type="email" name="correo_contacto"></label>
                <label><span>Teléfono</span><input type="text" name="telefono_contacto"></label>
            </div>
            <button class="ad-btn" type="submit">Guardar</button>
        </form>
    </section>

    <section class="ad-card">
        <h2>Listado</h2>
        <table class="ad-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Ciudad</th>
                    <th>Contacto</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($instituciones as $i)
                    <tr>
                        <td>{{ $i->nombre }}</td>
                        <td>{{ $i->ciudad ?: '—' }}</td>
                        <td>{{ $i->correo_contacto ?: $i->telefono_contacto ?: '—' }}</td>
                        <td><span class="ad-badge ad-badge-ok">{{ $i->estado }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="ad-empty">Sin instituciones.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
