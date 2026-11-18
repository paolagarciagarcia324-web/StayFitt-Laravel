<?php

namespace App\View\Composers;

use App\Models\HorarioPresencial;
use App\Models\OrdenDetalle;
use App\Models\PlanCliente;
use App\Models\PropuestaPlan;
use App\Models\SolicitudCompra;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminHeaderComposer
{
    public function compose(View $view): void
    {
        $ahora = Carbon::now();
        $mesInicio = $ahora->copy()->startOfMonth();

        $entraron = User::query()
            ->where('ultimo_acceso_en', '>=', $ahora->copy()->subDay())
            ->orderByDesc('ultimo_acceso_en')
            ->limit(8)
            ->get(['id_user', 'nombres', 'apellidos', 'ultimo_acceso_en', 'estado']);

        $activos = User::query()
            ->where(function ($q) {
                $q->whereIn('estado', ['ACTIVO', 'ACTIVA'])
                    ->orWhereIn('estado_cuenta', ['ACTIVO', 'ACTIVA']);
            })
            ->where('ultimo_acceso_en', '>=', $ahora->copy()->subDays(7))
            ->orderByDesc('ultimo_acceso_en')
            ->limit(8)
            ->get(['id_user', 'nombres', 'apellidos', 'ultimo_acceso_en']);

        $inactivos = User::query()
            ->where(function ($q) use ($ahora) {
                $q->whereNull('ultimo_acceso_en')
                    ->orWhere('ultimo_acceso_en', '<', $ahora->copy()->subDays(30));
            })
            ->orderBy('ultimo_acceso_en')
            ->limit(8)
            ->get(['id_user', 'nombres', 'apellidos', 'ultimo_acceso_en']);

        $topProductos = OrdenDetalle::query()
            ->join('ordenes_tienda as o', 'o.id_orden', '=', 'orden_detalle.id_orden')
            ->join('productos_tienda as p', 'p.id_producto', '=', 'orden_detalle.id_producto')
            ->where('o.creado_en', '>=', $mesInicio)
            ->groupBy('p.id_producto', 'p.nombre')
            ->orderByRaw('SUM(orden_detalle.cantidad) DESC')
            ->limit(6)
            ->get([
                'p.nombre',
                DB::raw('SUM(orden_detalle.cantidad) as unidades'),
            ]);

        $alertas = PlanCliente::query()
            ->with(['cliente.usuario', 'plan'])
            ->where('estado_plan_cliente', 'ACTIVO')
            ->whereBetween('fecha_fin', [$ahora->toDateString(), $ahora->copy()->addDays(14)->toDateString()])
            ->orderBy('fecha_fin')
            ->limit(8)
            ->get();

        $navSolicitudesPendientes = SolicitudCompra::query()
            ->where('estado_solicitud', 'PENDIENTE')
            ->count();

        $latestPendiente = SolicitudCompra::query()
            ->where('estado_solicitud', 'PENDIENTE')
            ->max('creado_en');

        $navSolicitudesLatestAt = $latestPendiente
            ? Carbon::parse($latestPendiente)->toIso8601String()
            : null;

        $navPropuestasPendientes = (int) PropuestaPlan::query()
            ->where('estado', PropuestaPlan::ESTADO_PENDIENTE_REVISION)
            ->count();

        $navOcupacionPct = 0;
        try {
            $cupoOcupado = (int) HorarioPresencial::query()->sum('cupo_ocupado');
            $cupoMaximo = max(1, (int) HorarioPresencial::query()->sum('cupo_maximo'));
            $navOcupacionPct = min(100, (int) round(($cupoOcupado / $cupoMaximo) * 100));
        } catch (\Throwable) {
            $navOcupacionPct = 0;
        }

        $navPlanesPorVencer = (int) PlanCliente::query()
            ->where('estado_plan_cliente', 'ACTIVO')
            ->whereBetween('fecha_fin', [$ahora->toDateString(), $ahora->copy()->addDays(14)->toDateString()])
            ->count();

        $modules = $this->modules($navSolicitudesPendientes, $navPropuestasPendientes);
        $currentTabs = collect($modules)->firstWhere('active')['tabs'] ?? [];

        $view->with([
            'headerEntraron' => $entraron,
            'headerActivos' => $activos,
            'headerInactivos' => $inactivos,
            'headerTopProductos' => $topProductos,
            'headerAlertas' => $alertas,
            'navSolicitudesPendientes' => $navSolicitudesPendientes,
            'navSolicitudesLatestAt' => $navSolicitudesLatestAt,
            'navOcupacionPct' => $navOcupacionPct,
            'navPlanesPorVencer' => $navPlanesPorVencer,
            'modules' => $modules,
            'currentTabs' => $currentTabs,
        ]);
    }

    private function modules(int $pend, int $navPropuestasPendientes): array
    {
        return [
            'dashboard' => [
                'label' => 'Dashboard',
                'icon' => 'dashboard',
                'tone' => 'pink',
                'href' => route('admin.dashboard'),
                'active' => request()->routeIs('admin.dashboard'),
                'tabs' => [],
            ],
            'operacion' => [
                'label' => 'Operación',
                'icon' => 'pagos',
                'tone' => 'cyan',
                'href' => route('admin.solicitudes.index'),
                'active' => request()->routeIs('admin.solicitudes.*', 'admin.pagos.*', 'admin.horarios.*', 'admin.eventos.*', 'admin.calendario.*'),
                'badge' => $pend,
                'tabs' => [
                    ['label' => 'Solicitudes', 'href' => route('admin.solicitudes.index'), 'active' => request()->routeIs('admin.solicitudes.*'), 'badge' => $pend],
                    ['label' => 'Pagos', 'href' => route('admin.pagos.index'), 'active' => request()->routeIs('admin.pagos.*')],
                    ['label' => 'Horarios', 'href' => route('admin.horarios.index'), 'active' => request()->routeIs('admin.horarios.*')],
                    ['label' => 'Eventos', 'href' => route('admin.eventos.index'), 'active' => request()->routeIs('admin.eventos.*')],
                    ['label' => 'Calendario', 'href' => route('admin.calendario.index'), 'active' => request()->routeIs('admin.calendario.*')],
                ],
            ],
            'personas' => [
                'label' => 'Personas',
                'icon' => 'personas',
                'tone' => 'pink',
                'href' => route('admin.usuarios.index'),
                'active' => request()->routeIs('admin.usuarios.*', 'admin.clientes.*', 'admin.instituciones.*', 'admin.coaches.*', 'admin.asistentes.*', 'admin.roles.*'),
                'tabs' => [
                    ['label' => 'Usuarios', 'href' => route('admin.usuarios.index'), 'active' => request()->routeIs('admin.usuarios.*')],
                    ['label' => 'Virtuales', 'href' => route('admin.clientes.virtuales'), 'active' => request()->routeIs('admin.clientes.virtuales')],
                    ['label' => 'Presenciales', 'href' => route('admin.clientes.presenciales'), 'active' => request()->routeIs('admin.clientes.presenciales')],
                    ['label' => 'Instituciones', 'href' => route('admin.instituciones.index'), 'active' => request()->routeIs('admin.instituciones.*')],
                    ['label' => 'Coaches', 'href' => route('admin.coaches.index'), 'active' => request()->routeIs('admin.coaches.*')],
                    ['label' => 'Asistentes', 'href' => route('admin.asistentes.index'), 'active' => request()->routeIs('admin.asistentes.*')],
                    ['label' => 'Roles', 'href' => route('admin.roles.index'), 'active' => request()->routeIs('admin.roles.*')],
                ],
            ],
            'comercial' => [
                'label' => 'Comercial',
                'icon' => 'planes',
                'tone' => 'cyan',
                'href' => route('admin.planes.index'),
                'active' => request()->routeIs('admin.planes.*', 'admin.propuestas.*', 'admin.cupones.*', 'admin.tienda.*', 'admin.novedades.*'),
                'badge' => $navPropuestasPendientes,
                'tabs' => [
                    ['label' => 'Planes', 'href' => route('admin.planes.index'), 'active' => request()->routeIs('admin.planes.*')],
                    ['label' => 'Propuestas', 'href' => route('admin.propuestas.index'), 'active' => request()->routeIs('admin.propuestas.*'), 'badge' => $navPropuestasPendientes],
                    ['label' => 'Novedades', 'href' => route('admin.novedades.index'), 'active' => request()->routeIs('admin.novedades.*')],
                    ['label' => 'Cupones', 'href' => route('admin.cupones.index'), 'active' => request()->routeIs('admin.cupones.*')],
                    ['label' => 'Tienda', 'href' => route('admin.tienda.index'), 'active' => request()->routeIs('admin.tienda.*')],
                ],
            ],
            'comunicacion' => [
                'label' => 'Comunicación',
                'icon' => 'comunidad',
                'tone' => 'pink',
                'href' => route('admin.whatsapp.index'),
                'active' => request()->routeIs('admin.whatsapp.*', 'admin.comunidad.*'),
                'tabs' => [
                    ['label' => 'Chats', 'href' => route('admin.whatsapp.index'), 'active' => request()->routeIs('admin.whatsapp.*')],
                    ['label' => 'Comunidad', 'href' => route('admin.comunidad.index'), 'active' => request()->routeIs('admin.comunidad.*')],
                ],
            ],
            'analisis' => [
                'label' => 'Análisis',
                'icon' => 'estadisticas',
                'tone' => 'cyan',
                'href' => route('admin.estadisticas.index'),
                'active' => request()->routeIs('admin.analisis.*', 'admin.estadisticas.*', 'admin.reportes.*'),
                'tabs' => [
                    ['label' => 'Estadísticas', 'href' => route('admin.estadisticas.index'), 'active' => request()->routeIs('admin.estadisticas.*', 'admin.analisis.*')],
                    ['label' => 'Reportes', 'href' => route('admin.reportes.index'), 'active' => request()->routeIs('admin.reportes.*')],
                ],
            ],
            'configuracion' => [
                'label' => 'Configuración',
                'icon' => 'configuracion',
                'tone' => 'pink',
                'href' => route('admin.configuracion.index'),
                'active' => request()->routeIs('admin.configuracion.*', 'admin.pasarelas.*'),
                'tabs' => [
                    ['label' => 'Sistema', 'href' => route('admin.configuracion.index'), 'active' => request()->routeIs('admin.configuracion.*')],
                    ['label' => 'Pasarelas', 'href' => route('admin.pasarelas.index'), 'active' => request()->routeIs('admin.pasarelas.*')],
                ],
            ],
        ];
    }
}
