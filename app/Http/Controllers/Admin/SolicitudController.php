<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SolicitudCompra;
use App\Services\SolicitudAprobacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SolicitudController extends Controller
{
    public function __construct(private SolicitudAprobacionService $service)
    {
    }

    public function index(): View
    {
        $pendientes = DB::table('vw_solicitudes_pendientes')->orderByDesc('creado_en')->get();
        $historial = SolicitudCompra::with('plan')
            ->whereIn('estado_solicitud', ['VALIDADA', 'RECHAZADA', 'CANCELADA'])
            ->orderByDesc('fecha_revision')
            ->limit(30)
            ->get();

        return view('admin.solicitudes.index', compact('pendientes', 'historial'));
    }

    public function show(int $id): View
    {
        $solicitud = SolicitudCompra::with(['plan.modulos', 'pago'])->findOrFail($id);

        return view('admin.solicitudes.show', compact('solicitud'));
    }

    public function aprobar(Request $request, int $id): RedirectResponse
    {
        $solicitud = SolicitudCompra::where('estado_solicitud', 'PENDIENTE')->findOrFail($id);
        $this->service->aprobar($solicitud, $request->input('observacion_admin'));

        return redirect()
            ->route('admin.solicitudes.index')
            ->with('success', "Solicitud {$solicitud->codigo_solicitud} validada. Cliente creado/activado.");
    }

    public function rechazar(Request $request, int $id): RedirectResponse
    {
        $solicitud = SolicitudCompra::where('estado_solicitud', 'PENDIENTE')->findOrFail($id);
        $this->service->rechazar($solicitud, $request->input('observacion_admin'));

        return redirect()
            ->route('admin.solicitudes.index')
            ->with('success', "Solicitud {$solicitud->codigo_solicitud} rechazada. Cupo liberado.");
    }
}
