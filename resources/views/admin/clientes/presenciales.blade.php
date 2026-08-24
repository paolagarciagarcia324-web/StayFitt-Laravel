@extends('layouts.admin')

@section('title', 'Clientes presenciales')
@section('subtitle')
    <span class="ad-page-intro">
        <span class="ad-page-intro-lead">Sala Campoalegre</span>
        <span class="ad-page-intro-note">Cupo horario, plan y seguimiento presencial.</span>
    </span>
@endsection

@section('content')
    <section class="ad-card ad-users ad-cli-board">
        <div class="ad-cli-board-toolbar">
            <div class="ad-cli-board-toolbar-copy">
                <strong>Directorio</strong>
                <span>Cupo, plan y seguimiento en sala</span>
            </div>
            <div class="ad-cli-board-toolbar-actions">
                @include('partials.admin-search', [
                    'action' => route('admin.clientes.presenciales'),
                    'value' => $q,
                    'placeholder' => 'Buscar por nombre',
                ])
                <button class="ad-btn ad-btn-add" type="button" data-open-user-form>+ Crear nuevo usuario</button>
            </div>
        </div>

        <div class="ad-cli-board-surface">
            @include('partials.admin-client-table', [
                'clientes' => $clientes,
                'variant' => 'presencial',
                'emptyMessage' => 'Aún no hay clientes presenciales. Se crean al aprobar solicitudes o altas desde Admin.',
            ])
        </div>
    </section>

    @include('partials.admin-usuario-create-modal', ['horarios' => $horarios ?? collect(), 'plan' => $plan ?? null])
    @include('partials.admin-cliente-delete')
    @include('partials.admin-cliente-view-modal')
    @include('partials.admin-cliente-edit-modal')
    @include('partials.admin-cliente-edit-ok')
    @include('partials.admin-avatar-zoom')
@endsection
