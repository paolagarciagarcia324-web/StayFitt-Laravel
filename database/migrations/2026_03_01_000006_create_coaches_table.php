<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE coaches (
  id_coach INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_user INT UNSIGNED NOT NULL,
  codigo_coach VARCHAR(20) NULL,
  especialidad VARCHAR(120) NULL,
  experiencia_anios TINYINT UNSIGNED NULL,
  certificaciones TEXT NULL,
  titulos TEXT NULL,
  biografia TEXT NULL,
  redes_sociales JSON NULL,
  tarifa_por_sesion DECIMAL(12,2) NULL,
  calificacion_promedio DECIMAL(2,1) NOT NULL DEFAULT 0.0,
  total_valoraciones INT UNSIGNED NOT NULL DEFAULT 0,
  horas_totales_sesiones INT UNSIGNED NOT NULL DEFAULT 0,
  clientes_atendidos INT UNSIGNED NOT NULL DEFAULT 0,
  estado_coach ENUM('ACTIVO','INACTIVO','OCUPADO') NOT NULL DEFAULT 'ACTIVO',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_coaches_user (id_user),
  UNIQUE KEY uq_coaches_codigo (codigo_coach),
  CONSTRAINT chk_coaches_experiencia CHECK (experiencia_anios IS NULL OR experiencia_anios <= 80),
  CONSTRAINT chk_coaches_calificacion CHECK (calificacion_promedio >= 0 AND calificacion_promedio <= 5),
  CONSTRAINT chk_coaches_tarifa CHECK (tarifa_por_sesion IS NULL OR tarifa_por_sesion >= 0),
  CONSTRAINT fk_coaches_usuario
    FOREIGN KEY (id_user) REFERENCES usuarios(id_user)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `coaches`;
SQL);
    }
};
