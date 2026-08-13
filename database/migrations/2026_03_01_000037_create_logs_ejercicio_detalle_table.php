<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE logs_ejercicio_detalle (
  id_log_ejercicio INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_registro_rutina INT UNSIGNED NOT NULL,
  id_rutina_ejercicio INT UNSIGNED NOT NULL,
  numero_serie INT UNSIGNED NOT NULL,
  peso_kg DECIMAL(6,2) NULL,
  repeticiones INT UNSIGNED NULL,
  rpe TINYINT UNSIGNED NULL,
  distancia_metros INT UNSIGNED NULL,
  duracion_segundos INT UNSIGNED NULL,
  descanso_segundos INT UNSIGNED NULL,
  completado TINYINT(1) NOT NULL DEFAULT 1,
  notas TEXT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT chk_log_serie CHECK (numero_serie > 0),
  CONSTRAINT chk_log_rpe CHECK (rpe IS NULL OR rpe BETWEEN 1 AND 10),
  CONSTRAINT fk_log_rr FOREIGN KEY (id_registro_rutina) REFERENCES registro_rutinas(id_registro_rutina) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_log_re FOREIGN KEY (id_rutina_ejercicio) REFERENCES rutina_ejercicios(id_rutina_ejercicio) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `logs_ejercicio_detalle`;
SQL);
    }
};
