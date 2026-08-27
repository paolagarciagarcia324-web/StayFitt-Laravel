<?php

use App\Models\Cliente;
use App\Models\Coach;
use App\Models\PlanEntrenamiento;
use App\Models\PropuestaPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function rolIdStaff(string $codigo, string $nombre): int
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

function crearAsistente(): User
{
    $idRol = rolIdStaff('ASISTENTE_TECNICO', 'Asistente');

    $user = new User([
        'nombres' => 'Andrea',
        'apellidos' => 'Ops',
        'correo' => 'asistente.test@figuefit.test',
        'email' => 'asistente.test@figuefit.test',
        'estado' => 'ACTIVO',
        'estado_cuenta' => 'ACTIVO',
        'acepto_terminos' => true,
    ]);
    $user->assignPassword('demo12345');
    $user->save();
    DB::table('user_roles')->insert(['id_user' => $user->id_user, 'id_rol' => $idRol]);

    return $user;
}

function crearCoachParaStaff(): Coach
{
    $idRol = rolIdStaff('COACH', 'Coach');
    $user = new User([
        'nombres' => 'Coach',
        'apellidos' => 'Staff',
        'correo' => 'coach.staff@figuefit.test',
        'email' => 'coach.staff@figuefit.test',
        'estado' => 'ACTIVO',
        'estado_cuenta' => 'ACTIVO',
        'acepto_terminos' => true,
    ]);
    $user->assignPassword('demo12345');
    $user->save();
    DB::table('user_roles')->insert(['id_user' => $user->id_user, 'id_rol' => $idRol]);

    return Coach::query()->create([
        'id_user' => $user->id_user,
        'codigo_coach' => 'C-STAFF',
        'especialidad' => 'Test',
        'estado_coach' => 'ACTIVO',
    ]);
}

test('asistente accede al dashboard staff premium', function () {
    $asistente = crearAsistente();
    crearCoachParaStaff();

    $this->actingAs($asistente)
        ->get(route('staff.dashboard'))
        ->assertOk()
        ->assertSee('Centro operativo');
});

test('asistente consulta clientes de apoyo del coach', function () {
    $asistente = crearAsistente();
    $coach = crearCoachParaStaff();
    session(['staff_coach_id' => $coach->id_coach]);

    $idRolCliente = rolIdStaff('CLIENTE', 'Cliente');
    $userCliente = new User([
        'nombres' => 'Cliente',
        'apellidos' => 'Apoyo',
        'correo' => 'cliente.staff@figuefit.test',
        'email' => 'cliente.staff@figuefit.test',
        'estado' => 'ACTIVO',
        'estado_cuenta' => 'ACTIVO',
        'acepto_terminos' => true,
    ]);
    $userCliente->assignPassword('demo12345');
    $userCliente->save();
    DB::table('user_roles')->insert(['id_user' => $userCliente->id_user, 'id_rol' => $idRolCliente]);

    Cliente::query()->create([
        'id_user' => $userCliente->id_user,
        'modalidad' => 'VIRTUAL',
        'estado_cliente' => 'ACTIVA',
        'id_coach' => $coach->id_coach,
        'id_coach_asignado' => $coach->id_coach,
    ]);

    $this->actingAs($asistente)
        ->get(route('staff.clientes.index'))
        ->assertOk()
        ->assertSee('Cliente');
});

test('asistente crea borrador de rutina', function () {
    $asistente = crearAsistente();
    $coach = crearCoachParaStaff();
    session(['staff_coach_id' => $coach->id_coach]);

    $idRolCliente = rolIdStaff('CLIENTE', 'Cliente');
    $userCliente = new User([
        'nombres' => 'Atleta',
        'apellidos' => 'Demo',
        'correo' => 'atleta.staff@figuefit.test',
        'email' => 'atleta.staff@figuefit.test',
        'estado' => 'ACTIVO',
        'estado_cuenta' => 'ACTIVO',
        'acepto_terminos' => true,
    ]);
    $userCliente->assignPassword('demo12345');
    $userCliente->save();
    DB::table('user_roles')->insert(['id_user' => $userCliente->id_user, 'id_rol' => $idRolCliente]);

    $cliente = Cliente::query()->create([
        'id_user' => $userCliente->id_user,
        'modalidad' => 'VIRTUAL',
        'estado_cliente' => 'ACTIVA',
        'id_coach' => $coach->id_coach,
    ]);

    $this->actingAs($asistente)
        ->post(route('staff.rutinas.borrador.store'), [
            'id_cliente' => $cliente->id_cliente,
            'nombre' => 'Borrador test',
            'objetivo' => 'Fuerza',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(PlanEntrenamiento::query()->where('estado', 'BORRADOR')->count())->toBe(1);
});

test('cliente no accede al modulo staff', function () {
    $idRol = rolIdStaff('CLIENTE', 'Cliente');
    $user = new User([
        'nombres' => 'Solo',
        'apellidos' => 'Cliente',
        'correo' => 'solo.cliente@figuefit.test',
        'email' => 'solo.cliente@figuefit.test',
        'estado' => 'ACTIVO',
        'estado_cuenta' => 'ACTIVO',
        'acepto_terminos' => true,
    ]);
    $user->assignPassword('demo12345');
    $user->save();
    DB::table('user_roles')->insert(['id_user' => $user->id_user, 'id_rol' => $idRol]);

    $this->actingAs($user)->get(route('staff.dashboard'))->assertForbidden();
});
