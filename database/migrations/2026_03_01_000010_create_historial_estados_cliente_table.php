<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE historial_estados_cliente (
  id_historial INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_cliente INT UNSIGNED NOT NULL,
  estado_anterior ENUM('ACTIVA','INACTIVA','SUSPENDIDA','PENDIENTE') NULL,
  estado_nuevo ENUM('ACTIVA','INACTIVA','SUSPENDIDA','PENDIENTE') NOT NULL,
  motivo VARCHAR(255) NULL,
  cambiado_por INT UNSIGNED NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_historial_cliente (id_cliente, creado_en),
  CONSTRAINT fk_historial_cliente
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_historial_user
    FOREIGN KEY (cambiado_por) REFERENCES usuarios(id_user)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `historial_estados_cliente`;
SQL);
    }
};
