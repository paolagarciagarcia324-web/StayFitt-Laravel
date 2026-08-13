<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE productos (
  id_producto INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_categoria_producto INT UNSIGNED NOT NULL,
  sku VARCHAR(40) NOT NULL,
  nombre VARCHAR(150) NOT NULL,
  slug VARCHAR(170) NOT NULL,
  descripcion TEXT NULL,
  precio DECIMAL(12,2) NOT NULL,
  moneda CHAR(3) NOT NULL DEFAULT 'COP',
  stock INT NOT NULL DEFAULT 0,
  es_marca_figuefit TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = visitante necesita plan activo para comprar',
  requiere_plan_activo TINYINT(1) NOT NULL DEFAULT 0,
  imagen_url VARCHAR(500) NULL,
  variantes_json JSON NULL COMMENT 'tallas/colores',
  envio_local TINYINT(1) NOT NULL DEFAULT 1,
  envio_nacional TINYINT(1) NOT NULL DEFAULT 1,
  estado_producto ENUM('ACTIVO','INACTIVO','AGOTADO') NOT NULL DEFAULT 'ACTIVO',
  creado_por INT UNSIGNED NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_productos_sku (sku),
  UNIQUE KEY uq_productos_slug (slug),
  CONSTRAINT chk_prod_precio CHECK (precio >= 0),
  CONSTRAINT chk_prod_stock CHECK (stock >= 0),
  CONSTRAINT fk_prod_cat FOREIGN KEY (id_categoria_producto) REFERENCES categorias_producto(id_categoria_producto) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_prod_creado FOREIGN KEY (creado_por) REFERENCES usuarios(id_user) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `productos`;
SQL);
    }
};
