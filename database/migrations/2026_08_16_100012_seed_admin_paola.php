<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        $correo = 'paola@correo.com';
        $password = 'paola12345';

        $idUser = DB::table('usuarios')->where('correo', $correo)->value('id_user')
            ?? DB::table('usuarios')->where('correo', 'paola@correo')->value('id_user');

        $payload = [
            'nombres' => 'Paola',
            'apellidos' => 'Garcia',
            'correo' => $correo,
            'email' => $correo,
            'password_hash' => Hash::make($password),
            'foto_perfil_url' => 'images/admin/paola-avatar.png',
            'estado' => 'ACTIVO',
            'estado_cuenta' => 'ACTIVO',
            'acepto_terminos' => 1,
            'fecha_aceptacion_terminos' => now(),
            'correo_verificado_en' => now(),
            'actualizado_en' => now(),
        ];

        if ($idUser) {
            DB::table('usuarios')->where('id_user', $idUser)->update($payload);
        } else {
            $payload['creado_en'] = now();
            $idUser = DB::table('usuarios')->insertGetId($payload);
        }

        $idRolAdmin = DB::table('roles')->where('codigo', 'ADMIN')->value('id_rol');

        if ($idRolAdmin && $idUser) {
            $exists = DB::table('user_roles')
                ->where('id_user', $idUser)
                ->where('id_rol', $idRolAdmin)
                ->exists();

            if (! $exists) {
                DB::table('user_roles')->insert([
                    'id_user' => $idUser,
                    'id_rol' => $idRolAdmin,
                    'asignado_en' => now(),
                    'creado_en' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        $idUser = DB::table('usuarios')->where('correo', 'paola@correo.com')->value('id_user');

        if ($idUser) {
            DB::table('user_roles')->where('id_user', $idUser)->delete();
            DB::table('usuarios')->where('id_user', $idUser)->delete();
        }
    }
};
