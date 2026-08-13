<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE consultas_ia_nutricion (
  id_consulta_ia INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_cliente INT UNSIGNED NOT NULL,
  id_plan_nutricional INT UNSIGNED NULL,
  imagen_url VARCHAR(500) NOT NULL,
  mensaje_usuario TEXT NULL,
  respuesta_ia TEXT NULL,
  cumple_estructura TINYINT(1) NULL COMMENT '1=Carbo+Proteina+Grasa OK',
  calorias_estimadas INT UNSIGNED NULL,
  proteinas_estimadas DECIMAL(6,2) NULL,
  carbohidratos_estimados DECIMAL(6,2) NULL,
  grasas_estimadas DECIMAL(6,2) NULL,
  alimentos_detectados_json JSON NULL,
  alternativas_sugeridas_json JSON NULL,
  estado ENUM('PENDIENTE','ANALIZADA','ERROR') NOT NULL DEFAULT 'PENDIENTE',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_cia_cliente (id_cliente, creado_en),
  CONSTRAINT fk_cia_cliente FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_cia_pn FOREIGN KEY (id_plan_nutricional) REFERENCES planes_nutricionales(id_plan_nutricional) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `consultas_ia_nutricion`;
SQL);
    }
};
