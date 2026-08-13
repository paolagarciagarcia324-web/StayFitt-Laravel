<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE acceso_cliente_modulo (
  id_acceso INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_plan_cliente INT UNSIGNED NOT NULL,
  id_cliente INT UNSIGNED NOT NULL,
  id_modulo INT UNSIGNED NOT NULL,
  fecha_inicio DATE NULL,
  fecha_fin DATE NULL,
  estado_acceso ENUM('ACTIVO','INACTIVO','VENCIDO') NOT NULL DEFAULT 'ACTIVO',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_acceso_pc_modulo (id_plan_cliente, id_modulo),
  KEY idx_acceso_cliente (id_cliente, estado_acceso),
  CONSTRAINT fk_acceso_pc
    FOREIGN KEY (id_plan_cliente) REFERENCES planes_cliente(id_plan_cliente)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_acceso_cliente
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_acceso_modulo
    FOREIGN KEY (id_modulo) REFERENCES modulos_servicio(id_modulo)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `acceso_cliente_modulo`;
SQL);
    }
};
