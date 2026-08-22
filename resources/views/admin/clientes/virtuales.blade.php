@extends('layouts.admin')

@section('title', $title)
@section('subtitle', $subtitle)

@section('content')
    <section class="ad-card ad-users">
        <div class="ad-users-bar">
            @include('partials.admin-search', [
                'action' => route('admin.clientes.virtuales'),
                'value' => $q,
                'placeholder' => 'Buscar por nombre',
            ])
        </div>

        @include('partials.admin-client-table', [
            'clientes' => $clientes,
            'variant' => 'virtual',
            'emptyMessage' => 'Aún no hay clientes virtuales. Se crean al aprobar solicitudes.',
        ])
    </section>
@endsection
