@extends('layouts.admin')

@section('title', 'Asistentes técnicos')
@section('subtitle')
    <span class="ad-page-intro">
        <span class="ad-page-intro-lead">Apoyo operativo</span>
        <span class="ad-page-intro-note">Registro de usuarios con rol Asistente Técnico de Coach.</span>
    </span>
@endsection

@section('content')
    <div class="ad-kpis" style="margin-bottom:16px">
        <div class="ad-kpi accent-cyan">
            <div class="ad-kpi-copy">
                <strong>{{ $total }}</strong>
                <span>Asistentes registrados</span>
            </div>
        </div>
    </div>

    <section class="ad-card ad-users ad-cli-board">
        <div class="ad-cli-board-toolbar">
            <div class="ad-cli-board-toolbar-copy">
                <strong>Directorio</strong>
                <span>{{ $asistentes->total() }} en este filtro</span>
            </div>
            @include('partials.admin-search', [
                'action' => route('admin.asistentes.index'),
                'value' => $q,
                'placeholder' => 'Buscar por nombre, correo o documento',
            ])
        </div>

        <div class="ad-cli-board-surface">
            <table class="ad-table ad-users-table ad-cli-table">
                <thead>
                    <tr>
                        <th class="ad-cli-col-num">#</th>
                        <th class="ad-cli-col-perfil">Perfil</th>
                        <th class="ad-cli-col-cliente">Asistente</th>
                        <th>Documento</th>
                        <th>Estado</th>
                        <th>Alta</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($asistentes as $index => $u)
                        @php
                            $nombre = trim($u->nombres.' '.$u->apellidos) ?: 'Usuario';
                            $estado = $u->estado ?: $u->estado_cuenta ?: '—';
                        @endphp
                        <tr>
                            <td class="ad-cli-col-num">{{ $asistentes->firstItem() + $index }}</td>
                            <td class="ad-cli-col-perfil">
                                @include('partials.admin-avatar', ['user' => $u, 'zoomable' => true])
                            </td>
                            <td class="ad-cli-col-cliente">
                                <strong>{{ $nombre }}</strong>
                                <div class="ad-cli-text">{{ $u->correo ?: '—' }}</div>
                            </td>
                            <td>{{ $u->documento_identidad ?: '—' }}</td>
                            <td>
                                <span class="ad-badge {{ in_array($estado, ['ACTIVO', 'ACTIVA'], true) ? 'ad-badge-ok' : 'ad-badge-pendiente' }}">
                                    {{ $estado }}
                                </span>
                            </td>
                            <td>{{ optional($u->creado_en)->format('d/m/Y') ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="ad-empty">
                                Aún no hay asistentes. Asigna el rol «Asistente Técnico de Coach» desde Usuarios.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if (method_exists($asistentes, 'links'))
                <div class="ad-pagination">{{ $asistentes->links() }}</div>
            @endif
        </div>
    </section>

    @include('partials.admin-avatar-zoom')
@endsection
