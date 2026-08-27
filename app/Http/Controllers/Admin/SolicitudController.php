<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SolicitudCompra;
use App\Services\ComprobantePasarelaService;
use App\Services\SolicitudAprobacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SolicitudController extends Controller
{
    public function __construct(
        private SolicitudAprobacionService $service,
        private ComprobantePasarelaService $comprobantes,
    ) {
    }

    public function index(): View
    {
        $pendientes = SolicitudCompra::with(['plan', 'pago'])
            ->where('estado_solicitud', 'PENDIENTE')
            ->orderByDesc('creado_en')
            ->paginate(10, ['*'], 'pendientes')
            ->withQueryString();

        $historial = SolicitudCompra::with('plan')
            ->whereIn('estado_solicitud', ['VALIDADA', 'RECHAZADA', 'CANCELADA'])
            ->orderByDesc('fecha_revision')
            ->paginate(10, ['*'], 'historial')
            ->withQueryString();

        return view('admin.solicitudes.index', compact('pendientes', 'historial'));
    }

    public function show(int $id): View
    {
        $solicitud = SolicitudCompra::with(['plan.modulos', 'pago'])->findOrFail($id);

        if ($solicitud->pago) {
            $pasarela = strtoupper((string) ($solicitud->pago->pasarela ?: 'PASARELA'));
            if (in_array($pasarela, ['', 'NINGUNA', 'PENDIENTE'], true)) {
                $pasarela = 'PASARELA';
            }
            $this->comprobantes->asegurar($solicitud->pago, $solicitud, $pasarela);
            $solicitud->load('pago');
        }

        return view('admin.solicitudes.show', compact('solicitud'));
    }

    public function aprobar(Request $request, int $id): RedirectResponse
    {
        $solicitud = SolicitudCompra::where('estado_solicitud', 'PENDIENTE')->findOrFail($id);
        $tempPassword = $this->service->aprobar($solicitud, $request->input('observacion_admin'));

        $redirect = redirect()
            ->route('admin.solicitudes.index')
            ->with('success', "Solicitud {$solicitud->codigo_solicitud} validada. Cliente creado/activado.");

        if ($tempPassword) {
            $redirect->with('cliente_temp_password', $tempPassword);
        }

        return $redirect;
    }

    public function rechazar(Request $request, int $id): RedirectResponse
    {
        $solicitud = SolicitudCompra::where('estado_solicitud', 'PENDIENTE')->findOrFail($id);
        $this->service->rechazar($solicitud, $request->input('observacion_admin'));

        return redirect()
            ->route('admin.solicitudes.index')
            ->with('success', "Solicitud {$solicitud->codigo_solicitud} rechazada. Acceso bloqueado si existía cliente.");
    }

    public function suspender(Request $request, int $id): RedirectResponse
    {
        $solicitud = SolicitudCompra::query()->findOrFail($id);
        $this->service->suspenderPorFraude($solicitud, $request->input('observacion_admin'));

        return redirect()
            ->route('admin.solicitudes.index')
            ->with('success', "Acceso suspendido por incidencia en {$solicitud->codigo_solicitud}.");
    }
}
