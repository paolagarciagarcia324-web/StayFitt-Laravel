<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE pedidos (
  id_pedido INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo_pedido VARCHAR(40) NOT NULL,
  id_user INT UNSIGNED NOT NULL,
  id_cliente INT UNSIGNED NULL,
  id_direccion INT UNSIGNED NULL,
  id_pago INT UNSIGNED NULL,
  tipo_envio ENUM('LOCAL','NACIONAL','RECOGIDA') NOT NULL DEFAULT 'LOCAL',
  subtotal DECIMAL(12,2) NOT NULL,
  costo_envio DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  descuento DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  total DECIMAL(12,2) NOT NULL,
  moneda CHAR(3) NOT NULL DEFAULT 'COP',
  estado_pedido ENUM('CARRITO','PENDIENTE_PAGO','PAGADO','PREPARANDO','ENVIADO','ENTREGADO','CANCELADO') NOT NULL DEFAULT 'CARRITO',
  tracking VARCHAR(120) NULL,
  notas TEXT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_pedidos_codigo (codigo_pedido),
  KEY idx_pedidos_user (id_user, estado_pedido),
  CONSTRAINT chk_ped_montos CHECK (subtotal >= 0 AND total >= 0 AND costo_envio >= 0 AND descuento >= 0),
  CONSTRAINT fk_ped_user FOREIGN KEY (id_user) REFERENCES usuarios(id_user) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_ped_cliente FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_ped_dir FOREIGN KEY (id_direccion) REFERENCES direcciones_envio(id_direccion) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_ped_pago FOREIGN KEY (id_pago) REFERENCES pagos(id_pago) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `pedidos`;
SQL);
    }
};
