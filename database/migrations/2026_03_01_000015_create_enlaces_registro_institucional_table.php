<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE enlaces_registro_institucional (
  id_enlace INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_institucion INT UNSIGNED NOT NULL,
  id_plan INT UNSIGNED NOT NULL,
  token VARCHAR(64) NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  limite_uso INT UNSIGNED NULL,
  registros_realizados INT UNSIGNED NOT NULL DEFAULT 0,
  fecha_expiracion DATETIME NULL,
  creado_por INT UNSIGNED NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_enlaces_token (token),
  KEY idx_enlaces_institucion (id_institucion, activo),
  CONSTRAINT chk_enlaces_limite CHECK (limite_uso IS NULL OR limite_uso > 0),
  CONSTRAINT chk_enlaces_registros CHECK (registros_realizados >= 0),
  CONSTRAINT fk_enlace_institucion
    FOREIGN KEY (id_institucion) REFERENCES instituciones(id_institucion)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_enlace_plan
    FOREIGN KEY (id_plan) REFERENCES planes(id_plan)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_enlace_creado_por
    FOREIGN KEY (creado_por) REFERENCES usuarios(id_user)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `enlaces_registro_institucional`;
SQL);
    }
};
