<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE metas_cliente (
  id_meta INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_cliente INT UNSIGNED NOT NULL,
  id_coach INT UNSIGNED NULL,
  tipo_meta ENUM('PESO','GRASA_CORPORAL','MASA_MUSCULAR','CINTURA','CADERA','EJERCICIO_SEMANAL','CALORIAS_DIARIAS','AGUA_DIARIA','HORAS_SUENO','MEDIDA_PERSONALIZADA','OTRA') NOT NULL DEFAULT 'PESO',
  descripcion VARCHAR(255) NOT NULL,
  valor_inicial DECIMAL(8,2) NULL,
  valor_objetivo DECIMAL(8,2) NOT NULL,
  valor_actual DECIMAL(8,2) NULL,
  unidad_medida VARCHAR(30) NOT NULL DEFAULT 'kg',
  fecha_inicio DATE NOT NULL,
  fecha_limite DATE NULL,
  fecha_completada DATETIME NULL,
  progreso_porcentaje DECIMAL(5,2) GENERATED ALWAYS AS (
    CASE
      WHEN valor_objetivo = valor_inicial THEN 0
      WHEN valor_inicial IS NULL OR valor_actual IS NULL THEN NULL
      WHEN valor_objetivo > valor_inicial
        THEN LEAST(GREATEST((valor_actual - valor_inicial) / NULLIF(valor_objetivo - valor_inicial, 0) * 100, 0), 100)
      ELSE LEAST(GREATEST((valor_inicial - valor_actual) / NULLIF(valor_inicial - valor_objetivo, 0) * 100, 0), 100)
    END
  ) VIRTUAL,
  estado_meta ENUM('EN_PROGRESO','COMPLETADA','CANCELADA','VENCIDA') NOT NULL DEFAULT 'EN_PROGRESO',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_metas_cliente FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_metas_coach FOREIGN KEY (id_coach) REFERENCES coaches(id_coach) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `metas_cliente`;
SQL);
    }
};
