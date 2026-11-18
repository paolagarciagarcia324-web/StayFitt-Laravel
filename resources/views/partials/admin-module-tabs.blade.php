@php
    $pend = (int) ($navSolicitudesPendientes ?? 0);
    $navPropuestasPendientes = (int) (\App\Models\PropuestaPlan::query()
        ->where('estado', \App\Models\PropuestaPlan::ESTADO_PENDIENTE_REVISION)
        ->count());

    $modules = [
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

    $currentTabs = collect($modules)->firstWhere('active')['tabs'] ?? [];
    $onSolicitudes = request()->routeIs('admin.solicitudes.*');
@endphp
