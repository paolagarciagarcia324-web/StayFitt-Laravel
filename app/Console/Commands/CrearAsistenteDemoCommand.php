<?php

namespace App\Console\Commands;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class CrearAsistenteDemoCommand extends Command
{
    protected $signature = 'staff:crear-demo
                            {--email=asistente@demo.figuefit.test : Correo}
                            {--password=demo12345 : Contraseña}
                            {--nombres=Andrea : Nombres}
                            {--apellidos=Soporte : Apellidos}';

    protected $description = 'Crea o repara usuario ASISTENTE_TECNICO listo para login';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->option('email')));
        $password = (string) $this->option('password');

        $idRol = DB::table('roles')->where('codigo', 'ASISTENTE_TECNICO')->orWhere('codigo_rol', 'ASISTENTE_TECNICO')->value('id_rol');
        if (! $idRol) {
            $idRol = DB::table('roles')->insertGetId([
                'codigo' => 'ASISTENTE_TECNICO',
                'codigo_rol' => 'ASISTENTE_TECNICO',
                'nombre' => 'Asistente Técnico',
                'estado' => 'ACTIVO',
            ]);
        }

        $user = User::query()->where('correo', $email)->orWhere('email', $email)->first();

        if (! $user) {
            $user = new User([
                'nombres' => (string) $this->option('nombres'),
                'apellidos' => (string) $this->option('apellidos'),
                'correo' => $email,
                'email' => $email,
                'telefono' => '3000000002',
                'estado' => 'ACTIVO',
                'estado_cuenta' => 'ACTIVO',
                'acepto_terminos' => true,
            ]);
            $user->assignPassword($password);
            $user->save();
        } else {
            DB::table('usuarios')->where('id_user', $user->id_user)->update([
                'password_hash' => Hash::make($password),
                'estado' => 'ACTIVO',
                'estado_cuenta' => 'ACTIVO',
                'actualizado_en' => now(),
            ]);
            $user->refresh();
        }

        if (! DB::table('user_roles')->where('id_user', $user->id_user)->where('id_rol', $idRol)->exists()) {
            DB::table('user_roles')->insert(['id_user' => $user->id_user, 'id_rol' => $idRol]);
        }

        foreach (['127.0.0.1', '::1'] as $ip) {
            RateLimiter::clear(Str::transliterate(Str::lower($email).'|'.$ip));
        }

        $dbName = config('database.connections.mysql.database');
        $loginService = app(\App\Services\Auth\LoginUsuarioService::class);
        $found = $loginService->findByLogin($email);
        $ok = $found && $loginService->credentialsMatch($found, $password);

        $this->info($ok ? 'Login verificado OK.' : 'Verificación falló.');
        $this->line("Base de datos: {$dbName}");
        $this->table(['Campo', 'Valor'], [
            ['Correo', $email],
            ['Contraseña', $password],
            ['Panel', url('/staff')],
        ]);

        return $ok ? self::SUCCESS : self::FAILURE;
    }
}
