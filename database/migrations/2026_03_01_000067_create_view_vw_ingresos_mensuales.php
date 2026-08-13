<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE OR REPLACE VIEW vw_ingresos_mensuales AS
SELECT
  DATE_FORMAT(pg.fecha_validacion, '%Y-%m') AS mes,
  COUNT(*) AS total_pagos,
  SUM(pg.monto) AS ingreso_total,
  SUM(pg.monto_descuento) AS total_descuentos,
  AVG(pg.monto) AS ticket_promedio,
  pg.moneda
FROM pagos pg
WHERE pg.estado_pago = 'VALIDADO' AND pg.fecha_validacion IS NOT NULL
GROUP BY DATE_FORMAT(pg.fecha_validacion, '%Y-%m'), pg.moneda
ORDER BY mes DESC;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP VIEW IF EXISTS `vw_ingresos_mensuales`;
SQL);
    }
};
