<?php

namespace App\Support;

use App\Models\Coach;
use App\Models\User;
use Illuminate\Support\Str;

trait ResolvesStaffCoach
{
    protected function resolveCoachIdForUser(User $user, ?int $selectedCoachId = null): int
    {
        if ($user->tieneRol('ASISTENTE_TECNICO') || $user->tieneRol('ADMIN')) {
            if ($selectedCoachId) {
                $exists = Coach::query()->where('id_coach', $selectedCoachId)->exists();
                if ($exists) {
                    return $selectedCoachId;
                }
            }
        }

        $coach = $user->coach;
        if ($coach) {
            return (int) $coach->id_coach;
        }

        if ($user->tieneRol('COACH')) {
            $coach = Coach::create([
                'id_user' => $user->id_user,
                'codigo_coach' => 'C-'.Str::upper(Str::random(6)),
                'especialidad' => 'General',
                'estado_coach' => 'ACTIVO',
            ]);

            return (int) $coach->id_coach;
        }

        if ($selectedCoachId) {
            return $selectedCoachId;
        }

        abort(422, 'Debes seleccionar un coach para la propuesta.');
    }

    protected function resolveCoachOrAbort(): Coach
    {
        $user = auth()->user();
        if (! $user) {
            abort(403);
        }

        if ($user->coach) {
            return $user->coach;
        }

        if ($user->tieneRol('COACH')) {
            $coachId = $this->resolveCoachIdForUser($user);

            return Coach::query()->findOrFail($coachId);
        }

        abort(403, 'Debes tener perfil de coach para acceder.');
    }

    protected function userCanAccessPropuesta(User $user, \App\Models\PropuestaPlan $propuesta): bool
    {
        if ($user->tieneRol('ADMIN') || $user->tieneRol('ASISTENTE_TECNICO')) {
            return true;
        }

        if ($user->tieneRol('COACH')) {
            $coachId = $user->coach?->id_coach;

            return $coachId && (int) $propuesta->id_coach === (int) $coachId;
        }

        return false;
    }
}
