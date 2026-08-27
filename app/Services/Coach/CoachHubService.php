<?php

namespace App\Services\Coach;

use App\Models\Chat;
use App\Models\Coach;
use App\Models\Mensaje;
use App\Models\PlanEntrenamiento;
use App\Models\PropuestaPlan;
use App\Models\RegistroProgreso;
use App\Models\Rutina;
use App\Models\Sesion;
use Illuminate\Support\Collection;

class CoachHubService
{
    public function dashboard(Coach $coach): array
    {
        $clientesActivos = $this->clientesActivosCount($coach);
        $sesionesProximas = $this->proximasSesiones($coach, 5);
        $ultimosProgresos = $this->ultimosProgresos($coach, 6);
        $mensajesPendientes = $this->mensajesSinLeer($coach);
        $rutinasActivas = Rutina::query()
            ->whereHas('planEntrenamiento', fn ($q) => $q->where('id_coach', $coach->id_coach))
            ->where('estado', 'ACTIVA')
            ->count();

        $propuestasBase = PropuestaPlan::query()->where('id_coach', $coach->id_coach);
        $propuestas = [
            'borradores' => (clone $propuestasBase)->whereIn('estado', [
                PropuestaPlan::ESTADO_BORRADOR_ENTRENAMIENTO,
                PropuestaPlan::ESTADO_BORRADOR_NUTRICION,
            ])->count(),
            'pendientes' => (clone $propuestasBase)->where('estado', PropuestaPlan::ESTADO_PENDIENTE_REVISION)->count(),
            'publicados' => (clone $propuestasBase)->where('estado', PropuestaPlan::ESTADO_PUBLICADO)->count(),
        ];

        return [
            'kpis' => [
                'clientes' => $clientesActivos,
                'sesiones' => $sesionesProximas->count(),
                'mensajes' => $mensajesPendientes,
                'rutinas' => $rutinasActivas,
                'progresos_mes' => RegistroProgreso::query()
                    ->where('id_coach', $coach->id_coach)
                    ->where('fecha_registro', '>=', now()->startOfMonth())
                    ->count(),
            ],
            'clientes' => $this->clientesDestacados($coach, 4),
            'sesiones' => $sesionesProximas,
            'progresos' => $ultimosProgresos,
            'pendientes' => $this->actividadesPendientes($coach),
            'propuestas' => $propuestas,
        ];
    }

    public function clientesActivosCount(Coach $coach): int
    {
        return \App\Models\Cliente::query()
            ->where(function ($q) use ($coach) {
                $q->where('id_coach', $coach->id_coach)
                    ->orWhere('id_coach_asignado', $coach->id_coach);
            })
            ->whereIn('estado_cliente', ['ACTIVA', 'ACTIVO'])
            ->count();
    }

    public function proximasSesiones(Coach $coach, int $limit = 8): Collection
    {
        return Sesion::query()
            ->where('id_coach', $coach->id_coach)
            ->where('fecha', '>=', now()->toDateString())
            ->whereIn('estado', ['PROGRAMADA', 'CONFIRMADA', 'ACTIVA'])
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->limit($limit)
            ->get();
    }

    public function ultimosProgresos(Coach $coach, int $limit = 8): Collection
    {
        return RegistroProgreso::query()
            ->with(['cliente.usuario'])
            ->where('id_coach', $coach->id_coach)
            ->orderByDesc('fecha_registro')
            ->limit($limit)
            ->get();
    }

    public function mensajesSinLeer(Coach $coach): int
    {
        $userId = $coach->usuario?->id_user;
        if (! $userId) {
            return 0;
        }

        $chatIds = Chat::query()
            ->where('id_coach', $coach->id_coach)
            ->where('estado', 'ACTIVO')
            ->pluck('id_chat');

        if ($chatIds->isEmpty()) {
            return 0;
        }

        return Mensaje::query()
            ->whereIn('id_chat', $chatIds)
            ->where('id_user_emisor', '!=', $userId)
            ->where('leido', false)
            ->count();
    }

    public function clientesDestacados(Coach $coach, int $limit = 6): Collection
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

    public function actividadesPendientes(Coach $coach): Collection
    {
        $items = collect();

        PropuestaPlan::query()
            ->where('id_coach', $coach->id_coach)
            ->where('estado', PropuestaPlan::ESTADO_PENDIENTE_REVISION)
            ->orderByDesc('id_propuesta')
            ->limit(3)
            ->get()
            ->each(fn ($p) => $items->push([
                'tipo' => 'propuesta',
                'titulo' => 'Propuesta pendiente de revisión',
                'detalle' => $p->nombre ?? 'Plan #'.$p->id_propuesta,
                'url' => route('coach.propuestas.resumen', $p),
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
                'url' => route('coach.reportes.progreso', ['id_cliente' => $r->id_cliente]),
            ]));

        return $items->take(5);
    }

    public function planesDelCoach(Coach $coach): Collection
    {
        return PlanEntrenamiento::query()
            ->with(['cliente.usuario', 'rutinas'])
            ->where('id_coach', $coach->id_coach)
            ->orderByDesc('creado_en')
            ->get();
    }
}
