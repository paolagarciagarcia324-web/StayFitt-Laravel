<?php

namespace App\Support;

use App\Models\Cliente;
use App\Models\Institucion;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait ResolvesInstitucionContext
{
    protected function resolveInstitucionForUser(User $user): Institucion
    {
        if ($user->tieneRol('RESPONSABLE_INSTITUCIONAL') && ! $user->tieneRol('ADMIN')) {
            $institucion = Institucion::query()
                ->where('id_user_responsable', $user->id_user)
                ->first();

            if (! $institucion) {
                abort(403, 'No tiene una institución asignada.');
            }

            return $institucion;
        }

        if ($user->tieneRol('ADMIN')) {
            $fromSession = session('institucional_id');
            if ($fromSession) {
                return Institucion::query()->findOrFail($fromSession);
            }

            $first = Institucion::query()->orderBy('id_institucion')->first();
            if (! $first) {
                abort(422, 'No hay instituciones registradas.');
            }

            session(['institucional_id' => $first->id_institucion]);

            return $first;
        }

        abort(403);
    }

    protected function miembrosQuery(Institucion $institucion): Builder
    {
        return Cliente::query()
            ->with(['usuario', 'planesCliente.plan'])
            ->where('id_institucion', $institucion->id_institucion)
            ->where('tipo_cliente', 'INSTITUCIONAL');
    }

    protected function miembroOrAbort(Institucion $institucion, int $idCliente): Cliente
    {
        return $this->miembrosQuery($institucion)
            ->where('id_cliente', $idCliente)
            ->firstOrFail();
    }

    protected function assertInstitucionOwnership(Institucion $institucion, User $user): void
    {
        if ($user->tieneRol('ADMIN')) {
            return;
        }

        if ((int) $institucion->id_user_responsable !== (int) $user->id_user) {
            abort(403);
        }
    }
}
