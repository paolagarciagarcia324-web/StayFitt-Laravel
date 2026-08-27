<?php

namespace App\Services\Comunicacion;

use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificacionService
{
    public function crear(
        User $user,
        string $tipo,
        string $titulo,
        string $mensaje,
        ?string $referenciaTipo = null,
        ?int $referenciaId = null,
        string $canal = 'IN_APP'
    ): Notificacion {
        return Notificacion::create([
            'id_user' => $user->id_user,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'leida' => false,
            'canal' => $canal,
            'referencia_tipo' => $referenciaTipo,
            'referencia_id' => $referenciaId,
        ]);
    }

    public function listarRecientes(User $user, int $limit = 20): Collection
    {
        return Notificacion::query()
            ->where('id_user', $user->id_user)
            ->orderByDesc('creado_en')
            ->limit($limit)
            ->get();
    }

    public function contarNoLeidas(User $user): int
    {
        return Notificacion::query()
            ->where('id_user', $user->id_user)
            ->where('leida', false)
            ->count();
    }

    public function marcarLeida(Notificacion $notificacion, User $user): void
    {
        if ($notificacion->id_user !== $user->id_user) {
            abort(403);
        }

        $notificacion->update(['leida' => true]);
    }

    public function marcarTodasLeidas(User $user): void
    {
        Notificacion::query()
            ->where('id_user', $user->id_user)
            ->where('leida', false)
            ->update(['leida' => true]);
    }
}
