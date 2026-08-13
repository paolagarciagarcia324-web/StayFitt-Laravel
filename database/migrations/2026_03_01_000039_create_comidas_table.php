<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE comidas (
  id_comida INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_plan_nutricional INT UNSIGNED NOT NULL,
  tipo_comida ENUM('DESAYUNO','MEDIA_MANANA','ALMUERZO','MERIENDA','CENA','SNACK','POSTRE','BATIDO','OTRO') NOT NULL,
  nombre VARCHAR(150) NOT NULL,
  descripcion TEXT NULL,
  receta TEXT NULL,
  porcion_descripcion VARCHAR(100) NULL,
  porcion_gramos INT UNSIGNED NULL,
  hora_sugerida TIME NULL,
  imagen_url VARCHAR(500) NULL,
  video_url VARCHAR(500) NULL,
  calorias_aprox INT UNSIGNED NULL,
  proteinas_gramos DECIMAL(6,2) NULL,
  carbohidratos_gramos DECIMAL(6,2) NULL,
  grasas_gramos DECIMAL(6,2) NULL,
  fibra_gramos DECIMAL(6,2) NULL,
  azucar_gramos DECIMAL(6,2) NULL,
  sodio_mg INT UNSIGNED NULL,
  orden INT NOT NULL DEFAULT 0,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_comidas_plan (id_plan_nutricional, tipo_comida),
  CONSTRAINT fk_comidas_pn FOREIGN KEY (id_plan_nutricional) REFERENCES planes_nutricionales(id_plan_nutricional) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `comidas`;
SQL);
    }
};
