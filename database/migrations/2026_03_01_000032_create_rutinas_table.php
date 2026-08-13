<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE rutinas (
  id_rutina INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_plan_entrenamiento INT UNSIGNED NOT NULL,
  titulo VARCHAR(150) NOT NULL,
  descripcion TEXT NULL,
  dia_semana ENUM('LUNES','MARTES','MIERCOLES','JUEVES','VIERNES','SABADO','DOMINGO') NULL,
  tiempo_estimado_minutos INT UNSIGNED NULL,
  calentamiento TEXT NULL,
  notas_cliente TEXT NULL,
  orden INT NOT NULL DEFAULT 0,
  estado_rutina ENUM('ACTIVA','INACTIVA') NOT NULL DEFAULT 'ACTIVA',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_rutinas_pe
    FOREIGN KEY (id_plan_entrenamiento) REFERENCES planes_entrenamiento(id_plan_entrenamiento)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `rutinas`;
SQL);
    }
};
