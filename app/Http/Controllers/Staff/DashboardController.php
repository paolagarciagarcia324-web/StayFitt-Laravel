<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Services\Staff\StaffHubService;
use App\Support\ResolvesStaffContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use ResolvesStaffContext;

    public function index(StaffHubService $hub): View
    {
        $user = Auth::user();
        $coach = $this->resolveCoachForStaff($user);
        $data = $hub->dashboard($user, $coach);

        return view('staff.dashboard', [
            'user' => $user,
            'coach' => $coach,
            'coaches' => $hub->coachesActivos(),
            'kpis' => $data['kpis'],
            'propuestas' => $data['propuestas_recientes'],
            'clientes' => $data['clientes'],
            'pendientes' => $data['pendientes'],
            'novedadesActivas' => $data['novedades_activas'],
        ]);
    }
}
