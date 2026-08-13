<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE material_entrenamiento (
  id_material INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_plan_entrenamiento INT UNSIGNED NULL,
  id_programa INT UNSIGNED NULL,
  id_coach INT UNSIGNED NULL,
  titulo VARCHAR(150) NOT NULL,
  tipo_material ENUM('PDF','VIDEO','IMAGEN','ENLACE','PRESENTACION','OTRO') NOT NULL DEFAULT 'ENLACE',
  url_material VARCHAR(500) NOT NULL,
  tamano_archivo VARCHAR(20) NULL,
  descripcion TEXT NULL,
  orden INT NOT NULL DEFAULT 0,
  estado ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_mat_pe FOREIGN KEY (id_plan_entrenamiento) REFERENCES planes_entrenamiento(id_plan_entrenamiento) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_mat_prog FOREIGN KEY (id_programa) REFERENCES programas_virtuales(id_programa) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_mat_coach FOREIGN KEY (id_coach) REFERENCES coaches(id_coach) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `material_entrenamiento`;
SQL);
    }
};
