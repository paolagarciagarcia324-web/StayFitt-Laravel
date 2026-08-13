<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE usuarios (
  id_user INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombres VARCHAR(100) NOT NULL,
  apellidos VARCHAR(100) NOT NULL,
  Tipo_documento VARCHAR(30) NULL,
  documento_identidad VARCHAR(30) NULL,
  correo VARCHAR(150) NOT NULL,
  codigo_pais VARCHAR(5) NOT NULL DEFAULT '+57',
  telefono VARCHAR(30) NULL,
  password_hash VARCHAR(255) NOT NULL,
  foto_perfil_url VARCHAR(500) NULL,
  idioma VARCHAR(10) NOT NULL DEFAULT 'es',
  zona_horaria VARCHAR(50) NOT NULL DEFAULT 'America/Bogota',
  acepto_terminos TINYINT(1) NOT NULL DEFAULT 0,
  fecha_aceptacion_terminos DATETIME NULL,
  notificaciones_push_token TEXT NULL,
  estado ENUM('ACTIVO','INACTIVO','BLOQUEADO','PENDIENTE') NOT NULL DEFAULT 'PENDIENTE',
  correo_verificado_en DATETIME NULL,
  ultimo_acceso_en DATETIME NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_usuarios_correo (correo),
  UNIQUE KEY uq_usuarios_documento (documento_identidad),
  CONSTRAINT chk_usuarios_terminos CHECK (
    (acepto_terminos = 0 AND fecha_aceptacion_terminos IS NULL)
    OR (acepto_terminos = 1 AND fecha_aceptacion_terminos IS NOT NULL)
  )
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `usuarios`;
SQL);
    }
};
