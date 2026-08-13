<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $metricas = DB::table('vw_dashboard_metricas')->first();
        $solicitudes = DB::table('vw_solicitudes_pendientes')
            ->orderByDesc('creado_en')
            ->limit(6)
            ->get();

        return view('admin.dashboard', compact('metricas', 'solicitudes'));
    }
}
