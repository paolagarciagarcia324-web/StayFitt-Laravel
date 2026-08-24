<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('roles')
            ->where('codigo', 'ASISTENTE_TECNICO')
            ->orWhere('codigo_rol', 'ASISTENTE_TECNICO')
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('roles')->insert([
            'codigo' => 'ASISTENTE_TECNICO',
            'codigo_rol' => 'ASISTENTE_TECNICO',
            'nombre' => 'Asistente Técnico de Coach',
            'descripcion' => 'Apoyo operativo al coach: rutinas, videos, propuestas y novedades welcome',
            'estado' => 'ACTIVO',
        ]);
    }

    public function down(): void
    {
        $rol = DB::table('roles')
            ->where('codigo', 'ASISTENTE_TECNICO')
            ->orWhere('codigo_rol', 'ASISTENTE_TECNICO')
            ->first();

        if (! $rol) {
            return;
        }

        DB::table('user_roles')->where('id_rol', $rol->id_rol)->delete();
        DB::table('roles')->where('id_rol', $rol->id_rol)->delete();
    }
};
