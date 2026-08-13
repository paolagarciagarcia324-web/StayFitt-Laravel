<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE facturas (
  id_factura INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_pago INT UNSIGNED NOT NULL,
  numero_factura VARCHAR(40) NOT NULL,
  id_cliente INT UNSIGNED NOT NULL,
  id_institucion INT UNSIGNED NULL,
  subtotal DECIMAL(12,2) NOT NULL,
  monto_descuento DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  impuesto DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  total DECIMAL(12,2) NOT NULL,
  moneda CHAR(3) NOT NULL DEFAULT 'COP',
  pdf_url VARCHAR(500) NULL,
  xml_url VARCHAR(500) NULL,
  fecha_emision DATE NOT NULL,
  notas TEXT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_facturas_numero (numero_factura),
  UNIQUE KEY uq_facturas_pago (id_pago),
  CONSTRAINT chk_facturas_montos CHECK (subtotal >= 0 AND total >= 0 AND monto_descuento >= 0 AND impuesto >= 0),
  CONSTRAINT fk_facturas_pago
    FOREIGN KEY (id_pago) REFERENCES pagos(id_pago)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_facturas_cliente
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_facturas_institucion
    FOREIGN KEY (id_institucion) REFERENCES instituciones(id_institucion)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `facturas`;
SQL);
    }
};
