<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE eventos (
  id_evento INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_institucion INT UNSIGNED NULL,
  id_coach INT UNSIGNED NULL,
  titulo VARCHAR(160) NOT NULL,
  descripcion TEXT NULL,
  categoria_evento VARCHAR(80) NULL,
  modalidad ENUM('VIRTUAL','PRESENCIAL','MIXTO') NOT NULL DEFAULT 'PRESENCIAL',
  es_gratuito TINYINT(1) NOT NULL DEFAULT 1,
  fecha_inicio DATETIME NOT NULL,
  fecha_fin DATETIME NOT NULL,
  ubicacion VARCHAR(180) NULL,
  enlace_virtual VARCHAR(500) NULL,
  cupo_maximo INT UNSIGNED NULL,
  precio DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  moneda CHAR(3) NOT NULL DEFAULT 'COP',
  imagen_url VARCHAR(500) NULL,
  estado_evento ENUM('PUBLICADO','BORRADOR','EN_CURSO','FINALIZADO','CANCELADO') NOT NULL DEFAULT 'BORRADOR',
  creado_por INT UNSIGNED NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT chk_ev_fechas CHECK (fecha_fin > fecha_inicio),
  CONSTRAINT chk_ev_precio CHECK (precio >= 0),
  CONSTRAINT fk_ev_inst FOREIGN KEY (id_institucion) REFERENCES instituciones(id_institucion) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_ev_coach FOREIGN KEY (id_coach) REFERENCES coaches(id_coach) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_ev_creado FOREIGN KEY (creado_por) REFERENCES usuarios(id_user) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `eventos`;
SQL);
    }
};
