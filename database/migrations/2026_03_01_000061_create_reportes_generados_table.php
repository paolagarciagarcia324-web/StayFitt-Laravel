<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE reportes_generados (
  id_reporte INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_user INT UNSIGNED NULL,
  tipo_reporte ENUM('CLIENTES','PAGOS','SOLICITUDES','PLANES','PROGRESO','EVENTOS','INGRESOS','ENTRENAMIENTO','NUTRICION','ASISTENCIA','VIDEOS','TIENDA','GENERAL') NOT NULL DEFAULT 'GENERAL',
  nombre_reporte VARCHAR(160) NOT NULL,
  parametros_json JSON NULL,
  fecha_inicio DATE NULL,
  fecha_fin DATE NULL,
  formato ENUM('PDF','EXCEL','CSV','JSON') NOT NULL DEFAULT 'PDF',
  estado_reporte ENUM('PROCESANDO','COMPLETADO','ERROR') NOT NULL DEFAULT 'PROCESANDO',
  progreso TINYINT UNSIGNED NOT NULL DEFAULT 0,
  total_registros INT UNSIGNED NULL,
  archivo_url VARCHAR(500) NULL,
  generado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT chk_rep_prog CHECK (progreso <= 100),
  CONSTRAINT fk_rep_user FOREIGN KEY (id_user) REFERENCES usuarios(id_user) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `reportes_generados`;
SQL);
    }
};
