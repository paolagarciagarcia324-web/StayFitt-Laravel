<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE plan_modulos (
  id_plan_modulo INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_plan INT UNSIGNED NOT NULL,
  id_modulo INT UNSIGNED NOT NULL,
  orden INT NOT NULL DEFAULT 0,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_plan_modulo (id_plan, id_modulo),
  CONSTRAINT fk_plan_modulos_plan
    FOREIGN KEY (id_plan) REFERENCES planes(id_plan)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_plan_modulos_modulo
    FOREIGN KEY (id_modulo) REFERENCES modulos_servicio(id_modulo)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `plan_modulos`;
SQL);
    }
};
