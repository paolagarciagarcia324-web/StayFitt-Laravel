<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE planes_nutricionales (
  id_plan_nutricional INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_cliente INT UNSIGNED NOT NULL,
  id_coach INT UNSIGNED NULL,
  id_plan_cliente INT UNSIGNED NULL,
  titulo VARCHAR(150) NOT NULL,
  objetivo TEXT NULL,
  calorias_diarias INT UNSIGNED NULL,
  macros_proteinas_gramos DECIMAL(6,2) NULL,
  macros_carbohidratos_gramos DECIMAL(6,2) NULL,
  macros_grasas_gramos DECIMAL(6,2) NULL,
  recomendaciones_generales TEXT NULL,
  restricciones TEXT NULL,
  alergenos TEXT NULL,
  fecha_inicio DATE NULL,
  fecha_fin DATE NULL,
  estado_nutricional ENUM('ACTIVO','INACTIVO','FINALIZADO') NOT NULL DEFAULT 'ACTIVO',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_pn_cliente FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_pn_coach FOREIGN KEY (id_coach) REFERENCES coaches(id_coach) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_pn_pc FOREIGN KEY (id_plan_cliente) REFERENCES planes_cliente(id_plan_cliente) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `planes_nutricionales`;
SQL);
    }
};
