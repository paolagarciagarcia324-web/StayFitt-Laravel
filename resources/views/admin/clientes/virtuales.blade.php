@extends('layouts.admin')

@section('title', 'Clientes virtuales')
@section('subtitle')
    <span class="ad-page-intro">
        <span class="ad-page-intro-lead">Individual e institucional</span>
        <span class="ad-page-intro-note">Clientes creados al aprobar solicitudes virtuales.</span>
    </span>
@endsection

@section('content')
    <div class="ad-kpi-soft">
        <article>
            <i class="is-cyan">@include('partials.admin-icon', ['icon' => 'clientes'])</i>
            <div><strong>{{ $clientes->total() }}</strong><span class="ad-user-mail">Virtuales</span></div>
        </article>
        <article>
            <i class="is-ok">@include('partials.admin-icon', ['icon' => 'personas'])</i>
            <div><strong>{{ $clientes->where('estado_cliente', 'ACTIVO')->count() + $clientes->where('estado_cliente', 'ACTIVA')->count() }}</strong><span class="ad-user-mail">Activos en esta página</span></div>
        </article>
        <article>
            <i class="is-pink">@include('partials.admin-icon', ['icon' => 'instituciones'])</i>
            <div><strong>{{ $clientes->filter(fn ($c) => (bool) $c->id_institucion)->count() }}</strong><span class="ad-user-mail">Institucionales</span></div>
        </article>
        <article>
            <i class="is-warn">@include('partials.admin-icon', ['icon' => 'coaches'])</i>
            <div><strong>{{ $clientes->filter(fn ($c) => (bool) ($c->id_coach ?: $c->id_coach_asignado))->count() }}</strong><span class="ad-user-mail">Con coach</span></div>
        </article>
    </div>

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
