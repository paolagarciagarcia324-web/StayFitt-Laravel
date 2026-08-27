<?php

namespace App\Console\Commands;

use App\Models\AccesoClienteModulo;
use App\Models\Cliente;
use App\Models\Coach;
use App\Models\ModuloServicio;
use App\Models\Plan;
use App\Models\PlanCliente;
use App\Models\PreferenciaCliente;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CrearClientePresencialCommand extends Command
{
    protected $signature = 'cliente:crear-presencial
                            {--email=cliente.presencial@figuefit.test : Correo de acceso}
                            {--password=demo12345 : Contraseña}
                            {--nombres=Jean : Nombres}
                            {--apellidos=Presencial : Apellidos}';

    protected $description = 'Crea o actualiza un cliente PRESENCIAL listo para login';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->option('email')));
        $password = (string) $this->option('password');
        $nombres = (string) $this->option('nombres');
        $apellidos = (string) $this->option('apellidos');

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Correo inválido.');

            return self::FAILURE;
        }

        $rolCliente = Rol::query()->firstOrCreate(
            ['codigo' => 'CLIENTE'],
            ['codigo_rol' => 'CLIENTE', 'nombre' => 'Cliente', 'estado' => 'ACTIVO']
        );

        $coach = $this->ensureCoach();
        $plan = $this->ensurePlanPresencial();
        $horarioId = $this->ensureHorarioPresencial();

        $modulosPresencial = ['PERFIL', 'ENTRENAMIENTO', 'NUTRICION', 'PROGRESO', 'AGENDA', 'COMUNICACION', 'TIENDA', 'PAGOS'];

        $user = User::query()->where('correo', $email)->orWhere('email', $email)->first();

        if (! $user) {
            $user = User::make([
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'correo' => $email,
                'email' => $email,
                'telefono' => '3005550100',
                'documento_identidad' => '1098765432',
                'estado' => 'ACTIVO',
                'estado_cuenta' => 'ACTIVO',
                'acepto_terminos' => true,
            ]);
            $user->assignPassword($password);
            $user->save();
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
        }

        if (! $user->roles()->where('roles.id_rol', $rolCliente->id_rol)->exists()) {
            $user->roles()->attach($rolCliente->id_rol);
        }

        $cliente = Cliente::query()->firstOrNew(['id_user' => $user->id_user]);
        $cliente->fill([
            'id_coach' => $coach->id_coach,
            'id_coach_asignado' => $coach->id_coach,
            'id_horario' => $horarioId,
            'modalidad' => 'PRESENCIAL',
            'tipo_cliente' => 'INDIVIDUAL',
            'tipo_documento' => 'CC',
            'documento' => '1098765432',
            'fecha_nacimiento' => now()->subYears(28)->toDateString(),
            'genero' => 'MASCULINO',
            'ciudad' => 'Neiva',
            'peso_inicial_kg' => 78,
            'estatura_cm' => 175,
            'objetivo_principal' => 'Hipertrofia',
            'estado_cliente' => 'ACTIVO',
            'fecha_alta' => now()->toDateString(),
            'fecha_activacion' => now()->toDateString(),
        ]);
        $cliente->save();

        PreferenciaCliente::query()->updateOrCreate(
            ['id_cliente' => $cliente->id_cliente],
            [
                'notif_email' => true,
                'notif_push' => true,
                'notif_whatsapp' => true,
                'idioma' => 'es',
            ]
        );

        PlanCliente::query()
            ->where('id_cliente', $cliente->id_cliente)
            ->where('estado_plan_cliente', 'ACTIVO')
            ->update(['estado_plan_cliente' => 'VENCIDO']);

        $planCliente = PlanCliente::query()->create([
            'id_cliente' => $cliente->id_cliente,
            'id_plan' => $plan->id_plan,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addDays((int) ($plan->duracion_dias ?: 30))->toDateString(),
            'estado_plan_cliente' => 'ACTIVO',
        ]);

        foreach ($modulosPresencial as $codigo) {
            $modulo = ModuloServicio::query()->firstOrCreate(
                ['codigo' => $codigo],
                ['nombre' => $codigo, 'estado' => 'ACTIVO', 'orden' => 1]
            );

            $accesoData = [
                'id_plan_cliente' => $planCliente->id_plan_cliente,
                'id_cliente' => $cliente->id_cliente,
                'id_modulo' => $modulo->id_modulo,
                'fecha_inicio' => $planCliente->fecha_inicio,
                'fecha_fin' => $planCliente->fecha_fin,
                'estado_acceso' => 'ACTIVO',
            ];
            if (Schema::hasColumn('acceso_cliente_modulo', 'estado')) {
                $accesoData['estado'] = 'ACTIVO';
            }

            AccesoClienteModulo::query()->updateOrCreate(
                [
                    'id_cliente' => $cliente->id_cliente,
                    'id_modulo' => $modulo->id_modulo,
                ],
                $accesoData
            );
        }

        RateLimiter::clear(Str::transliterate(Str::lower($email).'|127.0.0.1'));
        RateLimiter::clear(Str::transliterate(Str::lower($email).'|::1'));

        $this->newLine();
        $this->info('Cliente presencial creado / actualizado.');
        $this->table(
            ['Campo', 'Valor'],
            [
                ['Correo', $email],
                ['Contraseña', $password],
                ['Nombre', "{$nombres} {$apellidos}"],
                ['Modalidad', 'PRESENCIAL'],
                ['Plan', $plan->nombre],
                ['Coach', '#'.$coach->id_coach],
                ['Login', url('/login')],
                ['Ficha', url('/cliente/presencial')],
            ]
        );

        return self::SUCCESS;
    }

    private function ensureCoach(): Coach
    {
        $coach = Coach::query()
            ->where('estado_coach', 'ACTIVO')
            ->orderBy('id_coach')
            ->first();

        if ($coach) {
            return $coach;
        }

        $rolCoach = Rol::query()->firstOrCreate(
            ['codigo' => 'COACH'],
            ['codigo_rol' => 'COACH', 'nombre' => 'Coach', 'estado' => 'ACTIVO']
        );

        $emailCoach = 'coach.bootstrap@figuefit.test';
        $userCoach = User::query()->where('correo', $emailCoach)->first();

        if (! $userCoach) {
            $userCoach = User::make([
                'nombres' => 'Coach',
                'apellidos' => 'FigueFit',
                'correo' => $emailCoach,
                'email' => $emailCoach,
                'estado' => 'ACTIVO',
                'estado_cuenta' => 'ACTIVO',
                'acepto_terminos' => true,
            ]);
            $userCoach->assignPassword('coach12345');
            $userCoach->save();
            $userCoach->roles()->syncWithoutDetaching([$rolCoach->id_rol]);
        }

        return Coach::query()->create([
            'id_user' => $userCoach->id_user,
            'codigo_coach' => 'C-BOOT-01',
            'especialidad' => 'Sala presencial',
            'estado_coach' => 'ACTIVO',
        ]);
    }

    private function ensurePlanPresencial(): Plan
    {
        $plan = Plan::query()
            ->where('modalidad', 'PRESENCIAL')
            ->where(function ($q) {
                $q->where('estado_plan', 'ACTIVO')->orWhere('estado', 'ACTIVO');
            })
            ->orderBy('id_plan')
            ->first();

        if ($plan) {
            return $plan;
        }

        return Plan::query()->create([
            'nombre' => 'FigueFit Presencial Sala',
            'slug' => 'figuefit-presencial-sala-'.Str::lower(Str::random(6)),
            'descripcion' => 'Acceso a sala, coach y módulos presenciales.',
            'modalidad' => 'PRESENCIAL',
            'precio' => 180000,
            'duracion_dias' => 30,
            'estado' => 'ACTIVO',
            'estado_plan' => 'ACTIVO',
        ]);
    }

    private function ensureHorarioPresencial(): ?int
    {
        $horarioId = DB::table('horarios_presenciales')
            ->where('activo', true)
            ->orderBy('id_horario')
            ->value('id_horario');

        if ($horarioId) {
            return (int) $horarioId;
        }

        if (! Schema::hasTable('horarios_presenciales')) {
            return null;
        }

        return (int) DB::table('horarios_presenciales')->insertGetId([
            'codigo' => 'PM-BOOT',
            'nombre' => 'Tarde boot',
            'dias' => 'L-V',
            'hora_inicio' => '17:00:00',
            'hora_fin' => '19:00:00',
            'cupo_maximo' => 20,
            'cupo_ocupado' => 0,
            'activo' => true,
            'orden' => 1,
            'creado_en' => now(),
        ]);
    }
}
