<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reportes_generados', function (Blueprint $table) {
            if (! Schema::hasColumn('reportes_generados', 'estado')) {
                $table->string('estado', 20)->default('GENERADO')->after('formato');
            }
            if (! Schema::hasColumn('reportes_generados', 'registros_count')) {
                $table->unsignedInteger('registros_count')->nullable()->after('estado');
            }
            if (! Schema::hasColumn('reportes_generados', 'error_mensaje')) {
                $table->string('error_mensaje', 500)->nullable()->after('registros_count');
            }
            if (! Schema::hasColumn('reportes_generados', 'titulo')) {
                $table->string('titulo', 150)->nullable()->after('tipo');
            }
        });

        DB::statement('DROP VIEW IF EXISTS vw_ingresos_pasarela');
        DB::statement('
            CREATE VIEW vw_ingresos_pasarela AS
            SELECT
              base.pasarela,
              base.metodo,
              base.estado_pago,
              base.fecha,
              COUNT(*) AS cantidad,
              COALESCE(SUM(base.monto), 0) AS total
            FROM (
              SELECT
                COALESCE(NULLIF(p.pasarela, \'\'), \'NINGUNA\') AS pasarela,
                COALESCE(NULLIF(p.metodo_pago, \'\'), NULLIF(p.metodo, \'\'), \'OTRO\') AS metodo,
                p.estado_pago AS estado_pago,
                DATE(COALESCE(p.fecha_pago, p.creado_en)) AS fecha,
                p.monto AS monto
              FROM pagos p
            ) AS base
            GROUP BY base.pasarela, base.metodo, base.estado_pago, base.fecha
        ');

        DB::statement('DROP VIEW IF EXISTS vw_progreso_resumen');
        DB::statement('
            CREATE VIEW vw_progreso_resumen AS
            SELECT
              r.id_cliente,
              r.id_plan_cliente,
              MIN(r.fecha_registro) AS primera_fecha,
              MAX(r.fecha_registro) AS ultima_fecha,
              COUNT(*) AS total_registros,
              MIN(r.peso_kg) AS peso_min,
              MAX(r.peso_kg) AS peso_max
            FROM registros_progreso r
            GROUP BY r.id_cliente, r.id_plan_cliente
        ');

        DB::statement('DROP VIEW IF EXISTS vw_asistencia_racha');
        DB::statement('
            CREATE VIEW vw_asistencia_racha AS
            SELECT
              c.id_cliente,
              u.nombres,
              u.apellidos,
              rc.tipo,
              rc.dias_consecutivos,
              rc.mejor_racha,
              rc.ultima_fecha
            FROM rachas_cliente rc
            JOIN clientes c ON c.id_cliente = rc.id_cliente
            JOIN usuarios u ON u.id_user = c.id_user
        ');

        // Corregir semántica: ingreso histórico vs. etiqueta engañosa
        DB::statement('DROP VIEW IF EXISTS vw_dashboard_metricas');
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
              (SELECT COALESCE(SUM(monto),0) FROM pagos WHERE estado_pago = 'VALIDADO') AS ingreso_historico_validado,
              (SELECT COUNT(*) FROM usuarios WHERE estado IN ('ACTIVO','ACTIVA')) AS usuarios_activos,
              (SELECT COUNT(*) FROM chats WHERE estado = 'ACTIVO') AS chats_activos,
              (SELECT COUNT(*) FROM mensajes) AS mensajes_totales
        ");
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS vw_asistencia_racha');
        DB::statement('DROP VIEW IF EXISTS vw_progreso_resumen');
        DB::statement('DROP VIEW IF EXISTS vw_ingresos_pasarela');

        DB::statement('DROP VIEW IF EXISTS vw_dashboard_metricas');
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

        Schema::table('reportes_generados', function (Blueprint $table) {
            foreach (['titulo', 'error_mensaje', 'registros_count', 'estado'] as $col) {
                if (Schema::hasColumn('reportes_generados', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
