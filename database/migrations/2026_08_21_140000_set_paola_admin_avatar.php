<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('usuarios')
            ->where('correo', 'paola@correo.com')
            ->update([
                'foto_perfil_url' => 'images/admin/paola-avatar.png',
                'actualizado_en' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('usuarios')
            ->where('correo', 'paola@correo.com')
            ->where('foto_perfil_url', 'images/admin/paola-avatar.png')
            ->update([
                'foto_perfil_url' => null,
                'actualizado_en' => now(),
            ]);
    }
};
