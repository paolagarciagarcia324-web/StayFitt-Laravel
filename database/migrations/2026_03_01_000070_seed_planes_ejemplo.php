<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('planes')->where('estado_plan', 'ACTIVO')->exists();
        if ($exists) {
            return;
        }

        DB::unprepared(<<<'SQL'
INSERT INTO planes (
  nombre, slug, descripcion, caracteristicas_json, precio, moneda, duracion_dias,
  modalidad, tipo_cliente, cupo_maximo, orden, popular, etiqueta, destacado, estado_plan
) VALUES
(
  'Plan Virtual Esencial',
  'plan-virtual-esencial',
  'Entrena desde casa con rutinas, videos y seguimiento digital guiado por tu coach.',
  '["Rutinas semanales personalizadas","Acceso a contenido virtual","Seguimiento de progreso","Chat con coach","Recordatorios y metas"]',
  129000.00, 'COP', 30, 'VIRTUAL', 'INDIVIDUAL', 50, 1, 0, NULL, 0, 'ACTIVO'
),
(
  'Plan Presencial Plus',
  'plan-presencial-plus',
  'Sesiones presenciales, horarios reservables y acompañamiento en sede con evaluación física.',
  '["Sesiones presenciales semanales","Reserva de horarios","Evaluación y seguimiento","Plan nutricional básico","Agenda y asistencia"]',
  249000.00, 'COP', 30, 'PRESENCIAL', 'INDIVIDUAL', 20, 2, 1, 'MÁS POPULAR', 1, 'ACTIVO'
),
(
  'Plan Mixto Premium',
  'plan-mixto-premium',
  'Lo mejor de virtual y presencial: entrenamiento híbrido con acceso completo a la plataforma.',
  '["Entrenamiento virtual + presencial","Nutrición y progreso avanzado","Videos con desbloqueo progresivo","Prioridad en reservas","Comunidad y eventos"]',
  189000.00, 'COP', 30, 'MIXTO', 'INDIVIDUAL', 30, 3, 0, 'RECOMENDADO', 1, 'ACTIVO'
);
SQL);

        DB::unprepared(<<<'SQL'
INSERT INTO plan_modulos (id_plan, id_modulo, orden)
SELECT p.id_plan, m.id_modulo, x.orden
FROM planes p
JOIN (
  SELECT 'plan-virtual-esencial' AS slug, 'PERFIL' AS codigo, 1 AS orden UNION ALL
  SELECT 'plan-virtual-esencial', 'ENTRENAMIENTO', 2 UNION ALL
  SELECT 'plan-virtual-esencial', 'CONTENIDO_VIRTUAL', 3 UNION ALL
  SELECT 'plan-virtual-esencial', 'PROGRESO', 4 UNION ALL
  SELECT 'plan-virtual-esencial', 'COMUNICACION', 5 UNION ALL
  SELECT 'plan-presencial-plus', 'PERFIL', 1 UNION ALL
  SELECT 'plan-presencial-plus', 'ENTRENAMIENTO', 2 UNION ALL
  SELECT 'plan-presencial-plus', 'NUTRICION', 3 UNION ALL
  SELECT 'plan-presencial-plus', 'AGENDA', 4 UNION ALL
  SELECT 'plan-presencial-plus', 'PROGRESO', 5 UNION ALL
  SELECT 'plan-presencial-plus', 'PAGOS', 6 UNION ALL
  SELECT 'plan-mixto-premium', 'PERFIL', 1 UNION ALL
  SELECT 'plan-mixto-premium', 'ENTRENAMIENTO', 2 UNION ALL
  SELECT 'plan-mixto-premium', 'NUTRICION', 3 UNION ALL
  SELECT 'plan-mixto-premium', 'CONTENIDO_VIRTUAL', 4 UNION ALL
  SELECT 'plan-mixto-premium', 'PROGRESO', 5 UNION ALL
  SELECT 'plan-mixto-premium', 'AGENDA', 6 UNION ALL
  SELECT 'plan-mixto-premium', 'COMUNICACION', 7
) x ON x.slug = p.slug
JOIN modulos_servicio m ON m.codigo = x.codigo;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DELETE pm FROM plan_modulos pm
INNER JOIN planes p ON p.id_plan = pm.id_plan
WHERE p.slug IN ('plan-virtual-esencial','plan-presencial-plus','plan-mixto-premium');
SQL);
        DB::unprepared(<<<'SQL'
DELETE FROM planes WHERE slug IN ('plan-virtual-esencial','plan-presencial-plus','plan-mixto-premium');
SQL);
    }
};
