@extends('layouts.admin')

@section('title', 'Vista previa · '.$preview['titulo'])
@section('subtitle', 'Periodo '.$preview['periodo'].' · '.$preview['count'].' registros')

@section('actions')
    <a class="ad-btn ad-btn-ghost" href="{{ route('admin.reportes.index') }}">Volver</a>
@endsection

@section('content')
    <x-admin.panel title="Exportar esta consulta" subtitle="Generado {{ $preview['generado_en'] }} · vista de hasta 50 filas">
        <div class="ad-form-actions">
            @foreach (['csv' => 'CSV', 'excel' => 'Excel', 'pdf' => 'PDF'] as $fmt => $label)
                <form method="POST" action="{{ route('admin.reportes.export') }}">
                    @csrf
                    <input type="hidden" name="tipo" value="{{ $tipo }}">
                    <input type="hidden" name="formato" value="{{ $fmt }}">
                    @foreach ($filtros as $key => $val)
                        <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                    @endforeach
                    <button class="ad-btn {{ $fmt === 'csv' ? '' : 'ad-btn-ghost' }}" type="submit">Exportar {{ $label }}</button>
                </form>
            @endforeach
        </div>
    </x-admin.panel>

    <x-admin.panel :title="$preview['titulo']" subtitle="Resultados filtrados" flush>
        <div class="ad-table-wrap">
            <table class="ad-table ad-users-table">
                <thead>
                    <tr>
                        @foreach ($preview['headers'] as $h)
                            <th>{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($preview['rows'] as $row)
                        <tr>
                            @foreach ($row as $cell)
                                <td>{{ $cell }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($preview['headers']) }}" class="ad-empty">Sin datos para los filtros seleccionados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.panel>
@endsection
