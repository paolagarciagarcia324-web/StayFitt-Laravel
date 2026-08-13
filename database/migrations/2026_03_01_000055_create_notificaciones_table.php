<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE notificaciones (
  id_notificacion INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  uuid_notificacion CHAR(36) NOT NULL,
  id_user INT UNSIGNED NOT NULL,
  titulo VARCHAR(160) NOT NULL,
  mensaje TEXT NOT NULL,
  tipo_notificacion ENUM('SISTEMA','PAGO','PLAN','AGENDA','CHAT','EVENTO','PROGRESO','META','RECORDATORIO','NUTRICION','VIDEO','RACHA','TIENDA','DEVOLUCION') NOT NULL DEFAULT 'SISTEMA',
  prioridad ENUM('BAJA','NORMAL','ALTA','URGENTE') NOT NULL DEFAULT 'NORMAL',
  canal_envio ENUM('PUSH','EMAIL','AMBOS') NOT NULL DEFAULT 'PUSH',
  url_destino VARCHAR(500) NULL,
  leida TINYINT(1) NOT NULL DEFAULT 0,
  fecha_lectura DATETIME NULL,
  fecha_envio DATETIME NULL,
  error_envio TEXT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_notif_uuid (uuid_notificacion),
  KEY idx_notif_user (id_user, leida, creado_en),
  CONSTRAINT fk_notif_user FOREIGN KEY (id_user) REFERENCES usuarios(id_user) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `notificaciones`;
SQL);
    }
};
