<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('usuarios')->where('correo', 'admin@figuefit.com')->exists();
        if ($exists) {
            return;
        }

        $idUser = DB::table('usuarios')->insertGetId([
            'nombres' => 'Admin',
            'apellidos' => 'FigueFit',
            'correo' => 'admin@figuefit.com',
            'password_hash' => Hash::make('Admin123!'),
            'acepto_terminos' => 1,
            'fecha_aceptacion_terminos' => now(),
            'estado' => 'ACTIVO',
            'correo_verificado_en' => now(),
        ]);

        $idRol = DB::table('roles')->where('codigo', 'ADMIN')->value('id_rol');
        if ($idRol) {
            DB::table('user_roles')->insert([
                'id_user' => $idUser,
                'id_rol' => $idRol,
            ]);
        }
    }

    public function down(): void
    {
        $idUser = DB::table('usuarios')->where('correo', 'admin@figuefit.com')->value('id_user');
        if ($idUser) {
            DB::table('user_roles')->where('id_user', $idUser)->delete();
            DB::table('usuarios')->where('id_user', $idUser)->delete();
        }
    }
};
