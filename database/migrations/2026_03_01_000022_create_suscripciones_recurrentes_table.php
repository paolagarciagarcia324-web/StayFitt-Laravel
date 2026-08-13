<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE suscripciones_recurrentes (
  id_suscripcion INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_plan_cliente INT UNSIGNED NOT NULL,
  id_cliente INT UNSIGNED NOT NULL,
  id_plan INT UNSIGNED NOT NULL,
  monto DECIMAL(12,2) NOT NULL,
  moneda CHAR(3) NOT NULL DEFAULT 'COP',
  frecuencia ENUM('MENSUAL','TRIMESTRAL','SEMESTRAL','ANUAL') NOT NULL DEFAULT 'MENSUAL',
  dia_cobro TINYINT UNSIGNED NOT NULL DEFAULT 1,
  metodo_pago_preferido ENUM('TRANSFERENCIA','NEQUI','DAVIPLATA','TARJETA_CREDITO','TARJETA_DEBITO','EFECTIVO','PSE','PAYPAL','OTRO') NULL,
  referencia_pago_automatico VARCHAR(120) NULL,
  estado_suscripcion ENUM('ACTIVA','PAUSADA','CANCELADA','VENCIDA') NOT NULL DEFAULT 'ACTIVA',
  fecha_inicio DATE NOT NULL,
  fecha_proximo_cobro DATE NOT NULL,
  fecha_ultimo_cobro DATE NULL,
  fecha_cancelacion DATETIME NULL,
  motivo_cancelacion VARCHAR(255) NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT chk_susc_monto CHECK (monto >= 0),
  CONSTRAINT chk_susc_dia CHECK (dia_cobro BETWEEN 1 AND 31),
  CONSTRAINT fk_susc_cliente
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_susc_plan
    FOREIGN KEY (id_plan) REFERENCES planes(id_plan)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `suscripciones_recurrentes`;
SQL);
    }
};
