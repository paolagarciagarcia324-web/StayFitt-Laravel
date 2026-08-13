<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE planes (
  id_plan INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL,
  slug VARCHAR(170) NOT NULL,
  descripcion TEXT NULL,
  caracteristicas_json JSON NULL,
  precio DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  moneda CHAR(3) NOT NULL DEFAULT 'COP',
  duracion_dias INT UNSIGNED NOT NULL DEFAULT 30,
  periodo_prueba_dias INT UNSIGNED NULL,
  modalidad ENUM('VIRTUAL','PRESENCIAL','MIXTO') NOT NULL DEFAULT 'VIRTUAL',
  tipo_cliente ENUM('INDIVIDUAL','INSTITUCIONAL','AMBOS') NOT NULL DEFAULT 'INDIVIDUAL',
  cupo_maximo INT UNSIGNED NULL,
  requiere_coach TINYINT(1) NOT NULL DEFAULT 0,
  orden INT NOT NULL DEFAULT 0,
  color_hex VARCHAR(7) NULL,
  popular TINYINT(1) NOT NULL DEFAULT 0,
  etiqueta VARCHAR(50) NULL,
  imagen_url VARCHAR(500) NULL,
  destacado TINYINT(1) NOT NULL DEFAULT 0,
  dias_gracia INT UNSIGNED NOT NULL DEFAULT 3,
  renovacion_automatica TINYINT(1) NOT NULL DEFAULT 0,
  fecha_inicio_programa DATE NULL,
  fecha_fin_programa DATE NULL,
  estado_plan ENUM('ACTIVO','INACTIVO','BORRADOR') NOT NULL DEFAULT 'BORRADOR',
  creado_por INT UNSIGNED NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_planes_slug (slug),
  KEY idx_planes_estado_modalidad (estado_plan, modalidad, tipo_cliente),
  CONSTRAINT chk_planes_precio CHECK (precio >= 0),
  CONSTRAINT chk_planes_duracion CHECK (duracion_dias > 0),
  CONSTRAINT chk_planes_cupo CHECK (cupo_maximo IS NULL OR cupo_maximo > 0),
  CONSTRAINT fk_planes_creado_por
    FOREIGN KEY (creado_por) REFERENCES usuarios(id_user)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `planes`;
SQL);
    }
};
