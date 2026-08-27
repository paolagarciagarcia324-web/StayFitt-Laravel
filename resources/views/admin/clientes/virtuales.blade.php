@extends('layouts.admin')

@section('title', 'Clientes virtuales')
@section('subtitle')
    <span class="ad-page-intro">
        <span class="ad-page-intro-lead">Individual e institucional</span>
        <span class="ad-page-intro-note">Clientes creados al aprobar solicitudes virtuales.</span>
    </span>
@endsection

@section('content')
    <section class="ad-card ad-users ad-cli-board">
        <div class="ad-cli-board-toolbar">
            <div class="ad-cli-board-toolbar-copy">
                <strong>Directorio</strong>
                <span>Busca y gestiona fichas sin salir del listado</span>
            </div>
            @include('partials.admin-search', [
                'action' => route('admin.clientes.virtuales'),
                'value' => $q,
                'placeholder' => 'Buscar por nombre',
                'live' => true,
            ])
        </div>

        <div class="ad-cli-board-surface">
            @include('partials.admin-client-table', [
                'clientes' => $clientes,
                'variant' => 'virtual',
                'emptyMessage' => 'Aún no hay clientes virtuales. Se crean al aprobar solicitudes.',
            ])
        </div>
    </section>

    @include('partials.admin-cliente-delete')
    @include('partials.admin-cliente-view-modal')
    @include('partials.admin-cliente-edit-modal')
    @include('partials.admin-cliente-edit-ok')
    @include('partials.admin-avatar-zoom')
@endsection
