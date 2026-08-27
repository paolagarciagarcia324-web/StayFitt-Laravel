<?php

namespace App\Support;

use App\Models\Cliente;
use App\Models\Coach;
use Illuminate\Database\Eloquent\Builder;

trait ResolvesCoachClientes
{
    protected function clientesDelCoachQuery(Coach $coach): Builder
    {
        return Cliente::query()
            ->where(function ($q) use ($coach) {
                $q->where('id_coach', $coach->id_coach)
                    ->orWhere('id_coach_asignado', $coach->id_coach);
            });
    }

    protected function clienteDelCoachOrAbort(Coach $coach, int $idCliente): Cliente
    {
        return $this->clientesDelCoachQuery($coach)
            ->where('id_cliente', $idCliente)
            ->firstOrFail();
    }
}
