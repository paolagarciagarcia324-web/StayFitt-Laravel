<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE OR REPLACE VIEW vw_dashboard_metricas AS
SELECT
  (SELECT COUNT(*) FROM clientes WHERE estado_cliente = 'ACTIVA') AS total_clientes_activos,
  (SELECT COUNT(*) FROM clientes WHERE modalidad = 'VIRTUAL' AND estado_cliente = 'ACTIVA') AS clientes_virtuales,
  (SELECT COUNT(*) FROM clientes WHERE modalidad = 'PRESENCIAL' AND estado_cliente = 'ACTIVA') AS clientes_presenciales,
  (SELECT COUNT(*) FROM solicitudes_compra WHERE estado_solicitud = 'PENDIENTE') AS solicitudes_pendientes,
  (SELECT COUNT(*) FROM pagos WHERE estado_pago = 'PENDIENTE') AS pagos_pendientes,
  (SELECT COUNT(*) FROM devoluciones WHERE estado_devolucion IN ('PENDIENTE','EN_PROCESO')) AS devoluciones_abiertas,
  (SELECT COUNT(*) FROM coaches WHERE estado_coach = 'ACTIVO') AS coaches_activos,
  (SELECT COUNT(*) FROM pedidos WHERE estado_pedido IN ('PAGADO','PREPARANDO','ENVIADO')) AS pedidos_tienda_activos,
  (SELECT COALESCE(SUM(monto),0) FROM pagos WHERE estado_pago = 'VALIDADO'
     AND MONTH(fecha_validacion)=MONTH(CURDATE()) AND YEAR(fecha_validacion)=YEAR(CURDATE())) AS ingreso_mes_actual;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP VIEW IF EXISTS `vw_dashboard_metricas`;
SQL);
    }
};
