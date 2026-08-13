<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE registro_rutinas (
  id_registro_rutina INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_cliente INT UNSIGNED NOT NULL,
  id_rutina INT UNSIGNED NOT NULL,
  fecha_registro DATE NOT NULL,
  estado_registro ENUM('PENDIENTE','COMPLETADA','NO_REALIZADA','EN_PROGRESO') NOT NULL DEFAULT 'PENDIENTE',
  fecha_inicio DATETIME NULL,
  fecha_completado DATETIME NULL,
  notas_cliente TEXT NULL,
  calificacion_dificultad TINYINT UNSIGNED NULL,
  crea_racha TINYINT(1) NOT NULL DEFAULT 0,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_cliente_rutina_fecha (id_cliente, id_rutina, fecha_registro),
  CONSTRAINT chk_rr_calif CHECK (calificacion_dificultad IS NULL OR calificacion_dificultad BETWEEN 1 AND 5),
  CONSTRAINT fk_rr_cliente FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_rr_rutina FOREIGN KEY (id_rutina) REFERENCES rutinas(id_rutina) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `registro_rutinas`;
SQL);
    }
};
