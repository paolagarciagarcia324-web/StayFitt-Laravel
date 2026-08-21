@extends('layouts.admin')

@section('title', $plan->exists ? 'Editar plan' : 'Nuevo plan')
@section('subtitle', 'Configuración comercial, cupos y módulos')

@section('content')
    <section class="ad-card">
        <form class="ad-form" method="POST" action="{{ $plan->exists ? route('admin.planes.update', $plan) : route('admin.planes.store') }}" enctype="multipart/form-data">
            @csrf
            @if ($plan->exists) @method('PUT') @endif

            @php $selected = old('modulos', $plan->exists ? $plan->modulos->pluck('id_modulo')->all() : []); @endphp
            @include('admin.planes._fields', ['plan' => $plan, 'modulos' => $modulos, 'selected' => $selected, 'readonlyCupos' => $plan->exists])

            <div class="ad-modal-actions">
                <button class="ad-btn" type="submit">Guardar plan</button>
                <a class="ad-btn ad-btn-ghost" href="{{ route('admin.planes.index') }}">Cancelar</a>
            </div>
        </form>
    </section>
@endsection
