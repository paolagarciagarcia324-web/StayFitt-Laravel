<?php

use App\Models\Cliente;
use App\Models\Coach;
use App\Models\PlanEntrenamiento;
use App\Models\RegistroProgreso;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function rolId(string $codigo, string $nombre): int
{
    $existing = DB::table('roles')->where('codigo', $codigo)->value('id_rol');
    if ($existing) {
        return (int) $existing;
    }

    return (int) DB::table('roles')->insertGetId([
        'codigo' => $codigo,
        'codigo_rol' => $codigo,
        'nombre' => $nombre,
        'estado' => 'ACTIVO',
    ]);
}

function crearCoachUsuario(string $suffix = '1'): array
{
    $idRol = rolId('COACH', 'Coach');

    $user = new User([
        'nombres' => 'Laura',
        'apellidos' => 'Coach',
        'correo' => "coach.test{$suffix}@figuefit.test",
        'email' => "coach.test{$suffix}@figuefit.test",
        'estado' => 'ACTIVO',
        'estado_cuenta' => 'ACTIVO',
        'acepto_terminos' => true,
    ]);
    $user->assignPassword('demo12345');
    $user->save();
    DB::table('user_roles')->insert(['id_user' => $user->id_user, 'id_rol' => $idRol]);

    $coach = Coach::query()->create([
        'id_user' => $user->id_user,
        'codigo_coach' => 'C-TEST'.$suffix,
        'especialidad' => 'Fuerza',
        'estado_coach' => 'ACTIVO',
    ]);

    return compact('user', 'coach');
}

function crearClienteAsignado(Coach $coach, string $suffix = '1'): Cliente
{
    $idRol = rolId('CLIENTE', 'Cliente');

    $userCliente = new User([
        'nombres' => 'Ana',
        'apellidos' => 'Atleta',
        'correo' => "ana.test{$suffix}@figuefit.test",
        'email' => "ana.test{$suffix}@figuefit.test",
        'estado' => 'ACTIVO',
        'estado_cuenta' => 'ACTIVO',
        'acepto_terminos' => true,
    ]);
    $userCliente->assignPassword('demo12345');
    $userCliente->save();
    DB::table('user_roles')->insert(['id_user' => $userCliente->id_user, 'id_rol' => $idRol]);

    return Cliente::query()->create([
        'id_user' => $userCliente->id_user,
        'modalidad' => 'VIRTUAL',
        'estado_cliente' => 'ACTIVA',
        'id_coach' => $coach->id_coach,
        'id_coach_asignado' => $coach->id_coach,
        'objetivo_principal' => 'Tonificar',
    ]);
}

test('coach accede al dashboard premium', function () {
    ['user' => $user] = crearCoachUsuario();

    $this->actingAs($user)
        ->get(route('coach.dashboard'))
        ->assertOk()
        ->assertSee('Centro de operaciones');
});

test('coach ve solo sus clientes asignados', function () {
    ['user' => $user, 'coach' => $coach] = crearCoachUsuario();
    crearClienteAsignado($coach);

    $this->actingAs($user)
        ->get(route('coach.clientes.index'))
        ->assertOk()
        ->assertSee('Ana');

    $cliente = Cliente::query()->where('id_coach', $coach->id_coach)->first();

    $this->actingAs($user)
        ->get(route('coach.clientes.show', $cliente->id_cliente))
        ->assertOk()
        ->assertSee('Expediente');
});

test('coach no accede a cliente de otro coach', function () {
    ['user' => $user] = crearCoachUsuario('a');
    ['coach' => $otroCoach] = crearCoachUsuario('b');
    $ajeno = crearClienteAsignado($otroCoach, 'b');

    $this->actingAs($user)
        ->get(route('coach.clientes.show', $ajeno->id_cliente))
        ->assertNotFound();
});

test('coach crea plan y rutina de entrenamiento', function () {
    ['user' => $user, 'coach' => $coach] = crearCoachUsuario('c');
    $cliente = crearClienteAsignado($coach, 'c');

    $this->actingAs($user)
        ->post(route('coach.entrenamiento.planes.store'), [
            'id_cliente' => $cliente->id_cliente,
            'nombre' => 'Plan test',
            'objetivo' => 'Intermedio',
        ])
        ->assertRedirect();

    $plan = PlanEntrenamiento::query()->where('id_coach', $coach->id_coach)->first();
    expect($plan)->not->toBeNull();

    $this->actingAs($user)
        ->post(route('coach.entrenamiento.rutinas.store', $plan->id_plan_entrenamiento), [
            'nombre' => 'Día A',
            'nivel' => 'Intermedio',
            'duracion_min' => 45,
            'ejercicios' => [
                ['nombre_ejercicio' => 'Sentadilla', 'series' => 3, 'repeticiones' => '12', 'descanso_seg' => 60],
            ],
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($plan->fresh()->rutinas)->toHaveCount(1);
});

test('coach registra observacion en progreso', function () {
    ['user' => $user, 'coach' => $coach] = crearCoachUsuario('d');
    $cliente = crearClienteAsignado($coach, 'd');

    $registro = RegistroProgreso::query()->create([
        'id_cliente' => $cliente->id_cliente,
        'id_coach' => $coach->id_coach,
        'fecha_registro' => now()->toDateString(),
        'peso_kg' => 68.5,
    ]);

    $this->actingAs($user)
        ->post(route('coach.reportes.progreso.observacion'), [
            'id_registro_progreso' => $registro->id_registro_progreso,
            'observacion_coach' => 'Buena adherencia semanal',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($registro->fresh()->observacion_coach)->toBe('Buena adherencia semanal');
});

test('usuario sin rol coach no accede al modulo', function () {
    $idRol = rolId('CLIENTE', 'Cliente');

    $user = new User([
        'nombres' => 'Sin',
        'apellidos' => 'Coach',
        'correo' => 'nocoach@figuefit.test',
        'email' => 'nocoach@figuefit.test',
        'estado' => 'ACTIVO',
        'estado_cuenta' => 'ACTIVO',
        'acepto_terminos' => true,
    ]);
    $user->assignPassword('demo12345');
    $user->save();
    DB::table('user_roles')->insert(['id_user' => $user->id_user, 'id_rol' => $idRol]);

    $this->actingAs($user)
        ->get(route('coach.dashboard'))
        ->assertForbidden();
});
