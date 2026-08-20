<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->dropViews();

        DB::statement("
            CREATE VIEW vw_cupos_planes AS
            SELECT
              p.id_plan, p.nombre, p.slug, p.modalidad, p.tipo_cliente,
              p.cupo_maximo, p.cupos_totales, p.cupos_reservados, p.cupos_ocupados, p.precio, p.moneda, p.estado_plan,
              CASE
                WHEN p.estado_plan <> 'ACTIVO' THEN 'INACTIVO'
                WHEN COALESCE(p.cupo_maximo, p.cupos_totales, 0) > 0
                     AND (COALESCE(p.cupos_reservados,0) + COALESCE(p.cupos_ocupados,0)) >= COALESCE(p.cupo_maximo, p.cupos_totales)
                     THEN 'CUPO_LLENO'
                ELSE 'DISPONIBLE'
              END AS estado_cupo
            FROM planes p
        ");

        DB::statement("
            CREATE VIEW vw_solicitudes_pendientes AS
            SELECT
              sc.id_solicitud, sc.codigo_solicitud, sc.nombres, sc.apellidos,
              sc.documento_identidad, sc.correo, sc.telefono, sc.modalidad,
              sc.estado_solicitud, pg.id_pago, pg.estado_pago, pg.monto, pg.metodo_pago,
              sc.creado_en
            FROM solicitudes_compra sc
            LEFT JOIN pagos pg ON pg.id_solicitud = sc.id_solicitud
            WHERE sc.estado_solicitud = 'PENDIENTE'
        ");

        DB::statement("
            CREATE VIEW vw_clientes_activos AS
            SELECT
              c.id_cliente, u.nombres, u.apellidos, u.correo, c.modalidad, c.tipo_cliente,
              c.estado_cliente, c.id_institucion, c.id_coach, pc.id_plan_cliente,
              pl.nombre AS plan_nombre, pc.fecha_inicio, pc.fecha_fin, pc.estado_plan_cliente
            FROM clientes c
            INNER JOIN usuarios u ON u.id_user = c.id_user
            LEFT JOIN planes_cliente pc ON pc.id_cliente = c.id_cliente AND pc.estado_plan_cliente IN ('ACTIVO','EN_PERIODO_PRUEBA')
            LEFT JOIN planes pl ON pl.id_plan = pc.id_plan
        ");

        DB::statement("
            CREATE VIEW vw_dashboard_metricas AS
            SELECT
              (SELECT COUNT(*) FROM clientes WHERE estado_cliente IN ('ACTIVA','ACTIVO')) AS total_clientes_activos,
              (SELECT COUNT(*) FROM clientes WHERE modalidad = 'VIRTUAL' AND estado_cliente IN ('ACTIVA','ACTIVO')) AS clientes_virtuales,
              (SELECT COUNT(*) FROM clientes WHERE modalidad = 'PRESENCIAL' AND estado_cliente IN ('ACTIVA','ACTIVO')) AS clientes_presenciales,
              (SELECT COUNT(*) FROM solicitudes_compra WHERE estado_solicitud = 'PENDIENTE') AS solicitudes_pendientes,
              (SELECT COUNT(*) FROM pagos WHERE estado_pago = 'PENDIENTE') AS pagos_pendientes,
              0 AS devoluciones_abiertas,
              (SELECT COUNT(*) FROM coaches WHERE estado_coach = 'ACTIVO') AS coaches_activos,
              (SELECT COUNT(*) FROM ordenes_tienda WHERE estado_orden IN ('PAGADA','PREPARADA','ENVIADA')) AS pedidos_tienda_activos,
              (SELECT COALESCE(SUM(monto),0) FROM pagos WHERE estado_pago = 'VALIDADO') AS ingreso_mes_actual
        ");

        DB::statement("
            CREATE VIEW vw_pagos_por_estado AS
            SELECT p.id_pago, p.tipo_pago, p.metodo_pago, p.pasarela, p.estado_pago, p.monto, p.moneda,
                   p.referencia_pago, p.creado_en, s.estado_solicitud
            FROM pagos p
            LEFT JOIN solicitudes_compra s ON s.id_solicitud = p.id_solicitud
        ");

        DB::statement("
            CREATE VIEW vw_accesos_vigentes AS
            SELECT a.id_acceso, a.id_cliente, m.codigo AS codigo_modulo, m.nombre AS nombre_modulo,
                   a.estado_acceso, a.fecha_inicio, a.fecha_fin, pc.estado_plan_cliente
            FROM acceso_cliente_modulo a
            JOIN modulos_servicio m ON m.id_modulo = a.id_modulo
            JOIN planes_cliente pc ON pc.id_plan_cliente = a.id_plan_cliente
        ");
    }

    public function down(): void
    {
        $this->dropViews();
    }

    private function dropViews(): void
    {
        foreach ([
            'vw_accesos_vigentes',
            'vw_pagos_por_estado',
            'vw_dashboard_metricas',
            'vw_clientes_activos',
            'vw_solicitudes_pendientes',
            'vw_cupos_planes',
        ] as $view) {
            DB::statement("DROP VIEW IF EXISTS {$view}");
        }
    }
};
