<?php

namespace App\View\Composers;

use App\Services\Staff\StaffHubService;
use App\Support\ResolvesStaffContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StaffNavComposer
{
    use ResolvesStaffContext;

    public function __construct(private StaffHubService $hub)
    {
    }

    public function compose(View $view): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        try {
            $coach = $this->resolveCoachForStaff($user);
            $clientes = $this->hub->clientesDestacados($coach, 100)->count();
        } catch (\Throwable) {
            $coach = null;
            $clientes = 0;
        }

        $view->with([
            'staffNavUser' => $user,
            'staffNavCoach' => $coach,
            'staffNavCoaches' => $this->hub->coachesActivos(),
            'staffNavClientes' => $clientes,
            'staffNavEspecialidad' => session('staff_especialidad', 'Apoyo operativo'),
        ]);
    }
}
