<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE OR REPLACE VIEW vw_cupos_planes AS
SELECT
  p.id_plan, p.nombre, p.slug, p.modalidad, p.tipo_cliente, p.cupo_maximo, p.precio, p.moneda, p.estado_plan,
  COALESCE(SUM(CASE WHEN sc.estado_solicitud IN ('PENDIENTE','VALIDADA') THEN 1 ELSE 0 END), 0) AS cupos_reservados,
  CASE
    WHEN p.cupo_maximo IS NULL THEN NULL
    ELSE CAST(p.cupo_maximo AS SIGNED) - COALESCE(SUM(CASE WHEN sc.estado_solicitud IN ('PENDIENTE','VALIDADA') THEN 1 ELSE 0 END), 0)
  END AS cupos_disponibles,
  CASE
    WHEN p.estado_plan <> 'ACTIVO' THEN 'INACTIVO'
    WHEN p.cupo_maximo IS NULL THEN 'SIN_LIMITE'
    WHEN CAST(p.cupo_maximo AS SIGNED) - COALESCE(SUM(CASE WHEN sc.estado_solicitud IN ('PENDIENTE','VALIDADA') THEN 1 ELSE 0 END), 0) <= 0 THEN 'CUPO_LLENO'
    WHEN CAST(p.cupo_maximo AS SIGNED) - COALESCE(SUM(CASE WHEN sc.estado_solicitud IN ('PENDIENTE','VALIDADA') THEN 1 ELSE 0 END), 0) <= 5 THEN 'ULTIMOS_CUPOS'
    ELSE 'DISPONIBLE'
  END AS estado_cupo
FROM planes p
LEFT JOIN solicitudes_compra sc ON sc.id_plan = p.id_plan
GROUP BY p.id_plan, p.nombre, p.slug, p.modalidad, p.tipo_cliente, p.cupo_maximo, p.precio, p.moneda, p.estado_plan;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP VIEW IF EXISTS `vw_cupos_planes`;
SQL);
    }
};
