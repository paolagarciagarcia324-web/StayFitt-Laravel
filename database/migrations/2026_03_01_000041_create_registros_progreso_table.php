<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE registros_progreso (
  id_registro_progreso INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_cliente INT UNSIGNED NOT NULL,
  id_plan_cliente INT UNSIGNED NULL,
  registrado_por INT UNSIGNED NULL,
  fecha_registro DATE NOT NULL,
  peso_kg DECIMAL(5,2) NULL,
  porcentaje_grasa DECIMAL(4,1) NULL,
  masa_muscular_kg DECIMAL(5,2) NULL,
  masa_grasa_kg DECIMAL(5,2) NULL,
  imc DECIMAL(4,1) NULL,
  cuello_cm DECIMAL(4,1) NULL,
  pecho_cm DECIMAL(5,2) NULL,
  brazo_cm DECIMAL(4,1) NULL,
  cintura_cm DECIMAL(5,2) NULL,
  abdomen_cm DECIMAL(5,2) NULL,
  cadera_cm DECIMAL(5,2) NULL,
  gluteo_cm DECIMAL(5,2) NULL,
  pierna_cm DECIMAL(5,2) NULL,
  gemelo_cm DECIMAL(4,1) NULL,
  frecuencia_cardiaca_reposo SMALLINT UNSIGNED NULL,
  nivel_energia ENUM('MUY_BAJO','BAJO','NORMAL','ALTO','MUY_ALTO') NULL,
  calidad_sueno ENUM('PESIMO','MALO','REGULAR','BUENO','EXCELENTE') NULL,
  nivel_estres ENUM('MUY_BAJO','BAJO','NORMAL','ALTO','MUY_ALTO') NULL,
  observaciones_cliente TEXT NULL,
  observaciones_coach TEXT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_progreso_cliente_fecha (id_cliente, fecha_registro),
  KEY idx_progreso_plan (id_plan_cliente),
  CONSTRAINT chk_prog_peso CHECK (peso_kg IS NULL OR (peso_kg >= 20 AND peso_kg <= 400)),
  CONSTRAINT chk_prog_grasa CHECK (porcentaje_grasa IS NULL OR porcentaje_grasa <= 100),
  CONSTRAINT fk_prog_cliente FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_prog_pc FOREIGN KEY (id_plan_cliente) REFERENCES planes_cliente(id_plan_cliente) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_prog_user FOREIGN KEY (registrado_por) REFERENCES usuarios(id_user) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `registros_progreso`;
SQL);
    }
};
