@extends('layouts.admin')

@section('title', 'Planes')
@section('subtitle', 'Catálogo comercial')

@section('content')
    <div class="ad-actions" style="margin-bottom:14px">
        <a class="ad-btn" href="{{ route('admin.planes.create') }}">Nuevo plan</a>
    </div>

    <section class="ad-card">
        <table class="ad-table">
            <thead>
                <tr>
                    <th>Plan</th>
                    <th>Precio</th>
                    <th>Modalidad</th>
                    <th>Estado</th>
                    <th>Módulos</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($planes as $plan)
                    <tr>
                        <td>
                            <strong>{{ $plan->nombre }}</strong><br>
                            <small style="color:var(--muted)">{{ $plan->slug }}</small>
                        </td>
                        <td>{{ $plan->precioFormateado() }}</td>
                        <td>{{ $plan->modalidad }}</td>
                        <td><span class="ad-badge {{ $plan->estado_plan === 'ACTIVO' ? 'ad-badge-ok' : 'ad-badge-bad' }}">{{ $plan->estado_plan }}</span></td>
                        <td>{{ $plan->modulos->pluck('nombre')->join(', ') }}</td>
                        <td><a class="ad-btn ad-btn-ghost" href="{{ route('admin.planes.edit', $plan) }}">Editar</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
@endsection
