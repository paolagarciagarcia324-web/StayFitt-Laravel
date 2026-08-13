<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE OR REPLACE VIEW vw_solicitudes_pendientes AS
SELECT
  sc.id_solicitud, sc.codigo_solicitud,
  CONCAT(sc.nombres, ' ', sc.apellidos) AS nombre_solicitante,
  sc.documento_identidad, sc.correo, sc.telefono,
  sc.modalidad, sc.tipo_cliente,
  p.nombre AS plan_solicitado, p.precio, p.moneda,
  sc.estado_solicitud, pg.id_pago, pg.estado_pago, pg.monto, pg.metodo_pago,
  pg.referencia_pago, pg.comprobante_url,
  sc.creado_en, DATEDIFF(NOW(), sc.creado_en) AS dias_en_espera
FROM solicitudes_compra sc
INNER JOIN planes p ON p.id_plan = sc.id_plan
LEFT JOIN pagos pg ON pg.id_solicitud = sc.id_solicitud
WHERE sc.estado_solicitud = 'PENDIENTE';
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP VIEW IF EXISTS `vw_solicitudes_pendientes`;
SQL);
    }
};
