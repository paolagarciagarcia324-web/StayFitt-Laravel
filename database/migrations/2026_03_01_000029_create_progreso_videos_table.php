<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE progreso_videos (
  id_progreso_video INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_cliente INT UNSIGNED NOT NULL,
  id_video INT UNSIGNED NOT NULL,
  id_plan_cliente INT UNSIGNED NULL,
  estado_progreso ENUM('BLOQUEADO','DISPONIBLE','EN_PROGRESO','COMPLETADO') NOT NULL DEFAULT 'BLOQUEADO',
  porcentaje_avance DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  ultima_posicion_segundos INT UNSIGNED NULL,
  series_completadas TINYINT UNSIGNED NOT NULL DEFAULT 0,
  repeticiones_correctas INT UNSIGNED NOT NULL DEFAULT 0,
  repeticiones_incorrectas INT UNSIGNED NOT NULL DEFAULT 0,
  validacion_ia_ok TINYINT(1) NOT NULL DEFAULT 0,
  detalle_ia_json JSON NULL,
  iniciado_en DATETIME NULL,
  completado_en DATETIME NULL,
  genera_racha TINYINT(1) NOT NULL DEFAULT 0,
  veces_visto INT UNSIGNED NOT NULL DEFAULT 0,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_cliente_video (id_cliente, id_video),
  CONSTRAINT chk_pv_porcentaje CHECK (porcentaje_avance >= 0 AND porcentaje_avance <= 100),
  CONSTRAINT fk_pv_cliente
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_pv_video
    FOREIGN KEY (id_video) REFERENCES videos(id_video)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_pv_plan_cliente
    FOREIGN KEY (id_plan_cliente) REFERENCES planes_cliente(id_plan_cliente)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `progreso_videos`;
SQL);
    }
};
