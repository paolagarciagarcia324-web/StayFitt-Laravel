<?php

namespace App\Services\Staff;

use App\Models\Coach;
use App\Models\PlanEntrenamiento;
use App\Models\PropuestaPlan;
use App\Models\RegistroProgreso;
use App\Models\User;
use App\Models\Video;
use Illuminate\Support\Collection;

class StaffHubService
{
    public function dashboard(User $user, Coach $coach): array
    {
        $propuestasBase = PropuestaPlan::query()->where('id_coach', $coach->id_coach);

        if ($user->tieneRol('ASISTENTE_TECNICO') && ! $user->tieneRol('ADMIN')) {
            $propuestasBase->where(function ($q) use ($user) {
                $q->where('creado_por', $user->id_user)
                    ->orWhereIn('estado', [
                        PropuestaPlan::ESTADO_BORRADOR_ENTRENAMIENTO,
                        PropuestaPlan::ESTADO_BORRADOR_NUTRICION,
                        PropuestaPlan::ESTADO_PENDIENTE_REVISION,
                    ]);
            });
        }

        $clientesActivos = \App\Models\Cliente::query()
            ->where(function ($q) use ($coach) {
                $q->where('id_coach', $coach->id_coach)
                    ->orWhere('id_coach_asignado', $coach->id_coach);
            })
            ->whereIn('estado_cliente', ['ACTIVA', 'ACTIVO'])
            ->count();

        return [
            'kpis' => [
                'clientes' => $clientesActivos,
                'borradores' => (clone $propuestasBase)->whereIn('estado', [
                    PropuestaPlan::ESTADO_BORRADOR_ENTRENAMIENTO,
                    PropuestaPlan::ESTADO_BORRADOR_NUTRICION,
                ])->count(),
                'revision' => (clone $propuestasBase)->where('estado', PropuestaPlan::ESTADO_PENDIENTE_REVISION)->count(),
                'videos' => Video::query()->where('estado', 'ACTIVO')->count(),
                'rutinas_borrador' => PlanEntrenamiento::query()
                    ->where('id_coach', $coach->id_coach)
                    ->where('estado', 'BORRADOR')
                    ->count(),
            ],
            'propuestas_recientes' => (clone $propuestasBase)
                ->with('coach.usuario')
                ->orderByDesc('id_propuesta')
                ->limit(5)
                ->get(),
            'clientes' => $this->clientesDestacados($coach, 4),
            'pendientes' => $this->tareasPendientes($user, $coach),
            'novedades_activas' => \App\Models\NovedadWelcome::query()->visibles()->count(),
        ];
    }

    public function clientesDestacados(Coach $coach, int $limit = 8): Collection
    {
        return \App\Models\Cliente::query()
            ->with(['usuario', 'planesCliente.plan'])
            ->where(function ($q) use ($coach) {
                $q->where('id_coach', $coach->id_coach)
                    ->orWhere('id_coach_asignado', $coach->id_coach);
            })
            ->whereIn('estado_cliente', ['ACTIVA', 'ACTIVO'])
            ->orderByDesc('actualizado_en')
            ->limit($limit)
            ->get();
    }

    public function tareasPendientes(User $user, Coach $coach): Collection
    {
        $items = collect();

        PropuestaPlan::query()
            ->where('id_coach', $coach->id_coach)
            ->whereIn('estado', [
                PropuestaPlan::ESTADO_BORRADOR_ENTRENAMIENTO,
                PropuestaPlan::ESTADO_BORRADOR_NUTRICION,
            ])
            ->when($user->tieneRol('ASISTENTE_TECNICO') && ! $user->tieneRol('ADMIN'), fn ($q) => $q->where('creado_por', $user->id_user))
            ->orderByDesc('id_propuesta')
            ->limit(4)
            ->get()
            ->each(fn ($p) => $items->push([
                'tipo' => 'propuesta',
                'titulo' => 'Propuesta en borrador',
                'detalle' => $p->titulo_interno,
                'estado' => $p->etiquetaEstado(),
                'url' => route('coach.propuestas.entrenamiento', $p),
            ]));

        RegistroProgreso::query()
            ->with('cliente.usuario')
            ->where('id_coach', $coach->id_coach)
            ->whereNull('observacion_coach')
            ->orderByDesc('fecha_registro')
            ->limit(3)
            ->get()
            ->each(fn ($r) => $items->push([
                'tipo' => 'progreso',
                'titulo' => 'Progreso sin observación',
                'detalle' => trim(($r->cliente?->usuario?->nombres ?? 'Cliente').' · '.$r->fecha_registro?->format('d/m')),
                'estado' => 'Pendiente',
                'url' => route('staff.clientes.show', $r->id_cliente),
            ]));

        return $items->take(6);
    }

    public function coachesActivos(): Collection
    {
        return Coach::query()
            ->with('usuario')
            ->where('estado_coach', 'ACTIVO')
            ->orderBy('id_coach')
            ->get();
    }
}
