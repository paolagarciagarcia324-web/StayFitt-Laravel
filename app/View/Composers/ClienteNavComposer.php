<?php

namespace App\View\Composers;

use App\Services\Comunicacion\NotificacionService;
use App\Services\ModuloAccesoService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ClienteNavComposer
{
    public function __construct(
        private ModuloAccesoService $modulos,
        private NotificacionService $notificaciones,
    ) {
    }

    public function compose(View $view): void
    {
        $user = Auth::user();
        $codigos = [];
        $cliente = $user?->cliente;
        $planNombre = null;
        $diasPlan = null;

        if ($cliente) {
            $codigos = $this->modulos
                ->modulosActivosCliente($cliente)
                ->map(fn ($m) => $m->codigoCanonico())
                ->filter()
                ->values()
                ->all();

            $plan = $cliente->planActivo();
            $plan?->loadMissing('plan');
            $planNombre = $plan?->plan?->nombre;
            $diasPlan = $cliente->diasRestantesPlan();
        }

        $view->with([
            'clienteNavCodigos' => $codigos,
            'clienteNavNoLeidas' => $user ? $this->notificaciones->contarNoLeidas($user) : 0,
            'clienteNavUser' => $user,
            'clienteNavCliente' => $cliente,
            'clienteNavPlanNombre' => $planNombre,
            'clienteNavDiasPlan' => $diasPlan,
        ]);
    }
}
