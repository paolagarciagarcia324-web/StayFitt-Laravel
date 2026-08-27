<?php

use App\Models\Cliente;
use App\Models\Rol;
use App\Models\User;
use App\Services\Auth\LoginUsuarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('login busca usuario en base de datos por correo y valida password_hash', function () {
    $user = User::factory()->create([
        'correo' => 'login.db@test.local',
        'email' => 'login.db@test.local',
    ]);
    $user->assignPassword('clave12345');
    $user->save();

    $rol = Rol::query()->firstOrCreate(
        ['codigo' => 'CLIENTE'],
        ['codigo_rol' => 'CLIENTE', 'nombre' => 'Cliente', 'estado' => 'ACTIVO']
    );
    $user->roles()->attach($rol->id_rol);

    $this->post('/login', [
        'email' => 'login.db@test.local',
        'password' => 'clave12345',
    ])->assertRedirect(route('cliente.dashboard', absolute: false));

    $this->assertAuthenticatedAs($user);
});

test('login acepta documento como contraseña inicial presencial', function () {
    $user = User::factory()->create([
        'correo' => 'doc.login@test.local',
        'email' => 'doc.login@test.local',
        'documento_identidad' => '99887766',
    ]);
    $user->assignPassword('otra-clave');
    $user->save();

    Cliente::query()->create([
        'id_user' => $user->id_user,
        'modalidad' => 'PRESENCIAL',
        'tipo_cliente' => 'INDIVIDUAL',
        'documento' => '99887766',
        'estado_cliente' => 'ACTIVO',
        'fecha_alta' => now()->toDateString(),
    ]);

    $service = app(LoginUsuarioService::class);
    $found = $service->findByLogin('doc.login@test.local');

    expect($found)->not->toBeNull();
    expect($service->credentialsMatch($found, '99887766'))->toBeTrue();
});

test('login rechaza credenciales invalidas', function () {
    $user = User::factory()->create([
        'correo' => 'fail.login@test.local',
        'email' => 'fail.login@test.local',
    ]);
    $user->assignPassword('real12345');
    $user->save();

    $this->post('/login', [
        'email' => 'fail.login@test.local',
        'password' => 'incorrecta',
    ]);

    $this->assertGuest();
});

test('coach no redirige a ruta cliente tras login con url intended', function () {
    $user = User::factory()->create([
        'correo' => 'coach.redirect@test.local',
        'email' => 'coach.redirect@test.local',
    ]);
    $user->assignPassword('demo12345');
    $user->save();

    $rol = Rol::query()->firstOrCreate(
        ['codigo' => 'COACH'],
        ['codigo_rol' => 'COACH', 'nombre' => 'Coach', 'estado' => 'ACTIVO']
    );
    $user->roles()->attach($rol->id_rol);

    $this->get('/cliente/pagos')->assertRedirect(route('login'));

    $this->post('/login', [
        'email' => 'coach.redirect@test.local',
        'password' => 'demo12345',
    ])->assertRedirect(route('coach.dashboard', absolute: false));

    $this->actingAs($user)->get('/cliente/pagos')->assertForbidden();
});
