<?php

namespace App\Http\Controllers\Institucional;

use App\Http\Controllers\Controller;
use App\Services\Institucional\InstitucionalHubService;
use App\Support\ResolvesInstitucionContext;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use ResolvesInstitucionContext;

    public function __construct(private InstitucionalHubService $hub)
    {
    }

    public function index(): View
    {
        $user = auth()->user();
        $institucion = $this->resolveInstitucionForUser($user);
        $data = $this->hub->dashboard($institucion);

        $plan = $institucion->planContratado;
        $beneficios = $this->hub->beneficiosPlan($plan);

        return view('institucional.dashboard', [
            'user' => $user,
            'institucion' => $institucion,
            'kpis' => $data['kpis'],
            'miembros' => $data['miembros_recientes'],
            'invitaciones' => $data['invitaciones_recientes'],
            'comunicados' => $data['comunicados_recientes'],
            'participacion' => $data['participacion_mensual'],
            'actividad' => $data['actividad'],
            'plan' => $plan,
            'beneficios' => array_slice($beneficios, 0, 4),
        ]);
    }
}
