@extends('layouts.admin')

@section('title', 'Planes')
@section('subtitle', 'Catálogo y cupos')

@section('content')
    <section class="ad-card ad-users">
        <div class="ad-users-bar">
            <form class="ad-users-search" method="GET" action="{{ route('admin.planes.index') }}">
                <span>⌕</span>
                <input type="search" name="q" value="{{ $q }}" placeholder="Buscar plan">
            </form>
            <button class="ad-btn ad-btn-add" type="button" data-open-plan-form>+ Crear plan</button>
        </div>

        <table class="ad-table ad-users-table">
            <thead>
                <tr>
                    <th></th>
                    <th>Plan</th>
                    <th>Precio</th>
                    <th>Modalidad</th>
                    <th>Cupos</th>
                    <th>Estado</th>
                    <th>Módulos</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($planes as $plan)
                    <tr>
                        <td>@include('partials.admin-media-thumb', ['src' => $plan->imagenSrc(), 'alt' => $plan->nombre, 'icon' => 'planes'])</td>
                        <td>
                            <strong>{{ $plan->nombre }}</strong>
                            <em class="ad-user-mail">{{ $plan->codigo ?: $plan->slug }}</em>
                        </td>
                        <td>{{ $plan->precioFormateado() }}</td>
                        <td>{{ $plan->modalidad }}</td>
                        <td>
                            <span class="ad-pill-days">{{ (int) $plan->cupos_ocupados }}/{{ (int) $plan->cupos_totales }}</span>
                            @if ($plan->cupo_maximo)
                                <em class="ad-user-mail">máx {{ $plan->cupo_maximo }}</em>
                            @endif
                        </td>
                        <td>
                            <span class="ad-badge {{ $plan->estado_plan === 'ACTIVO' ? 'ad-badge-ok' : ($plan->estado_plan === 'BORRADOR' ? 'ad-badge-pendiente' : 'ad-badge-bad') }}">{{ $plan->estado_plan }}</span>
                        </td>
                        <td>{{ $plan->modulos->count() }}</td>
                        <td><a class="ad-btn ad-btn-ghost" href="{{ route('admin.planes.edit', $plan) }}">Editar</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="ad-empty">No hay planes en el catálogo.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>

    @include('admin.planes._form-modal', ['plan' => new \App\Models\Plan, 'modulos' => $modulos])
@endsection
