<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE bitacora_sistema (
  id_bitacora INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_user INT UNSIGNED NULL,
  id_sesion_app VARCHAR(100) NULL,
  modulo VARCHAR(80) NOT NULL,
  accion VARCHAR(120) NOT NULL,
  tipo_evento ENUM('INFO','WARNING','ERROR','CRITICAL') NOT NULL DEFAULT 'INFO',
  descripcion TEXT NULL,
  tabla_afectada VARCHAR(80) NULL,
  id_registro_afectado INT UNSIGNED NULL,
  ip VARCHAR(45) NULL,
  user_agent VARCHAR(255) NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_bit_mod (modulo, creado_en),
  KEY idx_bit_user (id_user),
  CONSTRAINT fk_bit_user FOREIGN KEY (id_user) REFERENCES usuarios(id_user) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `bitacora_sistema`;
SQL);
    }
};
