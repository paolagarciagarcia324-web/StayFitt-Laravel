<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE disponibilidades (
  id_disponibilidad INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_coach INT UNSIGNED NOT NULL,
  dia_semana ENUM('LUNES','MARTES','MIERCOLES','JUEVES','VIERNES','SABADO','DOMINGO') NOT NULL,
  hora_inicio TIME NOT NULL,
  hora_fin TIME NOT NULL,
  modalidad ENUM('VIRTUAL','PRESENCIAL','AMBOS') NOT NULL DEFAULT 'AMBOS',
  lugar VARCHAR(180) NULL,
  enlace_virtual VARCHAR(500) NULL,
  estado_disponibilidad ENUM('ACTIVA','INACTIVA','OCUPADA') NOT NULL DEFAULT 'ACTIVA',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT chk_disp_horas CHECK (hora_fin > hora_inicio),
  CONSTRAINT fk_disp_coach FOREIGN KEY (id_coach) REFERENCES coaches(id_coach) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `disponibilidades`;
SQL);
    }
};
