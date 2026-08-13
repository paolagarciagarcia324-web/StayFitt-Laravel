<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE progreso_fotos (
  id_foto INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_registro_progreso INT UNSIGNED NOT NULL,
  tipo_foto ENUM('FRENTE','PERFIL','ESPALDA','OTRA') NOT NULL,
  url_foto VARCHAR(500) NOT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_foto_registro_tipo (id_registro_progreso, tipo_foto),
  CONSTRAINT fk_fotos_progreso
    FOREIGN KEY (id_registro_progreso) REFERENCES registros_progreso(id_registro_progreso)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `progreso_fotos`;
SQL);
    }
};
