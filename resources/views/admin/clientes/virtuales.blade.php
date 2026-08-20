@extends('layouts.admin')

@section('title', $title)
@section('subtitle', $subtitle)

@section('content')
    <section class="ad-card ad-users">
        <div class="ad-users-bar">
            <form class="ad-users-search" method="GET" action="{{ route('admin.clientes.virtuales') }}">
                <span>⌕</span>
                <input type="search" name="q" value="{{ $q }}" placeholder="Buscar por nombre">
            </form>
        </div>

        @include('partials.admin-client-table', [
            'clientes' => $clientes,
            'variant' => 'virtual',
            'emptyMessage' => 'Aún no hay clientes virtuales. Se crean al aprobar solicitudes.',
        ])
    </section>
@endsection
