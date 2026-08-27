<?php

namespace App\Services\Analitica;

use App\Models\Chat;
use App\Models\Cliente;
use App\Models\Coach;
use App\Models\DescuentoCupon;
use App\Models\InvitacionWhatsapp;
use App\Models\Mensaje;
use App\Models\Notificacion;
use App\Models\OrdenDetalle;
use App\Models\OrdenTienda;
use App\Models\Pago;
use App\Models\Plan;
use App\Models\PlanCliente;
use App\Models\PropuestaPlan;
use App\Models\SolicitudCompra;
use App\Models\UsoCupon;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class IndicadorService
{
    public function __construct(private SerieTemporalService $series)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function dashboard(FiltroAnalitica $filtro): array
    {
        // No cachear Collections/Modelos: el driver database deja __PHP_Incomplete_Class
        // al deserializar y rompe la vista de estadísticas.
        return [
            'filtro' => $filtro->toArray(),
            'periodo' => $filtro->etiquetaPeriodo(),
            'kpis' => $this->kpis($filtro),
            'series' => [
                'ingresos' => $this->series->ingresosValidados($filtro),
                'mensajes' => $this->series->mensajesEnviados($filtro),
                'planes' => $this->series->planesVendidos($filtro),
                'productos' => $this->series->productosVendidos($filtro),
                'usuarios' => $this->series->usuariosRegistrados($filtro),
            ],
            'distribuciones' => [
                'modalidad' => $this->clientesPorModalidad($filtro),
                'pagos_estado' => $this->pagosPorEstado($filtro),
                'solicitudes_estado' => $this->solicitudesPorEstado($filtro),
                'propuestas_estado' => $this->propuestasPorEstado($filtro),
                'pasarelas' => $this->ingresosPorPasarela($filtro),
                'whatsapp' => $this->invitacionesWhatsapp($filtro),
            ],
            'tablas' => [
                'cupos' => $this->cuposPlanes(),
                'cupones' => $this->topCupones($filtro),
                'chats' => $this->chatsActivos(),
                'tienda' => $this->pedidosTienda($filtro),
            ],
        ];
    }

    /**
     * @return array<string, int|float>
     */
    public function kpis(FiltroAnalitica $filtro): array
    {
        $desde = $filtro->desde;
        $hasta = $filtro->hasta;

        $clientesQ = Cliente::query()->whereIn('estado_cliente', ['ACTIVA', 'ACTIVO']);
        if ($filtro->modalidad) {
            $clientesQ->where('modalidad', $filtro->modalidad);
        }

        $pagosValidados = Pago::query()
            ->where('estado_pago', 'VALIDADO')
            ->whereBetween('creado_en', [$desde, $hasta]);
        if ($filtro->pasarela) {
            $pagosValidados->where('pasarela', $filtro->pasarela);
        }

        return [
            'usuarios_totales' => User::query()->count(),
            'usuarios_activos' => User::query()->whereIn('estado', ['ACTIVO', 'ACTIVA'])->count(),
            'usuarios_periodo' => User::query()->whereBetween('creado_en', [$desde, $hasta])->count(),
            'coaches_activos' => Coach::query()->where('estado_coach', 'ACTIVO')->count(),
            'clientes_activos' => (clone $clientesQ)->count(),
            'clientes_virtuales' => Cliente::query()->where('modalidad', 'VIRTUAL')->whereIn('estado_cliente', ['ACTIVA', 'ACTIVO'])->count(),
            'clientes_presenciales' => Cliente::query()->where('modalidad', 'PRESENCIAL')->whereIn('estado_cliente', ['ACTIVA', 'ACTIVO'])->count(),
            'planes_activos' => Plan::query()->where('estado_plan', 'ACTIVO')->count(),
            'planes_vendidos' => PlanCliente::query()->whereBetween('creado_en', [$desde, $hasta])->count(),
            'ingresos_periodo' => (float) (clone $pagosValidados)->sum('monto'),
            'pagos_validados' => (clone $pagosValidados)->count(),
            'solicitudes_pendientes' => SolicitudCompra::query()->where('estado_solicitud', 'PENDIENTE')->count(),
            'propuestas_pendientes' => PropuestaPlan::query()->where('estado', PropuestaPlan::ESTADO_PENDIENTE_REVISION)->count(),
            'propuestas_periodo' => PropuestaPlan::query()->whereBetween('creado_en', [$desde, $hasta])->count(),
            'productos_vendidos' => (int) OrdenDetalle::query()
                ->whereHas('orden', fn ($q) => $q->whereBetween('creado_en', [$desde, $hasta]))
                ->sum('cantidad'),
            'pedidos_tienda' => OrdenTienda::query()->whereBetween('creado_en', [$desde, $hasta])->count(),
            'cupones_usados' => UsoCupon::query()->whereBetween('usado_en', [$desde, $hasta])->count(),
            'cupones_activos' => DescuentoCupon::query()->where('estado_cupon', 'ACTIVO')->count(),
            'chats_activos' => Chat::query()->where('estado', 'ACTIVO')->count(),
            'mensajes_periodo' => Mensaje::query()->whereBetween('enviado_en', [$desde, $hasta])->count(),
            'invitaciones_wa_pendientes' => InvitacionWhatsapp::query()
                ->whereNull('aceptada_en')
                ->where('cerrada_sin_aceptar', false)
                ->count(),
            'notificaciones_periodo' => Notificacion::query()->whereBetween('creado_en', [$desde, $hasta])->count(),
        ];
    }

    public function clientesPorModalidad(FiltroAnalitica $filtro): Collection
    {
        $q = Cliente::query()
            ->select('modalidad', DB::raw('COUNT(*) as total'))
            ->groupBy('modalidad');

        if ($filtro->modalidad) {
            $q->where('modalidad', $filtro->modalidad);
        }

        return $q->pluck('total', 'modalidad');
    }

    public function pagosPorEstado(FiltroAnalitica $filtro): Collection
    {
        $q = Pago::query()
            ->select('estado_pago', DB::raw('COUNT(*) as total'), DB::raw('COALESCE(SUM(monto),0) as monto'))
            ->whereBetween('creado_en', [$filtro->desde, $filtro->hasta])
            ->groupBy('estado_pago');

        if ($filtro->estado) {
            $q->where('estado_pago', $filtro->estado);
        }
        if ($filtro->pasarela) {
            $q->where('pasarela', $filtro->pasarela);
        }

        return $q->get();
    }

    public function solicitudesPorEstado(FiltroAnalitica $filtro): Collection
    {
        return SolicitudCompra::query()
            ->select('estado_solicitud', DB::raw('COUNT(*) as total'))
            ->whereBetween('creado_en', [$filtro->desde, $filtro->hasta])
            ->when($filtro->estado, fn ($q) => $q->where('estado_solicitud', $filtro->estado))
            ->when($filtro->modalidad, fn ($q) => $q->where('modalidad', $filtro->modalidad))
            ->groupBy('estado_solicitud')
            ->pluck('total', 'estado_solicitud');
    }

    public function propuestasPorEstado(FiltroAnalitica $filtro): Collection
    {
        return PropuestaPlan::query()
            ->select('estado', DB::raw('COUNT(*) as total'))
            ->whereBetween('creado_en', [$filtro->desde, $filtro->hasta])
            ->groupBy('estado')
            ->pluck('total', 'estado');
    }

    public function ingresosPorPasarela(FiltroAnalitica $filtro): Collection
    {
        try {
            return DB::table('vw_ingresos_pasarela')
                ->select('pasarela', DB::raw('SUM(cantidad) as cantidad'), DB::raw('SUM(total) as total'))
                ->where('estado_pago', 'VALIDADO')
                ->whereBetween('fecha', [$filtro->desde->toDateString(), $filtro->hasta->toDateString()])
                ->when($filtro->pasarela, fn ($q) => $q->where('pasarela', $filtro->pasarela))
                ->groupBy('pasarela')
                ->get();
        } catch (\Throwable) {
            return Pago::query()
                ->selectRaw("COALESCE(NULLIF(pasarela,''),'NINGUNA') as pasarela, COUNT(*) as cantidad, COALESCE(SUM(monto),0) as total")
                ->where('estado_pago', 'VALIDADO')
                ->whereBetween('creado_en', [$filtro->desde, $filtro->hasta])
                ->groupBy('pasarela')
                ->get();
        }
    }

    public function invitacionesWhatsapp(FiltroAnalitica $filtro): array
    {
        $base = InvitacionWhatsapp::query()
            ->whereBetween('mostrada_en', [$filtro->desde, $filtro->hasta]);

        return [
            'aceptadas' => (clone $base)->whereNotNull('aceptada_en')->count(),
            'cerradas' => (clone $base)->where('cerrada_sin_aceptar', true)->count(),
            'pendientes' => (clone $base)->whereNull('aceptada_en')->where('cerrada_sin_aceptar', false)->count(),
        ];
    }

    public function cuposPlanes(): Collection
    {
        try {
            return DB::table('vw_cupos_planes')->orderBy('nombre')->get();
        } catch (\Throwable) {
            return Plan::query()
                ->orderBy('orden')
                ->get(['id_plan', 'nombre', 'cupos_totales', 'cupos_ocupados', 'cupos_reservados', 'estado_plan', 'modalidad']);
        }
    }

    public function topCupones(FiltroAnalitica $filtro, int $limit = 8): Collection
    {
        $agregados = UsoCupon::query()
            ->select('id_descuento', DB::raw('COUNT(*) as usos'))
            ->whereBetween('usado_en', [$filtro->desde, $filtro->hasta])
            ->groupBy('id_descuento')
            ->orderByDesc('usos')
            ->limit($limit)
            ->get();

        $cupones = DescuentoCupon::query()
            ->whereIn('id_descuento', $agregados->pluck('id_descuento'))
            ->get()
            ->keyBy('id_descuento');

        return $agregados->map(function ($row) use ($cupones) {
            $row->descuento = $cupones->get($row->id_descuento);

            return $row;
        });
    }

    public function chatsActivos(int $limit = 8): Collection
    {
        return Chat::query()
            ->with(['coach.usuario', 'plan'])
            ->withCount('mensajes')
            ->where('estado', 'ACTIVO')
            ->orderByDesc('creado_en')
            ->limit($limit)
            ->get();
    }

    public function pedidosTienda(FiltroAnalitica $filtro, int $limit = 8): Collection
    {
        return OrdenTienda::query()
            ->with('cliente.usuario')
            ->whereBetween('creado_en', [$filtro->desde, $filtro->hasta])
            ->when($filtro->estado, fn ($q) => $q->where('estado_orden', $filtro->estado))
            ->orderByDesc('creado_en')
            ->limit($limit)
            ->get();
    }
}
