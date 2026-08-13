<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE solicitudes_renovacion (
  id_renovacion INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_cliente INT UNSIGNED NOT NULL,
  id_plan_cliente_actual INT UNSIGNED NOT NULL,
  id_plan_nuevo INT UNSIGNED NOT NULL,
  estado ENUM('PENDIENTE','APROBADA','RECHAZADA','CANCELADA') NOT NULL DEFAULT 'PENDIENTE',
  observacion_cliente TEXT NULL,
  observacion_admin TEXT NULL,
  revisado_por INT UNSIGNED NULL,
  fecha_revision DATETIME NULL,
  id_pago_nuevo INT UNSIGNED NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_renovacion_estado (estado, creado_en),
  CONSTRAINT fk_renovacion_cliente
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_renovacion_pc
    FOREIGN KEY (id_plan_cliente_actual) REFERENCES planes_cliente(id_plan_cliente)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_renovacion_plan
    FOREIGN KEY (id_plan_nuevo) REFERENCES planes(id_plan)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_renovacion_revisado
    FOREIGN KEY (revisado_por) REFERENCES usuarios(id_user)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_renovacion_pago
    FOREIGN KEY (id_pago_nuevo) REFERENCES pagos(id_pago)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `solicitudes_renovacion`;
SQL);
    }
};
