<?php

use App\Models\Cliente;
use App\Models\Pago;
use App\Models\Plan;
use App\Models\ReporteGenerado;
use App\Models\Rol;
use App\Models\User;
use App\Services\Analitica\FiltroAnalitica;
use App\Services\Analitica\IndicadorService;
use App\Services\Analitica\ReporteExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createAnaliticaAdmin(): User
{
    $rol = Rol::query()->firstOrCreate(
        ['codigo' => 'ADMIN'],
        [
            'codigo_rol' => 'ADMIN',
            'nombre' => 'Administrador',
            'estado' => 'ACTIVO',
        ]
    );

    $user = new User([
        'nombres' => 'Admin',
        'apellidos' => 'Analitica',
        'correo' => 'admin-analitica@test.local',
        'email' => 'admin-analitica@test.local',
        'estado' => 'ACTIVO',
        'estado_cuenta' => 'ACTIVO',
        'acepto_terminos' => true,
    ]);
    $user->assignPassword('admin12345');
    $user->save();
    $user->roles()->attach($rol->id_rol);

    return $user->fresh('roles');
}

function createAnaliticaClienteUser(): User
{
    $rol = Rol::query()->firstOrCreate(
        ['codigo' => 'CLIENTE'],
        ['codigo_rol' => 'CLIENTE', 'nombre' => 'Cliente', 'estado' => 'ACTIVO']
    );

    $user = new User([
        'nombres' => 'Cliente',
        'apellidos' => 'Analitica',
        'correo' => 'cliente-analitica@test.local',
        'email' => 'cliente-analitica@test.local',
        'estado' => 'ACTIVO',
        'estado_cuenta' => 'ACTIVO',
        'acepto_terminos' => true,
    ]);
    $user->assignPassword('cliente12345');
    $user->save();
    $user->roles()->attach($rol->id_rol);

    return $user->fresh('roles');
}

test('guest cannot access estadisticas', function () {
    $this->get(route('admin.estadisticas.index'))->assertRedirect(route('login'));
});

test('non admin cannot access reportes', function () {
    $cliente = createAnaliticaClienteUser();

    $this->actingAs($cliente)
        ->get(route('admin.reportes.index'))
        ->assertForbidden();
});

test('admin can access estadisticas dashboard', function () {
    $admin = createAnaliticaAdmin();

    $this->actingAs($admin)
        ->get(route('admin.estadisticas.index'))
        ->assertOk()
        ->assertSee('Dashboard analítico')
        ->assertSee('Resumen')
        ->assertSee('Comercial')
        ->assertSee('Comunicación');
});

test('admin can filter estadisticas by date', function () {
    $admin = createAnaliticaAdmin();

    $this->actingAs($admin)
        ->get(route('admin.estadisticas.index', [
            'fecha_inicio' => now()->subDays(7)->toDateString(),
            'fecha_fin' => now()->toDateString(),
        ]))
        ->assertOk();
});

test('admin can preview reporte pagos', function () {
    $admin = createAnaliticaAdmin();

    Pago::query()->create([
        'monto' => 100000,
        'moneda' => 'COP',
        'estado_pago' => 'VALIDADO',
        'metodo_pago' => 'TRANSFERENCIA',
        'pasarela' => 'NINGUNA',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.reportes.preview'), [
            'tipo' => 'pagos',
            'formato' => 'web',
            'fecha_inicio' => now()->subDays(30)->toDateString(),
            'fecha_fin' => now()->toDateString(),
        ])
        ->assertOk()
        ->assertSee('Reporte de pagos');
});

test('admin can export reporte csv and registers historial', function () {
    $admin = createAnaliticaAdmin();

    Plan::query()->create([
        'nombre' => 'Plan analitica',
        'slug' => 'plan-analitica-test',
        'modalidad' => 'VIRTUAL',
        'precio' => 50000,
        'duracion_dias' => 30,
        'estado_plan' => 'ACTIVO',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.reportes.export'), [
            'tipo' => 'cupos',
            'formato' => 'csv',
        ])
        ->assertOk();

    expect(ReporteGenerado::query()->where('tipo', 'CUPOS')->count())->toBe(1);
});

test('analisis index redirects to estadisticas', function () {
    $admin = createAnaliticaAdmin();

    $this->actingAs($admin)
        ->get(route('admin.analisis.index'))
        ->assertRedirect(route('admin.estadisticas.index'));
});

test('indicador service returns kpis structure', function () {
    createAnaliticaAdmin();

    $filtro = FiltroAnalitica::fromArray([]);
    $data = app(IndicadorService::class)->dashboard($filtro);

    expect($data)->toHaveKeys(['kpis', 'series', 'distribuciones', 'tablas']);
    expect($data['kpis'])->toHaveKeys([
        'usuarios_totales',
        'ingresos_periodo',
        'mensajes_periodo',
        'chats_activos',
        'cupones_usados',
    ]);
});

test('reporte export service lists all tipos', function () {
    expect(ReporteExportService::TIPOS)->toHaveKeys([
        'pagos', 'clientes', 'cupos', 'solicitudes', 'cupones', 'tienda', 'comunicacion',
    ]);
});
