<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE solicitudes_compra (
  id_solicitud INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo_solicitud VARCHAR(40) NOT NULL,
  id_plan INT UNSIGNED NOT NULL,
  id_descuento INT UNSIGNED NULL,
  id_institucion INT UNSIGNED NULL,
  modalidad ENUM('VIRTUAL','PRESENCIAL') NOT NULL DEFAULT 'VIRTUAL',
  tipo_cliente ENUM('INDIVIDUAL','INSTITUCIONAL') NOT NULL DEFAULT 'INDIVIDUAL',
  nombres VARCHAR(100) NOT NULL,
  apellidos VARCHAR(100) NOT NULL,
  documento_identidad VARCHAR(30) NOT NULL,
  correo VARCHAR(150) NOT NULL,
  telefono VARCHAR(30) NULL,
  ciudad VARCHAR(100) NULL,
  acepto_terminos TINYINT(1) NOT NULL DEFAULT 0,
  fecha_aceptacion_terminos DATETIME NULL,
  observacion_usuario TEXT NULL,
  estado_solicitud ENUM('PENDIENTE','VALIDADA','RECHAZADA','CANCELADA') NOT NULL DEFAULT 'PENDIENTE',
  revisado_por INT UNSIGNED NULL,
  fecha_revision DATETIME NULL,
  observacion_admin TEXT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_solicitudes_codigo (codigo_solicitud),
  KEY idx_solicitudes_estado (estado_solicitud, creado_en),
  KEY idx_solicitudes_correo (correo),
  KEY idx_solicitudes_documento (documento_identidad),
  CONSTRAINT fk_solicitudes_plan
    FOREIGN KEY (id_plan) REFERENCES planes(id_plan)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_solicitudes_descuento
    FOREIGN KEY (id_descuento) REFERENCES descuentos_cupones(id_descuento)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_solicitudes_institucion
    FOREIGN KEY (id_institucion) REFERENCES instituciones(id_institucion)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_solicitudes_revisado_por
    FOREIGN KEY (revisado_por) REFERENCES usuarios(id_user)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `solicitudes_compra`;
SQL);
    }
};
