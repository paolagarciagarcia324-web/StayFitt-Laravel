<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE instituciones (
  id_institucion INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo_institucion VARCHAR(20) NULL,
  nombre VARCHAR(150) NOT NULL,
  nit VARCHAR(50) NULL,
  tipo_institucion VARCHAR(80) NULL,
  nombre_contacto VARCHAR(120) NULL,
  correo_contacto VARCHAR(150) NULL,
  telefono_contacto VARCHAR(30) NULL,
  direccion VARCHAR(180) NULL,
  ciudad VARCHAR(100) NULL,
  logo_url VARCHAR(500) NULL,
  pagina_web VARCHAR(255) NULL,
  max_clientes INT UNSIGNED NULL,
  observaciones TEXT NULL,
  estado ENUM('ACTIVA','INACTIVA') NOT NULL DEFAULT 'ACTIVA',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_instituciones_codigo (codigo_institucion),
  UNIQUE KEY uq_instituciones_nit (nit),
  CONSTRAINT chk_instituciones_max_clientes CHECK (max_clientes IS NULL OR max_clientes > 0)
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `instituciones`;
SQL);
    }
};
