<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE programas_virtuales (
  id_programa INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_plan INT UNSIGNED NOT NULL,
  id_coach INT UNSIGNED NULL,
  nombre VARCHAR(150) NOT NULL,
  descripcion TEXT NULL,
  dificultad ENUM('PRINCIPIANTE','INTERMEDIO','AVANZADO','TODOS') NOT NULL DEFAULT 'PRINCIPIANTE',
  semanas_duracion INT UNSIGNED NULL,
  orden INT NOT NULL DEFAULT 0,
  imagen_url VARCHAR(500) NULL,
  estado_programa ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT chk_prog_semanas CHECK (semanas_duracion IS NULL OR semanas_duracion > 0),
  CONSTRAINT fk_prog_plan
    FOREIGN KEY (id_plan) REFERENCES planes(id_plan)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_prog_coach
    FOREIGN KEY (id_coach) REFERENCES coaches(id_coach)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `programas_virtuales`;
SQL);
    }
};
