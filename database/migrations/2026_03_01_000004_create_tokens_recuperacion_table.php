<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE tokens_recuperacion (
  id_token INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_user INT UNSIGNED NOT NULL,
  token VARCHAR(255) NOT NULL,
  tipo_token ENUM('PASSWORD_RESET','EMAIL_VERIFICACION','INVITACION','CAMBIO_EMAIL') NOT NULL DEFAULT 'PASSWORD_RESET',
  usado TINYINT(1) NOT NULL DEFAULT 0,
  expira_en DATETIME NOT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_tokens_token (token),
  KEY idx_tokens_user (id_user),
  CONSTRAINT fk_tokens_usuario
    FOREIGN KEY (id_user) REFERENCES usuarios(id_user)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS `tokens_recuperacion`;
SQL);
    }
};
