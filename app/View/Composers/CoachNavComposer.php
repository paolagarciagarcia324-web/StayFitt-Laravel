<?php

namespace App\View\Composers;

use App\Models\Coach;
use App\Services\Coach\CoachHubService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CoachNavComposer
{
    public function __construct(private CoachHubService $hub)
    {
    }

    public function compose(View $view): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $coach = $user->coach;
        if (! $coach && $user->tieneRol('COACH')) {
            $coach = Coach::query()->where('id_user', $user->id_user)->first();
        }

        $view->with([
            'coachNavUser' => $user,
            'coachNavCoach' => $coach,
            'coachNavClientes' => $coach ? $this->hub->clientesActivosCount($coach) : 0,
            'coachNavMensajes' => $coach ? $this->hub->mensajesSinLeer($coach) : 0,
        ]);
    }
}
