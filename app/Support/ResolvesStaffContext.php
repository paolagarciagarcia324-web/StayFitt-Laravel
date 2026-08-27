<?php

namespace App\Support;

use App\Models\Cliente;
use App\Models\Coach;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait ResolvesStaffContext
{
    protected function selectedCoachId(User $user): int
    {
        if ($user->coach) {
            return (int) $user->coach->id_coach;
        }

        $fromSession = session('staff_coach_id');
        if ($fromSession && Coach::query()->where('id_coach', $fromSession)->exists()) {
            return (int) $fromSession;
        }

        $first = Coach::query()
            ->where('estado_coach', 'ACTIVO')
            ->orderBy('id_coach')
            ->value('id_coach');

        if ($first) {
            session(['staff_coach_id' => $first]);

            return (int) $first;
        }

        abort(422, 'No hay coaches disponibles para operar.');
    }

    protected function resolveCoachForStaff(User $user): Coach
    {
        return Coach::query()
            ->with('usuario')
            ->findOrFail($this->selectedCoachId($user));
    }

    protected function clientesApoyoQuery(Coach $coach): Builder
    {
        return Cliente::query()
            ->where(function ($q) use ($coach) {
                $q->where('id_coach', $coach->id_coach)
                    ->orWhere('id_coach_asignado', $coach->id_coach);
            });
    }

    protected function clienteApoyoOrAbort(Coach $coach, int $idCliente): Cliente
    {
        return $this->clientesApoyoQuery($coach)
            ->where('id_cliente', $idCliente)
            ->firstOrFail();
    }
}
