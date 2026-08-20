<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use App\Models\HorarioPresencial;
use App\Models\OrdenTienda;
use App\Models\Pago;
use App\Models\PlanCliente;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $mesInicio = Carbon::now()->startOfMonth();
        $metricas = $this->metricas($mesInicio);

        $planesVendidos = PlanCliente::query()
            ->where('creado_en', '>=', $mesInicio)
            ->count();

        $productosVendidos = (int) DB::table('orden_detalle as d')
            ->join('ordenes_tienda as o', 'o.id_orden', '=', 'd.id_orden')
            ->where('o.creado_en', '>=', $mesInicio)
            ->sum('d.cantidad');

        $ordenes = OrdenTienda::query()
            ->with('cliente.usuario')
            ->orderByDesc('creado_en')
            ->limit(6)
            ->get();

        $proximosEventos = Evento::query()
            ->where('fecha_inicio', '>=', Carbon::now())
            ->orderBy('fecha_inicio')
            ->limit(5)
            ->get();

        $horarios = HorarioPresencial::query()->orderBy('orden')->get();

        $sparks = [
            'clientes' => $this->serieDiaria('clientes', 'creado_en'),
            'ingresos' => $this->serieDiaria('pagos', 'creado_en', "SUM(CASE WHEN estado_pago = 'VALIDADO' THEN monto ELSE 0 END)"),
            'planes' => $this->serieDiaria('planes_cliente', 'creado_en'),
            'productos' => $this->serieProductos(),
        ];

        return view('admin.dashboard', compact(
            'metricas',
            'ordenes',
            'proximosEventos',
            'horarios',
            'sparks',
            'planesVendidos',
            'productosVendidos',
        ));
    }

    private function metricas(Carbon $mesInicio): object
    {
        try {
            $row = DB::table('vw_dashboard_metricas')->first();
            if ($row) {
                $ingresoMes = (float) Pago::query()
                    ->where('estado_pago', 'VALIDADO')
                    ->where('creado_en', '>=', $mesInicio)
                    ->sum('monto');

                $row->ingreso_mes_actual = $ingresoMes;

                return $row;
            }
        } catch (\Throwable) {
            // fallback below
        }

        return (object) [
            'total_clientes_activos' => DB::table('clientes')->whereIn('estado_cliente', ['ACTIVO', 'ACTIVA'])->count(),
            'clientes_virtuales' => DB::table('clientes')->where('modalidad', 'VIRTUAL')->count(),
            'clientes_presenciales' => DB::table('clientes')->where('modalidad', 'PRESENCIAL')->count(),
            'solicitudes_pendientes' => DB::table('solicitudes_compra')->where('estado_solicitud', 'PENDIENTE')->count(),
            'ingreso_mes_actual' => (float) Pago::query()
                ->where('estado_pago', 'VALIDADO')
                ->where('creado_en', '>=', $mesInicio)
                ->sum('monto'),
        ];
    }

    /**
     * @return list<float|int>
     */
    private function serieDiaria(string $tabla, string $columna, string $agregado = 'COUNT(*)'): array
    {
        $desde = Carbon::now()->subDays(6)->startOfDay();

        try {
            $filas = DB::table($tabla)
                ->selectRaw("DATE({$columna}) as dia, {$agregado} as valor")
                ->where($columna, '>=', $desde)
                ->groupBy('dia')
                ->pluck('valor', 'dia');
        } catch (\Throwable) {
            return [2, 3, 2, 5, 4, 6, 5];
        }

        return $this->completarSerie($desde, $filas);
    }

    /**
     * @return list<float|int>
     */
    private function serieProductos(): array
    {
        $desde = Carbon::now()->subDays(6)->startOfDay();

        try {
            $filas = DB::table('orden_detalle as d')
                ->join('ordenes_tienda as o', 'o.id_orden', '=', 'd.id_orden')
                ->selectRaw('DATE(o.creado_en) as dia, SUM(d.cantidad) as valor')
                ->where('o.creado_en', '>=', $desde)
                ->groupBy('dia')
                ->pluck('valor', 'dia');
        } catch (\Throwable) {
            return [1, 2, 2, 4, 3, 5, 4];
        }

        return $this->completarSerie($desde, $filas);
    }

    /**
     * @param  \Illuminate\Support\Collection<string, mixed>  $filas
     * @return list<float|int>
     */
    private function completarSerie(Carbon $desde, $filas): array
    {
        $serie = [];
        for ($i = 0; $i < 7; $i++) {
            $dia = $desde->copy()->addDays($i)->toDateString();
            $serie[] = (float) ($filas[$dia] ?? 0);
        }

        return $serie;
    }
}
