<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE devoluciones (
  id_devolucion INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_pago INT UNSIGNED NOT NULL,
  codigo_devolucion VARCHAR(40) NOT NULL,
  monto DECIMAL(12,2) NOT NULL,
  moneda CHAR(3) NOT NULL DEFAULT 'COP',
  motivo ENUM('RECHAZO_SOLICITUD','SOLICITUD_CLIENTE','ERROR_PAGO','DUPLICADO','OTRO') NOT NULL,
  metodo_devolucion ENUM('MISMO_MEDIO','TRANSFERENCIA','NEQUI','DAVIPLATA','EFECTIVO','OTRO') NOT NULL DEFAULT 'MISMO_MEDIO',
  referencia_devolucion VARCHAR(120) NULL,
  estado_devolucion ENUM('PENDIENTE','EN_PROCESO','COMPLETADA','CANCELADA') NOT NULL DEFAULT 'PENDIENTE',
  solicitado_por INT UNSIGNED NULL,
  procesado_por INT UNSIGNED NULL,
  fecha_solicitud DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_completada DATETIME NULL,
  observacion TEXT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_devoluciones_codigo (codigo_devolucion),
  KEY idx_devoluciones_pago (id_pago, estado_devolucion),
  CONSTRAINT chk_devoluciones_monto CHECK (monto > 0),
  CONSTRAINT fk_devoluciones_pago
    FOREIGN KEY (id_pago) REFERENCES pagos(id_pago)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_devoluciones_solicitado
    FOREIGN KEY (solicitado_por) REFERENCES usuarios(id_user)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_devoluciones_procesado
    FOREIGN KEY (procesado_por) REFERENCES usuarios(id_user)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `devoluciones`;
SQL);
    }
};
