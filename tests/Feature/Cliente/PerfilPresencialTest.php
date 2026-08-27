<?php

use App\Models\AccesoClienteModulo;
use App\Models\Asistencia;
use App\Models\Cliente;
use App\Models\Coach;
use App\Models\ModuloServicio;
use App\Models\Pago;
use App\Models\Plan;
use App\Models\PlanCliente;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createClientePresencialDemo(array $codigos = ['PROGRESO', 'ENTRENAMIENTO', 'TIENDA']): array
{
    $rolCliente = Rol::query()->firstOrCreate(
        ['codigo' => 'CLIENTE'],
        ['codigo_rol' => 'CLIENTE', 'nombre' => 'Cliente', 'estado' => 'ACTIVO']
    );
    $rolCoach = Rol::query()->firstOrCreate(
        ['codigo' => 'COACH'],
        ['codigo_rol' => 'COACH', 'nombre' => 'Coach', 'estado' => 'ACTIVO']
    );

    $coachUser = User::make([
        'nombres' => 'Coach',
        'apellidos' => 'Sala',
        'correo' => 'coach.presencial@test.local',
        'email' => 'coach.presencial@test.local',
        'estado' => 'ACTIVO',
        'estado_cuenta' => 'ACTIVO',
        'acepto_terminos' => true,
    ]);
    $coachUser->assignPassword('coach12345');
    $coachUser->save();
    $coachUser->roles()->attach($rolCoach->id_rol);

    $coach = Coach::query()->create([
        'id_user' => $coachUser->id_user,
        'codigo_coach' => 'C-PR-01',
        'especialidad' => 'Sala',
        'estado_coach' => 'ACTIVO',
    ]);

    $user = User::make([
        'nombres' => 'Julián',
        'apellidos' => 'Presencial',
        'correo' => 'julian.presencial@test.local',
        'email' => 'julian.presencial@test.local',
        'telefono' => '3002223344',
        'estado' => 'ACTIVO',
        'estado_cuenta' => 'ACTIVO',
        'acepto_terminos' => true,
    ]);
    $user->assignPassword('cliente12345');
    $user->save();
    $user->roles()->attach($rolCliente->id_rol);

    $cliente = Cliente::query()->create([
        'id_user' => $user->id_user,
        'id_coach' => $coach->id_coach,
        'modalidad' => 'PRESENCIAL',
        'tipo_cliente' => 'INDIVIDUAL',
        'estado_cliente' => 'ACTIVO',
        'objetivo_principal' => 'Fuerza',
        'estatura_cm' => 178,
        'peso_inicial_kg' => 80,
        'ciudad' => 'Neiva',
        'fecha_alta' => now()->toDateString(),
    ]);

    $plan = Plan::query()->create([
        'nombre' => 'Plan Presencial Test',
        'slug' => 'plan-presencial-test-'.uniqid(),
        'modalidad' => 'PRESENCIAL',
        'duracion_dias' => 30,
        'precio' => 180000,
        'estado_plan' => 'ACTIVO',
    ]);

    $planCliente = PlanCliente::query()->create([
        'id_cliente' => $cliente->id_cliente,
        'id_plan' => $plan->id_plan,
        'fecha_inicio' => now()->toDateString(),
        'fecha_fin' => now()->addDays(30)->toDateString(),
        'estado_plan_cliente' => 'ACTIVO',
    ]);

    foreach ($codigos as $codigo) {
        $modulo = ModuloServicio::query()->firstOrCreate(
            ['codigo' => $codigo],
            ['nombre' => $codigo, 'estado' => 'ACTIVO', 'orden' => 1]
        );

        AccesoClienteModulo::query()->create([
            'id_plan_cliente' => $planCliente->id_plan_cliente,
            'id_cliente' => $cliente->id_cliente,
            'id_modulo' => $modulo->id_modulo,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addDays(30)->toDateString(),
            'estado_acceso' => 'ACTIVO',
        ]);
    }

    Pago::query()->create([
        'id_cliente' => $cliente->id_cliente,
        'id_plan' => $plan->id_plan,
        'id_plan_cliente' => $planCliente->id_plan_cliente,
        'monto' => 180000,
        'estado_pago' => 'APROBADO',
        'origen_pago' => 'DEMO',
        'creado_en' => now()->subDays(2),
    ]);

    return compact('user', 'cliente', 'plan', 'planCliente', 'coach');
}

