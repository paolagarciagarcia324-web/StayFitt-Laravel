<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE ejercicios (
  id_ejercicio INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL,
  descripcion TEXT NULL,
  instrucciones TEXT NULL,
  consejos TEXT NULL,
  contraindicaciones TEXT NULL,
  tipo_ejercicio ENUM('FUERZA','CARDIO','FLEXIBILIDAD','EQUILIBRIO','HIIT','CIRCUITO','RECUPERACION','MOVILIDAD','OTRO') NOT NULL DEFAULT 'FUERZA',
  grupo_muscular VARCHAR(120) NULL,
  musculos_secundarios VARCHAR(255) NULL,
  equipo_necesario VARCHAR(150) NULL,
  dificultad ENUM('PRINCIPIANTE','INTERMEDIO','AVANZADO','TODOS') NOT NULL DEFAULT 'TODOS',
  url_video VARCHAR(500) NULL,
  imagen_url VARCHAR(500) NULL,
  calorias_quemadas_aprox INT UNSIGNED NULL,
  metabolico ENUM('COMPUESTO','AISLAMIENTO') NULL,
  estado_ejercicio ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_ejercicios_nombre (nombre)
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `ejercicios`;
SQL);
    }
};
