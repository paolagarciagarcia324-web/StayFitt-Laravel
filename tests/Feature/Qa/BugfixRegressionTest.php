<?php

use App\Models\Cliente;
use App\Models\Institucion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function ensureRol(string $codigo): int
{
    $id = DB::table('roles')->where('codigo', $codigo)->value('id_rol');
    if ($id) {
        return (int) $id;
    }

    return (int) DB::table('roles')->insertGetId([
        'codigo' => $codigo,
        'codigo_rol' => $codigo,
        'nombre' => $codigo,
        'estado' => 'ACTIVO',
    ]);
}

test('cliente institucional puede acceder al portal cliente', function () {
    $idRol = ensureRol('CLIENTE_INSTITUCIONAL');
    $inst = Institucion::create([
        'nombre' => 'Inst QA',
        'nit' => '900700700-1',
        'cupos_totales' => 10,
        'cupos_usados' => 1,
        'estado' => 'ACTIVA',
    ]);

    $user = new User([
        'nombres' => 'Diana',
        'apellidos' => 'Inst',
        'correo' => 'diana.inst.qa@test.test',
        'email' => 'diana.inst.qa@test.test',
        'estado' => 'ACTIVO',
        'estado_cuenta' => 'ACTIVO',
        'acepto_terminos' => true,
    ]);
    $user->assignPassword('demo12345');
    $user->save();
    DB::table('user_roles')->insert(['id_user' => $user->id_user, 'id_rol' => $idRol]);

    Cliente::create([
        'id_user' => $user->id_user,
        'id_institucion' => $inst->id_institucion,
        'tipo_cliente' => 'INSTITUCIONAL',
        'modalidad' => 'VIRTUAL',
        'estado_cliente' => 'ACTIVO',
        'fecha_alta' => now(),
    ]);

    $this->actingAs($user)
        ->get('/cliente')
        ->assertOk();
});

test('admin puede crear producto sin categoria explicita', function () {
    ensureRol('ADMIN');
    $idRol = (int) DB::table('roles')->where('codigo', 'ADMIN')->value('id_rol');

    $admin = new User([
        'nombres' => 'Admin',
        'apellidos' => 'QA',
        'correo' => 'admin.tienda.qa@test.test',
        'email' => 'admin.tienda.qa@test.test',
        'estado' => 'ACTIVO',
        'estado_cuenta' => 'ACTIVO',
        'acepto_terminos' => true,
    ]);
    $admin->assignPassword('demo12345');
    $admin->save();
    DB::table('user_roles')->insert(['id_user' => $admin->id_user, 'id_rol' => $idRol]);

    if (! DB::table('categorias_producto')->exists()) {
        DB::table('categorias_producto')->insert([
            'nombre' => 'General',
            'estado' => 'ACTIVA',
            'creado_en' => now(),
        ]);
    }

    $sku = 'QA-T-'.uniqid();

    $this->actingAs($admin)
        ->post('/admin/tienda', [
            'nombre' => 'Prod QA',
            'sku' => $sku,
            'precio' => 12000,
            'stock_total' => 5,
        ])
        ->assertRedirect();

    expect(DB::table('productos_tienda')->where('sku', $sku)->exists())->toBeTrue();
});
