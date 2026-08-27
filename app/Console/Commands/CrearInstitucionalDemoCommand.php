<?php

namespace App\Console\Commands;

use App\Models\Institucion;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class CrearInstitucionalDemoCommand extends Command
{
    protected $signature = 'institucional:crear-demo
                            {--email=rrhh@campoalegre.demo.figuefit.test : Correo responsable}
                            {--password=demo12345 : Contraseña}
                            {--institucion=Colegio Campoalegre : Nombre institución}';

    protected $description = 'Crea usuario RESPONSABLE_INSTITUCIONAL vinculado a una institución demo';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->option('email')));
        $password = (string) $this->option('password');
        $nombreInst = (string) $this->option('institucion');

        $idRol = DB::table('roles')
            ->where('codigo', 'RESPONSABLE_INSTITUCIONAL')
            ->orWhere('codigo_rol', 'RESPONSABLE_INSTITUCIONAL')
            ->value('id_rol');

        if (! $idRol) {
            $idRol = DB::table('roles')->insertGetId([
                'codigo' => 'RESPONSABLE_INSTITUCIONAL',
                'codigo_rol' => 'RESPONSABLE_INSTITUCIONAL',
                'nombre' => 'Responsable Institucional',
                'descripcion' => 'Portal B2B convenios',
                'estado' => 'ACTIVO',
            ]);
        }

        $institucion = Institucion::query()->where('nombre', 'like', '%'.$nombreInst.'%')->first();
        if (! $institucion) {
            $institucion = Institucion::query()->orderBy('id_institucion')->first();
        }

        if (! $institucion) {
            $this->error('No hay instituciones en la base de datos. Ejecuta los seeders primero.');

            return self::FAILURE;
        }

        $planId = DB::table('planes')
            ->where(function ($q) {
                $q->where('tipo_cliente', 'INSTITUCIONAL')->orWhere('tipo_cliente', 'AMBOS');
            })
            ->orderBy('id_plan')
            ->value('id_plan');

        $institucion->update(array_filter([
            'tipo_institucion' => $institucion->tipo_institucion ?: 'EDUCATIVA',
            'contacto_cargo' => $institucion->contacto_cargo ?: 'Coordinadora RR.HH.',
            'id_plan_contratado' => $institucion->id_plan_contratado ?: $planId,
            'fecha_inicio' => $institucion->fecha_inicio ?: now()->subMonths(2),
            'fecha_fin' => $institucion->fecha_fin ?: now()->addYear(),
            'actualizado_en' => now(),
        ]));

        $user = User::query()->where('correo', $email)->orWhere('email', $email)->first();

        if (! $user) {
            $user = new User([
                'nombres' => 'Laura',
                'apellidos' => 'Méndez',
                'correo' => $email,
                'email' => $email,
                'telefono' => '3185550100',
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

        $institucion->update([
            'id_user_responsable' => $user->id_user,
            'contacto_nombre' => trim($user->nombres.' '.$user->apellidos),
            'correo' => $email,
            'actualizado_en' => now(),
        ]);

        foreach (['127.0.0.1', '::1'] as $ip) {
            RateLimiter::clear(Str::transliterate(Str::lower($email).'|'.$ip));
        }

        $loginService = app(\App\Services\Auth\LoginUsuarioService::class);
        $found = $loginService->findByLogin($email);
        $ok = $found && $loginService->credentialsMatch($found, $password);

        $this->info($ok ? 'Login verificado OK.' : 'Verificación falló.');
        $this->line('Base de datos: '.config('database.connections.mysql.database'));
        $this->table(['Campo', 'Valor'], [
            ['Correo', $email],
            ['Contraseña', $password],
            ['Institución', $institucion->nombre],
            ['Panel', url('/institucional')],
        ]);

        return $ok ? self::SUCCESS : self::FAILURE;
    }
}
