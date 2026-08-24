<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\NovedadWelcome;
use App\Models\Plan;
use App\Models\PropuestaPlan;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'propuestas_borrador' => PropuestaPlan::query()->whereIn('estado', [
                PropuestaPlan::ESTADO_BORRADOR_ENTRENAMIENTO,
                PropuestaPlan::ESTADO_BORRADOR_NUTRICION,
            ])->count(),
            'propuestas_pendientes' => PropuestaPlan::query()
                ->where('estado', PropuestaPlan::ESTADO_PENDIENTE_REVISION)
                ->count(),
            'novedades_activas' => NovedadWelcome::query()->visibles()->count(),
        ];

        return view('staff.dashboard', compact('stats'));
    }
}
