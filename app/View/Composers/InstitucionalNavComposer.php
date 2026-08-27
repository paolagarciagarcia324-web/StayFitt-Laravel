<?php

namespace App\View\Composers;

use App\Services\Institucional\InstitucionalHubService;
use App\Support\ResolvesInstitucionContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InstitucionalNavComposer
{
    use ResolvesInstitucionContext;

    public function __construct(private InstitucionalHubService $hub)
    {
    }

    public function compose(View $view): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        try {
            $institucion = $this->resolveInstitucionForUser($user);
            $vinculados = $this->miembrosQuery($institucion)->count();
        } catch (\Throwable) {
            $institucion = null;
            $vinculados = 0;
        }

        $view->with([
            'instNavUser' => $user,
            'instNavInstitucion' => $institucion,
            'instNavVinculados' => $vinculados,
            'instNavInstituciones' => $user->tieneRol('ADMIN') ? $this->hub->institucionesActivas() : collect(),
        ]);
    }
}
