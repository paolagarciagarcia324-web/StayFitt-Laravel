<?php

namespace App\Services\Institucional;

use App\Models\Cliente;
use App\Models\ComunicadoInstitucional;
use App\Models\EnlaceRegistroInstitucional;
use App\Models\Institucion;
use App\Models\InvitacionInstitucional;
use App\Models\Plan;
use App\Models\RegistroProgreso;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InstitucionalHubService
{
    public function dashboard(Institucion $institucion): array
    {
        $institucion->loadMissing(['planContratado', 'responsable']);

        $miembros = $this->miembrosBase($institucion);
        $activos = (clone $miembros)->whereIn('estado_cliente', ['ACTIVO', 'ACTIVA'])->count();

        return [
            'kpis' => [
                'vinculados' => (clone $miembros)->count(),
                'activos' => $activos,
                'cupos_totales' => (int) $institucion->cupos_totales,
                'cupos_usados' => (int) $institucion->cupos_usados,
                'consumo_pct' => $institucion->consumoPorcentaje(),
                'invitaciones_pendientes' => $this->invitacionesPendientesCount($institucion),
                'enlaces_activos' => EnlaceRegistroInstitucional::query()
                    ->where('id_institucion', $institucion->id_institucion)
                    ->where('estado', 'ACTIVO')
                    ->where('fecha_expiracion', '>', now())
                    ->count(),
            ],
            'miembros_recientes' => (clone $miembros)
                ->orderByDesc('actualizado_en')
                ->limit(6)
                ->get(),
            'invitaciones_recientes' => InvitacionInstitucional::query()
                ->where('id_institucion', $institucion->id_institucion)
                ->orderByDesc('id_invitacion')
                ->limit(5)
                ->get()
                ->each->marcarExpiradaSiCorresponde(),
            'comunicados_recientes' => ComunicadoInstitucional::query()
                ->where('id_institucion', $institucion->id_institucion)
                ->orderByDesc('id_comunicado')
                ->limit(4)
                ->get(),
            'participacion_mensual' => $this->participacionMensual($institucion),
            'actividad' => $this->actividadReciente($institucion),
        ];
    }

    public function miembrosBase(Institucion $institucion)
    {
        return Cliente::query()
            ->with(['usuario', 'planesCliente.plan'])
            ->where('id_institucion', $institucion->id_institucion)
            ->where('tipo_cliente', 'INSTITUCIONAL');
    }

    public function invitacionesPendientesCount(Institucion $institucion): int
    {
        return InvitacionInstitucional::query()
            ->where('id_institucion', $institucion->id_institucion)
            ->where('estado', 'PENDIENTE')
            ->where('fecha_expiracion', '>', now())
            ->count();
    }

    public function participacionMensual(Institucion $institucion, int $meses = 6): array
    {
        $ids = $this->miembrosBase($institucion)->pluck('id_cliente');
        if ($ids->isEmpty()) {
            return [];
        }

        $desde = Carbon::today()->subMonths($meses - 1)->startOfMonth();
        $rows = RegistroProgreso::query()
            ->selectRaw('DATE_FORMAT(fecha_registro, "%Y-%m") as mes, COUNT(DISTINCT id_cliente) as participantes')
            ->whereIn('id_cliente', $ids)
            ->where('fecha_registro', '>=', $desde)
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $labels = [];
        $values = [];
        for ($i = 0; $i < $meses; $i++) {
            $mes = $desde->copy()->addMonths($i)->format('Y-m');
            $labels[] = $desde->copy()->addMonths($i)->translatedFormat('M y');
            $values[] = (int) ($rows->firstWhere('mes', $mes)?->participantes ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    public function actividadReciente(Institucion $institucion, int $limit = 8): Collection
    {
        $items = collect();
        $ids = $this->miembrosBase($institucion)->pluck('id_cliente');

        if ($ids->isNotEmpty()) {
            RegistroProgreso::query()
                ->with('cliente.usuario')
                ->whereIn('id_cliente', $ids)
                ->orderByDesc('fecha_registro')
                ->limit(4)
                ->get()
                ->each(function ($r) use ($items) {
                    $nombre = trim(($r->cliente?->usuario?->nombres ?? '').' '.($r->cliente?->usuario?->apellidos ?? ''));
                    $items->push([
                        'tipo' => 'progreso',
                        'titulo' => 'Registro de progreso',
                        'detalle' => ($nombre ?: 'Miembro').' · '.$r->fecha_registro?->format('d/m/Y'),
                        'fecha' => $r->fecha_registro,
                    ]);
                });
        }

        InvitacionInstitucional::query()
            ->where('id_institucion', $institucion->id_institucion)
            ->orderByDesc('creado_en')
            ->limit(3)
            ->get()
            ->each(function ($inv) use ($items) {
                $items->push([
                    'tipo' => 'invitacion',
                    'titulo' => 'Invitación '.$inv->etiquetaEstado(),
                    'detalle' => $inv->correo,
                    'fecha' => $inv->creado_en ? Carbon::parse($inv->creado_en) : now(),
                ]);
            });

        ComunicadoInstitucional::query()
            ->where('id_institucion', $institucion->id_institucion)
            ->orderByDesc('creado_en')
            ->limit(3)
            ->get()
            ->each(function ($c) use ($items) {
                $items->push([
                    'tipo' => 'comunicado',
                    'titulo' => $c->titulo,
                    'detalle' => $c->destinatarios.' destinatarios · '.$c->canal,
                    'fecha' => $c->creado_en ? Carbon::parse($c->creado_en) : now(),
                ]);
            });

        return $items->sortByDesc(fn ($i) => $i['fecha']?->timestamp ?? 0)->take($limit)->values();
    }

    public function reporteGeneral(Institucion $institucion): array
    {
        $miembros = $this->miembrosBase($institucion)->get();
        $ids = $miembros->pluck('id_cliente');
        $activos = $miembros->whereIn('estado_cliente', ['ACTIVO', 'ACTIVA'])->count();

        $progresos = $ids->isEmpty() ? 0 : RegistroProgreso::query()->whereIn('id_cliente', $ids)->count();
        $conProgreso = $ids->isEmpty() ? 0 : RegistroProgreso::query()
            ->whereIn('id_cliente', $ids)
            ->distinct('id_cliente')
            ->count('id_cliente');

        $participacion = $activos > 0 ? round(($conProgreso / $activos) * 100, 1) : 0.0;

        return [
            'total_miembros' => $miembros->count(),
            'activos' => $activos,
            'suspendidos' => $miembros->where('estado_cliente', 'SUSPENDIDO')->count(),
            'progresos_registrados' => $progresos,
            'miembros_con_actividad' => $conProgreso,
            'participacion_pct' => $participacion,
            'por_modalidad' => $miembros->groupBy('modalidad')->map->count(),
            'participacion_mensual' => $this->participacionMensual($institucion),
        ];
    }

    public function planesDisponibles(): Collection
    {
        return Plan::query()
            ->where(function ($q) {
                $q->where('tipo_cliente', 'INSTITUCIONAL')
                    ->orWhere('tipo_cliente', 'AMBOS');
            })
            ->where('estado_plan', 'ACTIVO')
            ->orderBy('nombre')
            ->get();
    }

    public function institucionesActivas(): Collection
    {
        return Institucion::query()
            ->where('estado', 'ACTIVA')
            ->orderBy('nombre')
            ->get(['id_institucion', 'nombre', 'nit']);
    }

    public function estadoEnlace(EnlaceRegistroInstitucional $enlace): string
    {
        if ($enlace->estado !== 'ACTIVO' || $enlace->fecha_expiracion->isPast()) {
            return 'EXPIRADA';
        }

        if ((int) $enlace->usos_actuales >= (int) $enlace->cupos_maximos) {
            return 'ACEPTADA';
        }

        return 'PENDIENTE';
    }

    public function actividadMiembro(int $idCliente, int $limit = 6): Collection
    {
        return RegistroProgreso::query()
            ->where('id_cliente', $idCliente)
            ->orderByDesc('fecha_registro')
            ->limit($limit)
            ->get();
    }

    public function beneficiosPlan(?Plan $plan): array
    {
        if (! $plan) {
            return [];
        }

        return array_values(array_filter(array_merge(
            $plan->beneficios ? preg_split('/\r\n|\r|\n/', (string) $plan->beneficios) : [],
            is_array($plan->caracteristicas_json) ? $plan->caracteristicas_json : []
        )));
    }

    public function exportCsvMiembros(Institucion $institucion): string
    {
        $rows = $this->miembrosBase($institucion)->get();
        $lines = ["Nombre,Correo,Estado,Modalidad,Plan,Alta"];

        foreach ($rows as $c) {
            $u = $c->usuario;
            $plan = $c->planesCliente->sortByDesc('id_plan_cliente')->first()?->plan?->nombre ?? '—';
            $lines[] = implode(',', array_map(fn ($v) => '"'.str_replace('"', '""', (string) $v).'"', [
                trim(($u?->nombres ?? '').' '.($u?->apellidos ?? '')),
                $u?->correo ?? $u?->email ?? '',
                $c->estado_cliente,
                $c->modalidad,
                $plan,
                $c->fecha_alta?->format('Y-m-d') ?? '',
            ]));
        }

        return implode("\n", $lines);
    }
}
