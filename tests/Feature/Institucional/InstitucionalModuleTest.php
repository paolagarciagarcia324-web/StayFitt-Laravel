<?php

use App\Models\Cliente;
use App\Models\Institucion;
use App\Models\InvitacionInstitucional;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function rolInstitucional(string $codigo, string $nombre): int
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

function crearInstitucionDemo(): Institucion
{
    return Institucion::create([
        'nombre' => 'Empresa Test B2B',
        'nit' => '900999888-1',
        'contacto_nombre' => 'Ana Responsable',
        'contacto_cargo' => 'RR.HH.',
        'correo' => 'rrhh@test.test',
        'telefono' => '3001112233',
        'ciudad' => 'Neiva',
        'tipo_institucion' => 'EMPRESA',
        'cupos_totales' => 50,
        'cupos_usados' => 0,
        'fecha_inicio' => now()->subMonth(),
        'fecha_fin' => now()->addYear(),
        'estado' => 'ACTIVA',
    ]);
}

function crearResponsable(Institucion $inst): User
{
    $idRol = rolInstitucional('RESPONSABLE_INSTITUCIONAL', 'Responsable');

    $user = new User([
        'nombres' => 'Ana',
        'apellidos' => 'Institucional',
        'correo' => 'responsable.inst@test.test',
        'email' => 'responsable.inst@test.test',
        'estado' => 'ACTIVO',
        'estado_cuenta' => 'ACTIVO',
        'acepto_terminos' => true,
    ]);
    $user->assignPassword('demo12345');
    $user->save();

    DB::table('user_roles')->insert(['id_user' => $user->id_user, 'id_rol' => $idRol]);
    $inst->update(['id_user_responsable' => $user->id_user]);

    return $user;
}

test('responsable accede al dashboard institucional', function () {
    $inst = crearInstitucionDemo();
    $user = crearResponsable($inst);

    $this->actingAs($user)
        ->get(route('institucional.dashboard'))
        ->assertOk()
        ->assertSee('Empresa Test B2B');
});

