<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminReporteExportRequest;
use App\Models\Plan;
use App\Models\ReporteGenerado;
use App\Services\Analitica\FiltroAnalitica;
use App\Services\Analitica\ReporteExportService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteController extends Controller
{
    public function index(): View
    {
        $historial = ReporteGenerado::query()
            ->with('usuario')
            ->orderByDesc('generado_en')
            ->limit(30)
            ->get();

        $planes = Plan::query()->orderBy('orden')->get(['id_plan', 'nombre']);
        $tipos = ReporteExportService::TIPOS;

        return view('admin.reportes.index', compact('historial', 'planes', 'tipos'));
    }

    public function preview(AdminReporteExportRequest $request, ReporteExportService $export): View
    {
        $data = $request->validated();
        $filtro = FiltroAnalitica::fromArray($data);
        $preview = $export->preview($data['tipo'], $filtro);

        return view('admin.reportes.preview', [
            'preview' => $preview,
            'tipo' => $data['tipo'],
            'filtros' => $filtro->toArray(),
            'tipos' => ReporteExportService::TIPOS,
        ]);
    }

    public function export(AdminReporteExportRequest $request, ReporteExportService $export): StreamedResponse|Response|View
    {
        $data = $request->validated();

        if (($data['formato'] ?? '') === 'web') {
            return $this->preview($request, $export);
        }

        return $export->exportar($data['tipo'], $data['formato'], FiltroAnalitica::fromArray($data));
    }

    public function download(int $id): StreamedResponse|Response
    {
        $reporte = ReporteGenerado::query()->findOrFail($id);

        if (! $reporte->url_archivo || ! Storage::disk('local')->exists($reporte->url_archivo)) {
            abort(404, 'Archivo de reporte no disponible.');
        }

        $path = Storage::disk('local')->path($reporte->url_archivo);
        $name = basename($reporte->url_archivo);

        return response()->download($path, $name);
    }
}
