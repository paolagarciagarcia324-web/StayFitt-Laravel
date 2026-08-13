<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE rachas_diarias (
  id_racha INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_cliente INT UNSIGNED NOT NULL,
  fecha DATE NOT NULL,
  origen ENUM('VIDEO_VIRTUAL','ASISTENCIA_PRESENCIAL','RUTINA') NOT NULL,
  id_referencia INT UNSIGNED NULL COMMENT 'id_progreso_video / id_sesion_participante / id_registro_rutina',
  llama TINYINT(1) NOT NULL DEFAULT 1,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_racha_cliente_fecha_origen (id_cliente, fecha, origen),
  KEY idx_racha_cliente_fecha (id_cliente, fecha),
  CONSTRAINT fk_racha_cliente
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `rachas_diarias`;
SQL);
    }
};
