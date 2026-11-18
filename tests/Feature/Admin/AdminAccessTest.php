<?php

use App\Models\Rol;
use App\Models\User;
use App\Support\FiltersAssignableRoles;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createAdminUser(): User
{
    $rol = Rol::query()->firstOrCreate(
        ['codigo' => 'ADMIN'],
        [
            'codigo_rol' => 'ADMIN',
            'nombre' => 'Administrador',
            'descripcion' => 'Test admin',
            'estado' => 'ACTIVO',
        ]
    );

    $user = new User([
        'nombres' => 'Admin',
        'apellidos' => 'Test',
        'correo' => 'admin@test.local',
        'email' => 'admin@test.local',
        'estado' => 'ACTIVO',
        'estado_cuenta' => 'ACTIVO',
        'acepto_terminos' => true,
    ]);
    $user->assignPassword('secret12345');
    $user->save();
    $user->roles()->attach($rol->id_rol);

    return $user->fresh('roles');
}

function createClienteUser(): User
{
    $rol = Rol::query()->firstOrCreate(
        ['codigo' => 'CLIENTE'],
        [
            'codigo_rol' => 'CLIENTE',
            'nombre' => 'Cliente',
            'estado' => 'ACTIVO',
        ]
    );

    $user = new User([
        'nombres' => 'Cliente',
        'apellidos' => 'Demo',
        'correo' => 'cliente@test.local',
        'email' => 'cliente@test.local',
        'estado' => 'ACTIVO',
        'estado_cuenta' => 'ACTIVO',
        'acepto_terminos' => true,
    ]);
    $user->assignPassword('cliente123');
    $user->save();
    $user->roles()->attach($rol->id_rol);

    return $user->fresh('roles');
}

test('guest cannot access admin dashboard', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
});

test('non admin user receives 403 on admin routes', function () {
    $cliente = createClienteUser();

    $this->actingAs($cliente)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('admin can access dashboard', function () {
    $admin = createAdminUser();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk();
});

test('admin login redirects to admin dashboard', function () {
    createAdminUser();

    $this->post('/login', [
        'email' => 'admin@test.local',
        'password' => 'secret12345',
    ])->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticated();
});

test('filters assignable roles excludes admin', function () {
    $adminId = FiltersAssignableRoles::adminRoleId();
    $clienteRol = Rol::query()->firstOrCreate(
        ['codigo' => 'CLIENTE'],
        [
            'codigo_rol' => 'CLIENTE',
            'nombre' => 'Cliente',
            'estado' => 'ACTIVO',
        ]
    );

    $sanitized = FiltersAssignableRoles::sanitize([$adminId, $clienteRol->id_rol]);

    expect($sanitized)->toContain($clienteRol->id_rol)
        ->and($sanitized)->not->toContain($adminId);
});

test('admin can view roles index', function () {
    $admin = createAdminUser();

    $this->actingAs($admin)
        ->get(route('admin.roles.index'))
        ->assertOk()
        ->assertSee('ADMIN');
});

test('admin can view configuration index', function () {
    $admin = createAdminUser();

    $this->actingAs($admin)
        ->get(route('admin.configuracion.index'))
        ->assertOk();
});

test('admin puede abrir módulos rediseñados', function () {
    $admin = createAdminUser();

    $routes = [
        'admin.dashboard',
        'admin.solicitudes.index',
        'admin.pagos.index',
        'admin.horarios.index',
        'admin.eventos.index',
        'admin.calendario.index',
        'admin.usuarios.index',
        'admin.clientes.virtuales',
        'admin.clientes.presenciales',
        'admin.instituciones.index',
        'admin.coaches.index',
        'admin.asistentes.index',
        'admin.roles.index',
        'admin.planes.index',
        'admin.propuestas.index',
        'admin.novedades.index',
        'admin.cupones.index',
        'admin.tienda.index',
        'admin.whatsapp.index',
        'admin.comunidad.index',
        'admin.estadisticas.index',
        'admin.reportes.index',
        'admin.configuracion.index',
        'admin.pasarelas.index',
    ];

    foreach ($routes as $name) {
        $this->actingAs($admin)
            ->get(route($name))
            ->assertOk();
    }
});
