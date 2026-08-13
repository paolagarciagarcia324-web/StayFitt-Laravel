<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE mensajes (
  id_mensaje INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  uuid_mensaje CHAR(36) NOT NULL,
  id_chat INT UNSIGNED NOT NULL,
  id_user INT UNSIGNED NOT NULL,
  tipo_mensaje ENUM('TEXTO','IMAGEN','ARCHIVO','SISTEMA','UBICACION') NOT NULL DEFAULT 'TEXTO',
  mensaje TEXT NOT NULL,
  archivo_url VARCHAR(500) NULL,
  leido TINYINT(1) NOT NULL DEFAULT 0,
  fecha_lectura DATETIME NULL,
  editado TINYINT(1) NOT NULL DEFAULT 0,
  editado_en DATETIME NULL,
  eliminado_para_todos TINYINT(1) NOT NULL DEFAULT 0,
  reenviado_de INT UNSIGNED NULL,
  respondiendo_a INT UNSIGNED NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_mensajes_uuid (uuid_mensaje),
  KEY idx_mensajes_chat (id_chat, creado_en),
  CONSTRAINT fk_msg_chat FOREIGN KEY (id_chat) REFERENCES chats(id_chat) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_msg_user FOREIGN KEY (id_user) REFERENCES usuarios(id_user) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_msg_reenv FOREIGN KEY (reenviado_de) REFERENCES mensajes(id_mensaje) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_msg_resp FOREIGN KEY (respondiendo_a) REFERENCES mensajes(id_mensaje) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `mensajes`;
SQL);
    }
};
