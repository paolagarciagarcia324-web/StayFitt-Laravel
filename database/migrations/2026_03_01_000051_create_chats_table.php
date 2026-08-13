<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE chats (
  id_chat INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  uuid_chat CHAR(36) NOT NULL,
  tipo_chat ENUM('CLIENTE_COACH','COMUNIDAD_PLAN','COMUNIDAD_HORARIO','SOPORTE','INSTITUCIONAL','COACHES_ADMIN') NOT NULL DEFAULT 'CLIENTE_COACH',
  id_cliente INT UNSIGNED NULL,
  id_coach INT UNSIGNED NULL,
  id_institucion INT UNSIGNED NULL,
  id_plan INT UNSIGNED NULL,
  id_horario INT UNSIGNED NULL,
  asunto VARCHAR(160) NULL,
  id_ultimo_mensaje INT UNSIGNED NULL,
  ultimo_mensaje_texto VARCHAR(200) NULL,
  ultimo_mensaje_fecha DATETIME NULL,
  estado_chat ENUM('ACTIVO','CERRADO','ARCHIVADO') NOT NULL DEFAULT 'ACTIVO',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_chats_uuid (uuid_chat),
  KEY idx_chats_tipo (tipo_chat, estado_chat),
  CONSTRAINT fk_chats_cliente FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_chats_coach FOREIGN KEY (id_coach) REFERENCES coaches(id_coach) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_chats_inst FOREIGN KEY (id_institucion) REFERENCES instituciones(id_institucion) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_chats_plan FOREIGN KEY (id_plan) REFERENCES planes(id_plan) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_chats_horario FOREIGN KEY (id_horario) REFERENCES horarios(id_horario) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `chats`;
SQL);
    }
};
