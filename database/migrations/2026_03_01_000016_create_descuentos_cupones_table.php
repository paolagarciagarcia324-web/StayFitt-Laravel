<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE descuentos_cupones (
  id_descuento INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(30) NOT NULL,
  descripcion VARCHAR(255) NULL,
  tipo_descuento ENUM('PORCENTAJE','MONTO_FIJO') NOT NULL DEFAULT 'PORCENTAJE',
  valor DECIMAL(10,2) NOT NULL,
  tipo_aplicacion ENUM('GENERAL','PLAN_ESPECIFICO','INSTITUCIONAL','PRIMERA_COMPRA','TIENDA') NOT NULL DEFAULT 'GENERAL',
  id_plan INT UNSIGNED NULL,
  id_institucion INT UNSIGNED NULL,
  uso_maximo INT UNSIGNED NULL,
  usos_actuales INT UNSIGNED NOT NULL DEFAULT 0,
  uso_maximo_por_usuario INT UNSIGNED NOT NULL DEFAULT 1,
  monto_minimo_compra DECIMAL(12,2) NULL,
  fecha_inicio DATE NOT NULL,
  fecha_fin DATE NOT NULL,
  estado_cupon ENUM('ACTIVO','INACTIVO','AGOTADO','VENCIDO') NOT NULL DEFAULT 'ACTIVO',
  creado_por INT UNSIGNED NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_cupones_codigo (codigo),
  CONSTRAINT chk_descuentos_valor CHECK (valor >= 0),
  CONSTRAINT chk_descuentos_fecha CHECK (fecha_fin >= fecha_inicio),
  CONSTRAINT fk_descuentos_plan
    FOREIGN KEY (id_plan) REFERENCES planes(id_plan)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_descuentos_institucion
    FOREIGN KEY (id_institucion) REFERENCES instituciones(id_institucion)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_descuentos_creado_por
    FOREIGN KEY (creado_por) REFERENCES usuarios(id_user)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `descuentos_cupones`;
SQL);
    }
};
