<?php

namespace App\View\Composers;

use App\Models\PlanCliente;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminHeaderComposer
{
    public function compose(View $view): void
    {
        $ahora = Carbon::now();
        $mesInicio = $ahora->copy()->startOfMonth();

        $entraron = User::query()
            ->where('ultimo_acceso_en', '>=', $ahora->copy()->subDay())
            ->orderByDesc('ultimo_acceso_en')
            ->limit(8)
            ->get(['id_user', 'nombres', 'apellidos', 'ultimo_acceso_en', 'estado']);

        $activos = User::query()
            ->where(function ($q) {
                $q->whereIn('estado', ['ACTIVO', 'ACTIVA'])
                    ->orWhereIn('estado_cuenta', ['ACTIVO', 'ACTIVA']);
            })
            ->where('ultimo_acceso_en', '>=', $ahora->copy()->subDays(7))
            ->orderByDesc('ultimo_acceso_en')
            ->limit(8)
            ->get(['id_user', 'nombres', 'apellidos', 'ultimo_acceso_en']);

        $inactivos = User::query()
            ->where(function ($q) use ($ahora) {
                $q->whereNull('ultimo_acceso_en')
                    ->orWhere('ultimo_acceso_en', '<', $ahora->copy()->subDays(30));
            })
            ->orderBy('ultimo_acceso_en')
            ->limit(8)
            ->get(['id_user', 'nombres', 'apellidos', 'ultimo_acceso_en']);

        $topProductos = DB::table('orden_detalle as d')
            ->join('ordenes_tienda as o', 'o.id_orden', '=', 'd.id_orden')
            ->join('productos_tienda as p', 'p.id_producto', '=', 'd.id_producto')
            ->where('o.creado_en', '>=', $mesInicio)
            ->groupBy('p.id_producto', 'p.nombre')
            ->orderByRaw('SUM(d.cantidad) DESC')
            ->limit(6)
            ->get([
                'p.nombre',
                DB::raw('SUM(d.cantidad) as unidades'),
            ]);

        $alertas = PlanCliente::query()
            ->with(['cliente.usuario', 'plan'])
            ->where('estado_plan_cliente', 'ACTIVO')
            ->whereBetween('fecha_fin', [$ahora->toDateString(), $ahora->copy()->addDays(14)->toDateString()])
            ->orderBy('fecha_fin')
            ->limit(8)
            ->get();

        $view->with([
            'headerEntraron' => $entraron,
            'headerActivos' => $activos,
            'headerInactivos' => $inactivos,
            'headerTopProductos' => $topProductos,
            'headerAlertas' => $alertas,
        ]);
    }
}
