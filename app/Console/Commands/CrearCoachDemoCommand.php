<?php

namespace App\Console\Commands;

use App\Models\Coach;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class CrearCoachDemoCommand extends Command
{
    protected $signature = 'coach:crear-demo
                            {--email=laura.coach@demo.figuefit.test : Correo del coach}
                            {--password=demo12345 : Contraseña}
                            {--nombres=Laura : Nombres}
                            {--apellidos=Sánchez : Apellidos}
                            {--especialidad=Fuerza y funcional : Especialidad}';

    protected $description = 'Crea o repara un usuario COACH listo para login (demo/producción local)';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->option('email')));
        $password = (string) $this->option('password');
        $nombres = (string) $this->option('nombres');
        $apellidos = (string) $this->option('apellidos');
        $especialidad = (string) $this->option('especialidad');

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Correo inválido.');

            return self::FAILURE;
        }

        $rolCoach = Rol::query()->firstOrCreate(
            ['codigo' => 'COACH'],
            ['codigo_rol' => 'COACH', 'nombre' => 'Coach', 'estado' => 'ACTIVO']
        );

        $user = User::query()
            ->where('correo', $email)
            ->orWhere('email', $email)
            ->first();

        if (! $user) {
            $user = User::make([
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'correo' => $email,
                'email' => $email,
                'telefono' => '3011000010',
                'documento_identidad' => '1100000000',
                'estado' => 'ACTIVO',
                'estado_cuenta' => 'ACTIVO',
                'acepto_terminos' => true,
            ]);
            $user->assignPassword($password);
            $user->save();
            $this->line('Usuario coach creado.');
        } else {
            $user->update([
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'correo' => $email,
                'email' => $email,
                'estado' => 'ACTIVO',
                'estado_cuenta' => 'ACTIVO',
            ]);
            DB::table('usuarios')
                ->where('id_user', $user->id_user)
                ->update([
                    'password_hash' => Hash::make($password),
                    'actualizado_en' => now(),
                ]);
            $user->refresh();
            $this->line('Usuario coach actualizado (contraseña restablecida).');
        }

        if (! $user->roles()->where('roles.id_rol', $rolCoach->id_rol)->exists()) {
            $user->roles()->attach($rolCoach->id_rol);
        }

        $coach = Coach::query()->where('id_user', $user->id_user)->first();

        if (! $coach) {
            $coach = Coach::query()->create([
                'id_user' => $user->id_user,
                'codigo_coach' => 'COACH-DEMO-'.Str::upper(Str::random(4)),
                'especialidad' => $especialidad,
                'biografia' => "Coach FigueFit — {$especialidad}.",
                'certificaciones' => 'NSCA-CPT, First Aid',
                'estado_coach' => 'ACTIVO',
            ]);
            $this->line('Perfil coach creado.');
        } else {
            $coach->update([
                'especialidad' => $especialidad,
                'estado_coach' => 'ACTIVO',
            ]);
        }

        foreach (['127.0.0.1', '::1', 'localhost'] as $ip) {
            RateLimiter::clear(Str::transliterate(Str::lower($email).'|'.$ip));
        }

        // Limpiar bloqueo por demasiados intentos (cualquier IP reciente en caché)
        try {
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
        } catch (\Throwable) {
            // ignore
        }

        $dbName = config('database.connections.mysql.database');
        $loginService = app(\App\Services\Auth\LoginUsuarioService::class);
        $found = $loginService->findByLogin($email);
        $passOk = $found && $loginService->credentialsMatch($found, $password);

        $this->newLine();
        $this->info($passOk ? 'Login verificado OK.' : 'AVISO: verificación de login falló — revisa BD.');
        $this->line("Base de datos activa: <comment>{$dbName}</comment>");
        $this->table(
            ['Campo', 'Valor'],
            [
                ['Correo', $email],
                ['Contraseña', $password],
                ['Nombre', "{$nombres} {$apellidos}"],
                ['Coach ID', '#'.$coach->id_coach],
                ['Login', url('/login')],
                ['Panel', url('/coach')],
            ]
        );

        return $passOk ? self::SUCCESS : self::FAILURE;
    }
}
