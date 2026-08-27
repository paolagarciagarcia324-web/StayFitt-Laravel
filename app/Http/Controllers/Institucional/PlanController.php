<?php

namespace App\Http\Controllers\Institucional;

use App\Http\Controllers\Controller;
use App\Support\ResolvesInstitucionContext;
use Illuminate\View\View;

class PlanController extends Controller
{
    use ResolvesInstitucionContext;

    public function index(): View
    {
        $user = auth()->user();
        $institucion = $this->resolveInstitucionForUser($user);
        $institucion->load(['planContratado.modulos']);

        $plan = $institucion->planContratado;
        $beneficios = [];
        if ($plan) {
            $beneficios = array_filter(array_merge(
                $plan->beneficios ? explode("\n", $plan->beneficios) : [],
                is_array($plan->caracteristicas_json) ? $plan->caracteristicas_json : []
            ));
        }

        return view('institucional.plan.index', compact('user', 'institucion', 'plan', 'beneficios'));
    }
}
