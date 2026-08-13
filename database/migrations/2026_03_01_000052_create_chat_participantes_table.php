<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE chat_participantes (
  id_chat_participante INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_chat INT UNSIGNED NOT NULL,
  id_user INT UNSIGNED NOT NULL,
  rol_en_chat ENUM('ADMIN','COACH','MIEMBRO') NOT NULL DEFAULT 'MIEMBRO',
  silenciado TINYINT(1) NOT NULL DEFAULT 0,
  unido_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_chat_user (id_chat, id_user),
  CONSTRAINT fk_cp_chat FOREIGN KEY (id_chat) REFERENCES chats(id_chat) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_cp_user FOREIGN KEY (id_user) REFERENCES usuarios(id_user) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `chat_participantes`;
SQL);
    }
};
