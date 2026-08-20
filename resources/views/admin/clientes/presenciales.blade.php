@extends('layouts.admin')

@section('title', $title)
@section('subtitle', $subtitle)

@section('content')
    <section class="ad-card ad-users">
        <div class="ad-users-bar">
            <form class="ad-users-search" method="GET" action="{{ route('admin.clientes.presenciales') }}">
                <span>⌕</span>
                <input type="search" name="q" value="{{ $q }}" placeholder="Buscar por nombre">
            </form>
        </div>

        @include('partials.admin-client-table', [
            'clientes' => $clientes,
            'variant' => 'presencial',
            'emptyMessage' => 'Aún no hay clientes presenciales. Se crean al inscribirse en sala.',
        ])
    </section>
@endsection
