<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE planes_entrenamiento (
  id_plan_entrenamiento INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_cliente INT UNSIGNED NULL,
  id_plan INT UNSIGNED NULL,
  id_plan_cliente INT UNSIGNED NULL,
  id_coach INT UNSIGNED NULL,
  titulo VARCHAR(150) NOT NULL,
  objetivo TEXT NULL,
  nivel ENUM('PRINCIPIANTE','INTERMEDIO','AVANZADO') NOT NULL DEFAULT 'PRINCIPIANTE',
  modalidad ENUM('VIRTUAL','PRESENCIAL','MIXTO') NOT NULL DEFAULT 'VIRTUAL',
  semanas_duracion INT UNSIGNED NULL,
  dias_por_semana TINYINT UNSIGNED NULL,
  fecha_inicio DATE NULL,
  fecha_fin DATE NULL,
  estado_validacion ENUM('BORRADOR','PENDIENTE_ADMIN','VALIDADO','RECHAZADO') NOT NULL DEFAULT 'BORRADOR',
  validado_por INT UNSIGNED NULL,
  fecha_validacion DATETIME NULL,
  estado_entrenamiento ENUM('ACTIVO','INACTIVO','FINALIZADO') NOT NULL DEFAULT 'INACTIVO',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT chk_pe_semanas CHECK (semanas_duracion IS NULL OR semanas_duracion > 0),
  CONSTRAINT chk_pe_dias CHECK (dias_por_semana IS NULL OR dias_por_semana BETWEEN 1 AND 7),
  CONSTRAINT fk_pe_cliente FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_pe_plan FOREIGN KEY (id_plan) REFERENCES planes(id_plan) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_pe_pc FOREIGN KEY (id_plan_cliente) REFERENCES planes_cliente(id_plan_cliente) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_pe_coach FOREIGN KEY (id_coach) REFERENCES coaches(id_coach) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_pe_validado FOREIGN KEY (validado_por) REFERENCES usuarios(id_user) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `planes_entrenamiento`;
SQL);
    }
};
