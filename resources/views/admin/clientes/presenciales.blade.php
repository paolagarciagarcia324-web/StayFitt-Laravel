@extends('layouts.admin')

@section('title', 'Clientes presenciales')
@section('subtitle')
    <span class="ad-page-intro">
        <span class="ad-page-intro-lead">Sala Campoalegre</span>
        <span class="ad-page-intro-note">Cupo horario, plan y seguimiento presencial.</span>
    </span>
@endsection

@section('content')
    <div class="ad-kpi-soft">
        <article>
            <i class="is-cyan">@include('partials.admin-icon', ['icon' => 'clientes'])</i>
            <div><strong>{{ $clientes->total() }}</strong><span class="ad-user-mail">Presenciales</span></div>
        </article>
        <article>
            <i class="is-ok">@include('partials.admin-icon', ['icon' => 'horarios'])</i>
            <div><strong>{{ $clientes->filter(fn ($c) => (bool) $c->id_horario)->count() }}</strong><span class="ad-user-mail">Con horario</span></div>
        </article>
        <article>
            <i class="is-pink">@include('partials.admin-icon', ['icon' => 'planes'])</i>
            <div><strong>{{ $clientes->filter(fn ($c) => (bool) $c->planActivo())->count() }}</strong><span class="ad-user-mail">Con plan activo</span></div>
        </article>
        <article>
            <i class="is-warn">@include('partials.admin-icon', ['icon' => 'alerta'])</i>
            <div><strong>{{ $clientes->filter(fn ($c) => ($c->diasRestantesPlan() ?? 1) < 0)->count() }}</strong><span class="ad-user-mail">Plan vencido</span></div>
        </article>
    </div>

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
                    'live' => true,
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
