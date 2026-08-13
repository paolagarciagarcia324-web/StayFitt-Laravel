<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE planes_cliente (
  id_plan_cliente INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_cliente INT UNSIGNED NOT NULL,
  id_plan INT UNSIGNED NOT NULL,
  id_solicitud INT UNSIGNED NULL,
  id_pago INT UNSIGNED NULL,
  id_suscripcion INT UNSIGNED NULL,
  fecha_inicio DATE NOT NULL,
  fecha_fin DATE NOT NULL,
  periodo_prueba TINYINT(1) NOT NULL DEFAULT 0,
  renovacion_automatica TINYINT(1) NOT NULL DEFAULT 0,
  dias_gracia INT UNSIGNED NOT NULL DEFAULT 3,
  estado_plan_cliente ENUM('ACTIVO','VENCIDO','CANCELADO','SUSPENDIDO','EN_PERIODO_PRUEBA') NOT NULL DEFAULT 'ACTIVO',
  fecha_cancelacion DATETIME NULL,
  motivo_cancelacion VARCHAR(255) NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_pc_cliente_estado (id_cliente, estado_plan_cliente),
  KEY idx_pc_fechas (fecha_inicio, fecha_fin),
  CONSTRAINT chk_pc_fechas CHECK (fecha_fin >= fecha_inicio),
  CONSTRAINT fk_pc_cliente
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_pc_plan
    FOREIGN KEY (id_plan) REFERENCES planes(id_plan)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_pc_solicitud
    FOREIGN KEY (id_solicitud) REFERENCES solicitudes_compra(id_solicitud)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `planes_cliente`;
SQL);
    }
};