test('cliente presencial ve ficha premium y tabs', function () {
    ['user' => $user] = createClientePresencialDemo();

    $this->actingAs($user)
        ->get(route('cliente.presencial.perfil'))
        ->assertOk()
        ->assertSee('Expediente presencial')
        ->assertSee('Registrar asistencia');

    foreach (['personal', 'membresia', 'entrenamiento', 'progreso', 'asistencia', 'pagos'] as $tab) {
        $this->actingAs($user)
            ->get(route('cliente.presencial.perfil', ['tab' => $tab]))
            ->assertOk();
    }
});

test('perfil generico redirige presencial a ficha', function () {
    ['user' => $user] = createClientePresencialDemo();

    $this->actingAs($user)
        ->get(route('cliente.perfil.edit'))
        ->assertRedirect(route('cliente.presencial.perfil'));
});

test('virtual no accede a ficha presencial', function () {
    $rolCliente = Rol::query()->firstOrCreate(
        ['codigo' => 'CLIENTE'],
        ['codigo_rol' => 'CLIENTE', 'nombre' => 'Cliente', 'estado' => 'ACTIVO']
    );

    $user = User::make([
        'nombres' => 'Virtual',
        'apellidos' => 'Only',
        'correo' => 'virtual.only@test.local',
        'email' => 'virtual.only@test.local',
        'estado' => 'ACTIVO',
        'estado_cuenta' => 'ACTIVO',
        'acepto_terminos' => true,
    ]);
    $user->assignPassword('cliente12345');
    $user->save();
    $user->roles()->attach($rolCliente->id_rol);

    Cliente::query()->create([
        'id_user' => $user->id_user,
        'modalidad' => 'VIRTUAL',
        'tipo_cliente' => 'INDIVIDUAL',
        'estado_cliente' => 'ACTIVO',
        'fecha_alta' => now()->toDateString(),
    ]);

    $this->actingAs($user)
        ->get(route('cliente.presencial.perfil'))
        ->assertRedirect(route('cliente.perfil.edit'));
});

test('cliente presencial registra checkin de asistencia', function () {
    ['user' => $user, 'cliente' => $cliente] = createClientePresencialDemo();

    $this->actingAs($user)
        ->post(route('cliente.presencial.checkin'), [
            'observacion' => 'Piernas',
        ])
        ->assertRedirect(route('cliente.presencial.perfil', ['tab' => 'asistencia']));

    expect(
        Asistencia::query()
            ->where('id_cliente', $cliente->id_cliente)
            ->where('fecha', now()->toDateString())
            ->where('estado', 'PRESENTE')
            ->exists()
    )->toBeTrue();

    // Segunda vez no duplica
    $this->actingAs($user)
        ->post(route('cliente.presencial.checkin'))
        ->assertRedirect();

    expect(
        Asistencia::query()
            ->where('id_cliente', $cliente->id_cliente)
            ->where('fecha', now()->toDateString())
            ->count()
    )->toBe(1);
});

test('cliente presencial actualiza ficha personal', function () {
    ['user' => $user] = createClientePresencialDemo();

    $this->actingAs($user)
        ->put(route('cliente.presencial.update'), [
            'nombres' => 'Julián Andrés',
            'apellidos' => 'Presencial',
            'telefono' => '3015556677',
            'ciudad' => 'Campoalegre',
            'objetivo_principal' => 'Hipertrofia',
            'notif_whatsapp' => 1,
            'idioma' => 'es',
        ])
        ->assertRedirect(route('cliente.presencial.perfil', ['tab' => 'personal']));

    $user->refresh();
    expect($user->nombres)->toBe('Julián Andrés');
    expect($user->cliente->fresh()->ciudad)->toBe('Campoalegre');
    expect($user->cliente->fresh()->objetivo_principal)->toBe('Hipertrofia');
});
