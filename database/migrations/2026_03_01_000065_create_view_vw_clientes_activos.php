<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE OR REPLACE VIEW vw_clientes_activos AS
SELECT
  c.id_cliente, u.nombres, u.apellidos, u.correo, u.telefono,
  c.modalidad, c.tipo_cliente, c.estado_cliente, c.racha_actual, c.racha_maxima,
  i.nombre AS institucion, h.nombre AS horario,
  pc.id_plan_cliente, pl.nombre AS plan_actual, pc.fecha_inicio, pc.fecha_fin,
  DATEDIFF(pc.fecha_fin, CURDATE()) AS dias_restantes,
  c.id_coach, CONCAT(cu.nombres, ' ', cu.apellidos) AS nombre_coach
FROM clientes c
INNER JOIN usuarios u ON u.id_user = c.id_user
LEFT JOIN instituciones i ON i.id_institucion = c.id_institucion
LEFT JOIN horarios h ON h.id_horario = c.id_horario
LEFT JOIN planes_cliente pc ON pc.id_cliente = c.id_cliente AND pc.estado_plan_cliente IN ('ACTIVO','EN_PERIODO_PRUEBA')
LEFT JOIN planes pl ON pl.id_plan = pc.id_plan
LEFT JOIN coaches co ON co.id_coach = c.id_coach
LEFT JOIN usuarios cu ON cu.id_user = co.id_user
WHERE c.estado_cliente = 'ACTIVA';
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP VIEW IF EXISTS `vw_clientes_activos`;
SQL);
    }
};
