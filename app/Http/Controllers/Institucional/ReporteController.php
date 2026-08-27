<?php

namespace App\Http\Controllers\Institucional;

use App\Http\Controllers\Controller;
use App\Services\Institucional\InstitucionalHubService;
use App\Support\ResolvesInstitucionContext;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ReporteController extends Controller
{
    use ResolvesInstitucionContext;

    public function __construct(private InstitucionalHubService $hub)
    {
    }

    public function index(): View
    {
        $user = auth()->user();
        $institucion = $this->resolveInstitucionForUser($user);
        $reporte = $this->hub->reporteGeneral($institucion);

        return view('institucional.reportes.index', compact('user', 'institucion', 'reporte'));
    }

    public function export(): Response
    {
        $user = auth()->user();
        $institucion = $this->resolveInstitucionForUser($user);
        $csv = $this->hub->exportCsvMiembros($institucion);
        $filename = 'miembros-'.Str::slug($institucion->nombre).'-'.now()->format('Ymd').'.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
