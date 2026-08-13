<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE recordatorios (
  id_recordatorio INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_cliente INT UNSIGNED NOT NULL,
  id_coach INT UNSIGNED NULL,
  id_plan_cliente INT UNSIGNED NULL,
  titulo VARCHAR(160) NOT NULL,
  descripcion TEXT NULL,
  tipo_recordatorio ENUM('ENTRENAMIENTO','COMIDA','SESION','EVENTO','MEDICION','PAGO','VIDEO','ASISTENCIA','GENERAL') NOT NULL DEFAULT 'GENERAL',
  fecha_hora_programada DATETIME NOT NULL,
  fecha_hora_notificado DATETIME NULL,
  recurrencia ENUM('UNICO','DIARIO','SEMANAL','MENSUAL','DIAS_ESPECIFICOS') NOT NULL DEFAULT 'UNICO',
  dia_semana_recurrencia SET('LUNES','MARTES','MIERCOLES','JUEVES','VIERNES','SABADO','DOMINGO') NULL,
  hora_recurrencia TIME NULL,
  notificacion_push TINYINT(1) NOT NULL DEFAULT 1,
  notificacion_email TINYINT(1) NOT NULL DEFAULT 0,
  estado_recordatorio ENUM('ACTIVO','INACTIVO','COMPLETADO','VENCIDO') NOT NULL DEFAULT 'ACTIVO',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_rec_fecha (fecha_hora_programada, estado_recordatorio),
  CONSTRAINT fk_rec_cliente FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_rec_coach FOREIGN KEY (id_coach) REFERENCES coaches(id_coach) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_rec_pc FOREIGN KEY (id_plan_cliente) REFERENCES planes_cliente(id_plan_cliente) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `recordatorios`;
SQL);
    }
};
