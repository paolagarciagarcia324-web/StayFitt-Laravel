@extends('layouts.admin')

@section('title', 'Reportes')
@section('subtitle', 'Consultar, filtrar y exportar información del negocio')

@section('content')
    <div class="ad-grid-12">
        <div class="ad-col-5">
            <x-admin.panel title="Generar reporte" subtitle="Elige tipo, filtros y formato">
                <form class="ad-form" method="POST" action="{{ route('admin.reportes.export') }}" id="form-reporte">
                    @csrf
                    <details class="ad-form-section" open>
                        <summary>Tipo y formato</summary>
                        <div class="ad-form-section__body">
                            <label><span>Tipo *</span>
                                <select name="tipo" required>
                                    @foreach ($tipos as $key => $label)
                                        <option value="{{ $key }}" @selected(old('tipo') === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label><span>Formato *</span>
                                <select name="formato" id="formato-reporte" required>
                                    <option value="web">Vista previa web</option>
                                    <option value="csv">CSV</option>
                                    <option value="excel">Excel (.xls)</option>
                                    <option value="pdf">PDF (imprimible)</option>
                                </select>
                            </label>
                        </div>
                    </details>

                    <details class="ad-form-section" open>
                        <summary>Periodo</summary>
                        <div class="ad-form-section__body">
                            <div class="ad-form-row">
                                <label><span>Fecha inicio</span><input type="date" name="fecha_inicio" value="{{ old('fecha_inicio', now()->subDays(29)->toDateString()) }}"></label>
                                <label><span>Fecha fin</span><input type="date" name="fecha_fin" value="{{ old('fecha_fin', now()->toDateString()) }}"></label>
                            </div>
                        </div>
                    </details>

                    <details class="ad-form-section">
                        <summary>Filtros opcionales</summary>
                        <div class="ad-form-section__body">
                            <div class="ad-form-row">
                                <label><span>Modalidad</span>
                                    <select name="modalidad">
                                        <option value="">Todas</option>
                                        <option value="VIRTUAL">Virtual</option>
                                        <option value="PRESENCIAL">Presencial</option>
                                    </select>
                                </label>
                                <label><span>Estado</span><input type="text" name="estado" placeholder="VALIDADO / PENDIENTE…" value="{{ old('estado') }}"></label>
                            </div>
                            <div class="ad-form-row">
                                <label><span>Pasarela</span><input type="text" name="pasarela" placeholder="WOMPI / NINGUNA" value="{{ old('pasarela') }}"></label>
                                <label><span>Plan</span>
                                    <select name="id_plan">
                                        <option value="">Todos</option>
                                        @foreach ($planes as $plan)
                                            <option value="{{ $plan->id_plan }}">{{ $plan->nombre }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </div>
                        </div>
                    </details>

                    <div class="ad-form-actions">
                        <button class="ad-btn" type="submit" onclick="document.getElementById('formato-reporte').value='web'">Vista previa</button>
                        <button class="ad-btn ad-btn-ghost" type="submit" onclick="document.getElementById('formato-reporte').value='csv'">CSV</button>
                        <button class="ad-btn ad-btn-ghost" type="submit" onclick="document.getElementById('formato-reporte').value='excel'">Excel</button>
                        <button class="ad-btn ad-btn-ghost" type="submit" onclick="document.getElementById('formato-reporte').value='pdf'">PDF</button>
                    </div>
                </form>
            </x-admin.panel>
        </div>

        <div class="ad-col-7">
            <x-admin.panel title="Historial de reportes" subtitle="Últimas exportaciones generadas" flush>
                <div class="ad-table-wrap">
                    <table class="ad-table ad-users-table">
                        <thead>
                            <tr>
                                <th>Título / Tipo</th>
                                <th>Formato</th>
                                <th>Registros</th>
                                <th>Usuario</th>
                                <th>Fecha</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($historial as $r)
                                <tr>
                                    <td>
                                        <strong>{{ $r->titulo ?: $r->tipo }}</strong>
                                        <em class="ad-user-mail">{{ $r->tipo }} · {{ $r->estado ?? 'GENERADO' }}</em>
                                    </td>
                                    <td>{{ $r->formato }}</td>
                                    <td>{{ $r->registros_count ?? '—' }}</td>
                                    <td>{{ $r->usuario?->nombres }} {{ $r->usuario?->apellidos }}</td>
                                    <td>{{ optional($r->generado_en)->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if ($r->url_archivo)
                                            <a class="ad-link" href="{{ route('admin.reportes.download', $r->id_reporte) }}">Descargar</a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="ad-empty-hero" style="padding:24px">
                                            <span class="ad-empty-orb">@include('partials.admin-icon', ['icon' => 'reportes'])</span>
                                            <strong>Sin reportes generados</strong>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-admin.panel>
        </div>
    </div>
@endsection
