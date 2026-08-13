<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE sesion_participantes (
  id_sesion_participante INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_sesion INT UNSIGNED NOT NULL,
  id_cliente INT UNSIGNED NOT NULL,
  estado_asistencia ENUM('INSCRITA','ASISTIO','NO_ASISTIO','CANCELADA','JUSTIFICADA') NOT NULL DEFAULT 'INSCRITA',
  genera_racha TINYINT(1) NOT NULL DEFAULT 0,
  observaciones TEXT NULL,
  marcado_por INT UNSIGNED NULL,
  marcado_en DATETIME NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_sesion_cliente (id_sesion, id_cliente),
  CONSTRAINT fk_sp_sesion FOREIGN KEY (id_sesion) REFERENCES sesiones(id_sesion) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_sp_cliente FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_sp_marcado FOREIGN KEY (marcado_por) REFERENCES usuarios(id_user) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `sesion_participantes`;
SQL);
    }
};
