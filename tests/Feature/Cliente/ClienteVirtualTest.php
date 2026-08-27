<?php

use App\Models\AccesoClienteModulo;
use App\Models\Cliente;
use App\Models\Coach;
use App\Models\ModuloServicio;
use App\Models\Plan;
use App\Models\PlanCliente;
use App\Models\PlanEntrenamiento;
use App\Models\RegistroProgreso;
use App\Models\RegistroRutina;
use App\Models\Rol;
use App\Models\Rutina;
use App\Models\User;
use App\Services\ModuloAccesoService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createClienteVirtualConModulos(array $codigos = ['PERFIL', 'ENTRENAMIENTO', 'PROGRESO']): array
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
        'apellidos' => 'Demo',
        'correo' => 'coach.cv@test.local',
        'email' => 'coach.cv@test.local',
        'estado' => 'ACTIVO',
        'estado_cuenta' => 'ACTIVO',
        'acepto_terminos' => true,
    ]);
    $coachUser->assignPassword('coach12345');
    $coachUser->save();
    $coachUser->roles()->attach($rolCoach->id_rol);

    $coach = Coach::query()->create([
        'id_user' => $coachUser->id_user,
        'codigo_coach' => 'C-CV-01',
        'especialidad' => 'General',
        'estado_coach' => 'ACTIVO',
    ]);

    $user = User::make([
        'nombres' => 'Ana',
        'apellidos' => 'Virtual',
        'correo' => 'ana.virtual@test.local',
        'email' => 'ana.virtual@test.local',
        'telefono' => '3001112233',
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
        'modalidad' => 'VIRTUAL',
        'tipo_cliente' => 'INDIVIDUAL',
        'estado_cliente' => 'ACTIVO',
        'objetivo_principal' => 'Salud',
        'estatura_cm' => 165,
        'fecha_alta' => now()->toDateString(),
    ]);

    $plan = Plan::query()->create([
        'nombre' => 'Plan Virtual Test',
        'slug' => 'plan-virtual-test-'.uniqid(),
        'modalidad' => 'VIRTUAL',
        'duracion_dias' => 30,
        'precio' => 99000,
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

    return compact('user', 'cliente', 'plan', 'planCliente', 'coach');
}

test('modulo acceso acepta estado_cliente ACTIVO', function () {
    ['cliente' => $cliente] = createClienteVirtualConModulos(['ENTRENAMIENTO']);

    expect(app(ModuloAccesoService::class)->clienteTieneModulo($cliente, 'ENTRENAMIENTO'))->toBeTrue();
});

test('cliente accede al dashboard perfil plan pagos y analisis', function () {
    ['user' => $user] = createClienteVirtualConModulos();

    foreach ([
        'cliente.dashboard',
        'cliente.perfil.edit',
        'cliente.plan.index',
        'cliente.pagos.index',
        'cliente.analisis.index',
        'cliente.comercial.planes',
        'cliente.comercial.cupones',
        'cliente.novedades.index',
    ] as $routeName) {
        $this->actingAs($user)->get(route($routeName))->assertOk();
    }
});

test('cliente actualiza perfil', function () {
    ['user' => $user] = createClienteVirtualConModulos();

    $this->actingAs($user)
        ->put(route('cliente.perfil.update'), [
            'nombres' => 'Ana María',
            'apellidos' => 'Virtual',
            'telefono' => '3009998877',
            'ciudad' => 'Neiva',
            'objetivo_principal' => 'Fuerza',
            'notif_email' => 1,
            'idioma' => 'es',
        ])
        ->assertRedirect();

    $user->refresh();
    expect($user->nombres)->toBe('Ana María');
    expect($user->cliente->fresh()->ciudad)->toBe('Neiva');
});

test('entrenamiento sin módulo responde 403', function () {
    ['user' => $user] = createClienteVirtualConModulos(['PROGRESO']);

    $this->actingAs($user)
        ->get(route('cliente.entrenamiento.index'))
        ->assertForbidden();
});

test('cliente completa rutina de entrenamiento', function () {
    ['user' => $user, 'cliente' => $cliente, 'coach' => $coach] = createClienteVirtualConModulos(['ENTRENAMIENTO']);

    $planEnt = PlanEntrenamiento::query()->create([
        'id_cliente' => $cliente->id_cliente,
        'id_coach' => $coach->id_coach,
        'nombre' => 'Plan test',
        'objetivo' => 'Test',
        'modalidad' => 'VIRTUAL',
        'estado' => 'ACTIVO',
    ]);

    $rutina = Rutina::query()->create([
        'id_plan_entrenamiento' => $planEnt->id_plan_entrenamiento,
        'nombre' => 'Rutina A',
        'dia_semana' => 1,
        'orden' => 1,
        'duracion_min' => 40,
        'estado' => 'ACTIVA',
    ]);

    $this->actingAs($user)
        ->get(route('cliente.entrenamiento.index'))
        ->assertOk();

    $this->actingAs($user)
        ->post(route('cliente.entrenamiento.completar', $rutina->id_rutina), [
            'dificultad' => 5,
            'observacion_cliente' => 'OK',
        ])
        ->assertRedirect();

    expect(
        RegistroRutina::query()
            ->where('id_cliente', $cliente->id_cliente)
            ->where('id_rutina', $rutina->id_rutina)
            ->where('completada', true)
            ->exists()
    )->toBeTrue();
});

test('cliente registra progreso', function () {
    ['user' => $user, 'cliente' => $cliente] = createClienteVirtualConModulos(['PROGRESO']);

    $this->actingAs($user)
        ->post(route('cliente.progreso.store'), [
            'fecha_registro' => now()->toDateString(),
            'peso_kg' => 66.5,
            'estatura_cm' => 165,
            'nivel_energia' => 8,
        ])
        ->assertRedirect(route('cliente.progreso.index'));

    expect(
        RegistroProgreso::query()
            ->where('id_cliente', $cliente->id_cliente)
            ->where('peso_kg', 66.5)
            ->exists()
    )->toBeTrue();
});

test('cliente sin rol no entra al panel cliente', function () {
    $rol = Rol::query()->firstOrCreate(
        ['codigo' => 'ADMIN'],
        ['codigo_rol' => 'ADMIN', 'nombre' => 'Admin', 'estado' => 'ACTIVO']
    );

    $user = User::make([
        'nombres' => 'Admin',
        'apellidos' => 'Only',
        'correo' => 'admin.only@test.local',
        'email' => 'admin.only@test.local',
        'estado' => 'ACTIVO',
        'estado_cuenta' => 'ACTIVO',
        'acepto_terminos' => true,
    ]);
    $user->assignPassword('admin12345');
    $user->save();
    $user->roles()->attach($rol->id_rol);

    $this->actingAs($user)
        ->get(route('cliente.dashboard'))
        ->assertForbidden();
});
