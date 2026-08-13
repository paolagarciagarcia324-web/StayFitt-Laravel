<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE rutina_ejercicios (
  id_rutina_ejercicio INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_rutina INT UNSIGNED NOT NULL,
  id_ejercicio INT UNSIGNED NOT NULL,
  series INT UNSIGNED NULL,
  repeticiones VARCHAR(50) NULL,
  peso_sugerido_kg DECIMAL(6,2) NULL,
  duracion_segundos INT UNSIGNED NULL,
  descanso_segundos INT UNSIGNED NULL,
  rir TINYINT UNSIGNED NULL,
  rm_porcentaje DECIMAL(4,1) NULL,
  ritmo_ejecucion VARCHAR(20) NULL,
  fallo_muscular TINYINT(1) NOT NULL DEFAULT 0,
  orden INT NOT NULL DEFAULT 0,
  observaciones TEXT NULL,
  CONSTRAINT chk_re_series CHECK (series IS NULL OR series > 0),
  CONSTRAINT chk_re_rir CHECK (rir IS NULL OR rir <= 5),
  CONSTRAINT fk_re_rutina FOREIGN KEY (id_rutina) REFERENCES rutinas(id_rutina) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_re_ejercicio FOREIGN KEY (id_ejercicio) REFERENCES ejercicios(id_ejercicio) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `rutina_ejercicios`;
SQL);
    }
};
