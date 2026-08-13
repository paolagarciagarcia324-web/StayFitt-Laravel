<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE sesiones (
  id_sesion INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_coach INT UNSIGNED NULL,
  id_cliente INT UNSIGNED NULL,
  id_institucion INT UNSIGNED NULL,
  id_plan INT UNSIGNED NULL,
  id_horario INT UNSIGNED NULL,
  titulo VARCHAR(160) NOT NULL,
  descripcion TEXT NULL,
  tipo_sesion ENUM('INDIVIDUAL','GRUPAL','INSTITUCIONAL','EVENTO','PRUEBA') NOT NULL DEFAULT 'INDIVIDUAL',
  modalidad ENUM('VIRTUAL','PRESENCIAL','MIXTO') NOT NULL DEFAULT 'VIRTUAL',
  es_publica TINYINT(1) NOT NULL DEFAULT 0,
  fecha_inicio DATETIME NOT NULL,
  fecha_fin DATETIME NOT NULL,
  ubicacion VARCHAR(180) NULL,
  enlace_virtual VARCHAR(500) NULL,
  cupo_maximo INT UNSIGNED NULL,
  estado_sesion ENUM('PROGRAMADA','EN_CURSO','REALIZADA','CANCELADA','REPROGRAMADA') NOT NULL DEFAULT 'PROGRAMADA',
  creado_por INT UNSIGNED NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_sesiones_fecha (fecha_inicio, estado_sesion),
  KEY idx_sesiones_coach (id_coach, fecha_inicio),
  CONSTRAINT chk_ses_fechas CHECK (fecha_fin > fecha_inicio),
  CONSTRAINT chk_ses_cupo CHECK (cupo_maximo IS NULL OR cupo_maximo > 0),
  CONSTRAINT fk_ses_coach FOREIGN KEY (id_coach) REFERENCES coaches(id_coach) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_ses_cliente FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_ses_inst FOREIGN KEY (id_institucion) REFERENCES instituciones(id_institucion) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_ses_plan FOREIGN KEY (id_plan) REFERENCES planes(id_plan) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_ses_horario FOREIGN KEY (id_horario) REFERENCES horarios(id_horario) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_ses_creado FOREIGN KEY (creado_por) REFERENCES usuarios(id_user) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `sesiones`;
SQL);
    }
};
