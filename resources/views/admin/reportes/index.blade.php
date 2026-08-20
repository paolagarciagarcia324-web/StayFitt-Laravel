@extends('layouts.admin')

@section('title', 'Reportes')
@section('subtitle', 'Exportar CSV')

@section('content')
    <section class="ad-card ad-users">
        <div class="ad-pas-grid">
            @foreach (['pagos' => 'Pagos', 'clientes' => 'Clientes', 'cupos' => 'Cupos'] as $tipo => $label)
                <article class="ad-pas-card">
                    <h3>{{ $label }}</h3>
                    <form method="POST" action="{{ route('admin.reportes.export') }}" style="margin-top:12px">
                        @csrf
                        <input type="hidden" name="tipo" value="{{ $tipo }}">
                        <button class="ad-btn" type="submit">Descargar CSV</button>
                    </form>
                </article>
            @endforeach
        </div>
    </section>

    <section class="ad-card ad-users" style="margin-top:16px">
        <div class="ad-card-head"><h2>Historial</h2></div>
        <table class="ad-table ad-users-table">
            <thead>
                <tr><th>Tipo</th><th>Formato</th><th>Estado</th><th>Fecha</th></tr>
            </thead>
            <tbody>
                @forelse ($historial as $r)
                    <tr>
                        <td>{{ $r->tipo }}</td>
                        <td>{{ $r->formato }}</td>
                        <td><span class="ad-badge ad-badge-pendiente">Generado</span></td>
                        <td>{{ optional($r->generado_en)->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <div class="ad-empty-hero" style="padding:24px">
                                <span class="ad-empty-orb">@include('partials.admin-icon', ['icon' => 'reportes'])</span>
                                <strong>Sin reportes</strong>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
