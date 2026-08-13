<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE horarios (
  id_horario INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_coach INT UNSIGNED NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  dias_semana SET('LUNES','MARTES','MIERCOLES','JUEVES','VIERNES','SABADO','DOMINGO') NOT NULL,
  hora_inicio TIME NOT NULL,
  hora_fin TIME NOT NULL,
  cupo_maximo INT UNSIGNED NULL,
  lugar VARCHAR(180) NULL,
  modalidad ENUM('PRESENCIAL','VIRTUAL','MIXTO') NOT NULL DEFAULT 'PRESENCIAL',
  estado ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT chk_horarios_horas CHECK (hora_fin > hora_inicio),
  CONSTRAINT chk_horarios_cupo CHECK (cupo_maximo IS NULL OR cupo_maximo > 0),
  KEY idx_horarios_coach (id_coach, estado),
  CONSTRAINT fk_horarios_coach
    FOREIGN KEY (id_coach) REFERENCES coaches(id_coach)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `horarios`;
SQL);
    }
};
