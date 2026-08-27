<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Services\Coach\CoachHubService;
use App\Support\ResolvesStaffCoach;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use ResolvesStaffCoach;

    public function index(CoachHubService $hub): View
    {
        $coach = $this->resolveCoachOrAbort()->load('usuario');
        $data = $hub->dashboard($coach);

        return view('coach.dashboard', [
            'coach' => $coach,
            'kpis' => $data['kpis'],
            'clientes' => $data['clientes'],
            'sesiones' => $data['sesiones'],
            'progresos' => $data['progresos'],
            'pendientes' => $data['pendientes'],
            'propuestas' => $data['propuestas'],
        ]);
    }
}
