<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE pagos (
  id_pago INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  id_solicitud INT UNSIGNED NULL,
  id_cliente INT UNSIGNED NULL,
  id_plan INT UNSIGNED NULL,
  id_plan_cliente INT UNSIGNED NULL,
  origen_pago ENUM('SOLICITUD_PLAN','RENOVACION','PRESENCIAL','TIENDA','EVENTO','OTRO') NOT NULL DEFAULT 'SOLICITUD_PLAN',
  monto DECIMAL(12,2) NOT NULL,
  moneda CHAR(3) NOT NULL DEFAULT 'COP',
  tipo_pago ENUM('UNICO','MENSUAL','TRIMESTRAL','SEMESTRAL','ANUAL') NOT NULL DEFAULT 'UNICO',
  metodo_pago ENUM('TRANSFERENCIA','NEQUI','DAVIPLATA','TARJETA_CREDITO','TARJETA_DEBITO','EFECTIVO','PSE','PAYPAL','OTRO') NOT NULL DEFAULT 'TRANSFERENCIA',
  referencia_pago VARCHAR(120) NULL,
  codigo_transaccion VARCHAR(120) NULL,
  comprobante_url VARCHAR(500) NULL,
  fecha_pago DATETIME NULL,
  fecha_vencimiento DATE NULL,
  monto_descuento DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  porcentaje_comision DECIMAL(5,2) NULL,
  estado_pago ENUM('PENDIENTE','VALIDADO','RECHAZADO','REEMBOLSADO','REEMBOLSO_PARCIAL') NOT NULL DEFAULT 'PENDIENTE',
  validado_por INT UNSIGNED NULL,
  fecha_validacion DATETIME NULL,
  observacion_admin TEXT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  UNIQUE KEY uq_pago_solicitud (id_solicitud),
  UNIQUE KEY uq_pago_codigo_transaccion (codigo_transaccion),
  KEY idx_pagos_estado (estado_pago, creado_en),
  KEY idx_pagos_cliente (id_cliente),
  CONSTRAINT chk_pagos_monto CHECK (monto >= 0),
  CONSTRAINT chk_pagos_descuento CHECK (monto_descuento >= 0),
  CONSTRAINT chk_pagos_comision CHECK (porcentaje_comision IS NULL OR (porcentaje_comision >= 0 AND porcentaje_comision <= 100)),
  CONSTRAINT chk_pagos_validacion CHECK (
    (estado_pago = 'PENDIENTE' AND fecha_validacion IS NULL)
    OR (estado_pago <> 'PENDIENTE')
  ),
  CONSTRAINT fk_pagos_solicitud
    FOREIGN KEY (id_solicitud) REFERENCES solicitudes_compra(id_solicitud)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_pagos_cliente
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_pagos_plan
    FOREIGN KEY (id_plan) REFERENCES planes(id_plan)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_pagos_validado_por
    FOREIGN KEY (validado_por) REFERENCES usuarios(id_user)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `pagos`;
SQL);
    }
};
