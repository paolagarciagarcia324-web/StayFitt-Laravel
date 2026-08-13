<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE pedido_items (
  id_pedido_item INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_pedido INT UNSIGNED NOT NULL,
  id_producto INT UNSIGNED NOT NULL,
  cantidad INT UNSIGNED NOT NULL,
  precio_unitario DECIMAL(12,2) NOT NULL,
  variante VARCHAR(80) NULL,
  subtotal DECIMAL(12,2) NOT NULL,
  UNIQUE KEY uq_pedido_producto_var (id_pedido, id_producto, variante),
  CONSTRAINT chk_pi_cant CHECK (cantidad > 0),
  CONSTRAINT chk_pi_precio CHECK (precio_unitario >= 0 AND subtotal >= 0),
  CONSTRAINT fk_pi_pedido FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_pi_producto FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `pedido_items`;
SQL);
    }
};