test('responsable no ve otra institución', function () {
    $inst = crearInstitucionDemo();
    $otra = Institucion::create([
        'nombre' => 'Otra Org',
        'nit' => '800111222-3',
        'cupos_totales' => 10,
        'cupos_usados' => 0,
        'estado' => 'ACTIVA',
    ]);

    $user = crearResponsable($inst);

    $userAjeno = new User([
        'nombres' => 'Miembro',
        'apellidos' => 'Ajeno',
        'correo' => 'ajeno@test.test',
        'email' => 'ajeno@test.test',
        'estado' => 'ACTIVO',
        'estado_cuenta' => 'ACTIVO',
        'acepto_terminos' => true,
    ]);
    $userAjeno->assignPassword('demo12345');
    $userAjeno->save();

    $clienteAjeno = Cliente::create([
        'id_user' => $userAjeno->id_user,
        'id_institucion' => $otra->id_institucion,
        'tipo_cliente' => 'INSTITUCIONAL',
        'modalidad' => 'VIRTUAL',
        'estado_cliente' => 'ACTIVO',
        'fecha_alta' => now(),
    ]);

    $userPropio = new User([
        'nombres' => 'Miembro',
        'apellidos' => 'Propio',
        'correo' => 'propio@test.test',
        'email' => 'propio@test.test',
        'estado' => 'ACTIVO',
        'estado_cuenta' => 'ACTIVO',
        'acepto_terminos' => true,
    ]);
    $userPropio->assignPassword('demo12345');
    $userPropio->save();

    $miembroPropio = Cliente::create([
        'id_user' => $userPropio->id_user,
        'id_institucion' => $inst->id_institucion,
        'tipo_cliente' => 'INSTITUCIONAL',
        'modalidad' => 'VIRTUAL',
        'estado_cliente' => 'ACTIVO',
        'fecha_alta' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('institucional.usuarios.show', $miembroPropio->id_cliente))
        ->assertOk()
        ->assertSee('Miembro Propio');

    $this->actingAs($user)
        ->get(route('institucional.usuarios.show', $clienteAjeno->id_cliente))
        ->assertNotFound();
});

test('cliente regular no accede al portal institucional', function () {
    $idRol = rolInstitucional('CLIENTE', 'Cliente');
    $user = new User([
        'nombres' => 'Cliente',
        'apellidos' => 'Normal',
        'correo' => 'cliente.normal@test.test',
        'email' => 'cliente.normal@test.test',
        'estado' => 'ACTIVO',
        'estado_cuenta' => 'ACTIVO',
        'acepto_terminos' => true,
    ]);
    $user->assignPassword('demo12345');
    $user->save();
    DB::table('user_roles')->insert(['id_user' => $user->id_user, 'id_rol' => $idRol]);

    $this->actingAs($user)
        ->get(route('institucional.dashboard'))
        ->assertForbidden();
});

test('responsable puede crear invitación', function () {
    $inst = crearInstitucionDemo();
    $user = crearResponsable($inst);

    $this->actingAs($user)
        ->post(route('institucional.invitaciones.store'), [
            'correo' => 'nuevo.colaborador@test.test',
            'dias_validez' => 7,
        ])
        ->assertRedirect();

    expect(InvitacionInstitucional::query()->where('correo', 'nuevo.colaborador@test.test')->exists())->toBeTrue();
});

test('login redirige responsable a institucional', function () {
    $inst = crearInstitucionDemo();
    $user = crearResponsable($inst);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertRedirect(route('institucional.dashboard'));
});

test('responsable actualiza perfil institucional', function () {
    $inst = crearInstitucionDemo();
    $user = crearResponsable($inst);

    $this->actingAs($user)
        ->put(route('institucional.perfil.update'), [
            'nombre' => 'Empresa Actualizada B2B',
            'tipo_institucion' => 'EMPRESA',
            'ciudad' => 'Bogotá',
        ])
        ->assertRedirect();

    expect($inst->fresh()->nombre)->toBe('Empresa Actualizada B2B');
});

test('responsable exporta reporte csv', function () {
    $inst = crearInstitucionDemo();
    $user = crearResponsable($inst);

    $this->actingAs($user)
        ->get(route('institucional.reportes.export'))
        ->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');
});

test('responsable envía comunicado a miembros activos', function () {
    $inst = crearInstitucionDemo();
    $user = crearResponsable($inst);

    $miembroUser = new User([
        'nombres' => 'Colab',
        'apellidos' => 'Activo',
        'correo' => 'colab@test.test',
        'email' => 'colab@test.test',
        'estado' => 'ACTIVO',
        'estado_cuenta' => 'ACTIVO',
        'acepto_terminos' => true,
    ]);
    $miembroUser->assignPassword('demo12345');
    $miembroUser->save();

    Cliente::create([
        'id_user' => $miembroUser->id_user,
        'id_institucion' => $inst->id_institucion,
        'tipo_cliente' => 'INSTITUCIONAL',
        'modalidad' => 'VIRTUAL',
        'estado_cliente' => 'ACTIVO',
        'fecha_alta' => now(),
    ]);

    $this->actingAs($user)
        ->post(route('institucional.comunicacion.store'), [
            'titulo' => 'Aviso demo',
            'mensaje' => 'Mensaje de prueba institucional.',
            'canal' => 'IN_APP',
        ])
        ->assertRedirect();

    expect(DB::table('comunicados_institucionales')->where('titulo', 'Aviso demo')->exists())->toBeTrue();
});

test('registro publico acepta invitacion', function () {
    $inst = crearInstitucionDemo();
    $planId = DB::table('planes')->orderBy('id_plan')->value('id_plan');

    $inv = InvitacionInstitucional::create([
        'id_institucion' => $inst->id_institucion,
        'id_plan' => $planId,
        'correo' => 'invitado@test.test',
        'token' => 'token-invitacion-test-123',
        'estado' => 'PENDIENTE',
        'fecha_expiracion' => now()->addDays(7),
    ]);

    $this->get(route('registro-institucional.invitacion', $inv->token))
        ->assertOk()
        ->assertSee($inst->nombre);

    $this->post(route('registro-institucional.invitacion.store', $inv->token), [
        'nombres' => 'Invitado',
        'apellidos' => 'Test',
        'correo' => 'invitado@test.test',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'acepto_terminos' => '1',
    ])->assertRedirect(route('login'));

    expect($inv->fresh()->estado)->toBe('ACEPTADA');
    expect(Cliente::query()->where('id_institucion', $inst->id_institucion)->whereHas('usuario', fn ($q) => $q->where('correo', 'invitado@test.test'))->exists())->toBeTrue();
});
