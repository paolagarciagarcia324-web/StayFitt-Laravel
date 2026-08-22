@extends('layouts.admin')

@section('title', $title)
@section('subtitle', $subtitle)

@section('content')
    <section class="ad-card ad-users">
        <div class="ad-users-bar">
            @include('partials.admin-search', [
                'action' => route('admin.clientes.presenciales'),
                'value' => $q,
                'placeholder' => 'Buscar por nombre',
            ])
        </div>

        @include('partials.admin-client-table', [
            'clientes' => $clientes,
            'variant' => 'presencial',
            'emptyMessage' => 'Aún no hay clientes presenciales. Se crean al inscribirse en sala.',
        ])
    </section>
@endsection
