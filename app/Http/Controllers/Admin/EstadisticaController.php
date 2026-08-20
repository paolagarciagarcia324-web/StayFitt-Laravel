<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Pago;
use App\Models\Plan;
use App\Models\SolicitudCompra;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class EstadisticaController extends Controller
{
    public function index(): View
    {
        $metricas = null;
        if (Schema::hasTable('vw_dashboard_metricas') || $this->viewExists('vw_dashboard_metricas')) {
            try {
                $metricas = DB::table('vw_dashboard_metricas')->first();
            } catch (\Throwable) {
                $metricas = null;
            }
        }

        $clientesPorModalidad = Cliente::query()
            ->select('modalidad', DB::raw('COUNT(*) as total'))
            ->groupBy('modalidad')
            ->pluck('total', 'modalidad');

        $pagosPorEstado = Pago::query()
            ->select('estado_pago', DB::raw('COUNT(*) as total'), DB::raw('SUM(monto) as monto'))
            ->groupBy('estado_pago')
            ->get();

        $solicitudesPorEstado = SolicitudCompra::query()
            ->select('estado_solicitud', DB::raw('COUNT(*) as total'))
            ->groupBy('estado_solicitud')
            ->pluck('total', 'estado_solicitud');

        $cupos = Plan::query()
            ->orderBy('orden')
            ->get(['nombre', 'cupos_totales', 'cupos_ocupados', 'cupos_reservados', 'estado_plan']);

        return view('admin.estadisticas.index', compact(
            'metricas',
            'clientesPorModalidad',
            'pagosPorEstado',
            'solicitudesPorEstado',
            'cupos'
        ));
    }

    private function viewExists(string $name): bool
    {
        try {
            $db = DB::getDatabaseName();
            $row = DB::selectOne(
                'SELECT COUNT(*) AS c FROM information_schema.views WHERE table_schema = ? AND table_name = ?',
                [$db, $name]
            );

            return ((int) ($row->c ?? 0)) > 0;
        } catch (\Throwable) {
            return false;
        }
    }
}
