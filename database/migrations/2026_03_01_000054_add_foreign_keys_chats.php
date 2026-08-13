<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
ALTER TABLE chats
  ADD CONSTRAINT fk_chats_ultimo_msg
    FOREIGN KEY (id_ultimo_mensaje) REFERENCES mensajes(id_mensaje)
    ON DELETE SET NULL ON UPDATE CASCADE;
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
ALTER TABLE `chats` DROP FOREIGN KEY `fk_chats_ultimo_msg`;
SQL);
    }
};
