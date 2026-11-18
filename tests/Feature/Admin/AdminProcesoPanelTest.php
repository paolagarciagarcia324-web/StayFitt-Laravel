<?php

use App\Models\Chat;
use App\Models\Cliente;
use App\Models\Coach;
use App\Models\HorarioPresencial;
use App\Models\Pago;
use App\Models\Plan;
use App\Models\Rol;
use App\Models\SolicitudCompra;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function procesoAdmin(): User
{
    $rolId = procesoRolId('ADMIN');

    $user = new User([
        'nombres' => 'Admin',
        'apellidos' => 'Proceso',
        'correo' => 'admin.proceso@test.local',
        'email' => 'admin.proceso@test.local',
        'estado' => 'ACTIVO',
        'estado_cuenta' => 'ACTIVO',
        'acepto_terminos' => true,
    ]);
    $user->assignPassword('secret12345');
    $user->save();
    $user->roles()->attach($rolId);

    return $user->fresh('roles');
}

function procesoRolId(string $codigo): int
{
    $existing = DB::table('roles')->where('codigo', $codigo)->value('id_rol');
    if ($existing) {
        return (int) $existing;
    }

    return (int) DB::table('roles')->insertGetId([
        'codigo' => $codigo,
        'codigo_rol' => $codigo,
        'nombre' => $codigo,
        'estado' => 'ACTIVO',
    ]);
}

function procesoPlanVirtual(): Plan
{
    return Plan::query()->firstOrCreate(
        ['codigo' => 'PLAN-ADMIN-PROC'],
        [
            'nombre' => 'Plan Admin Proceso',
            'slug' => 'plan-admin-proceso',
            'modalidad' => 'VIRTUAL',
            'tipo_cliente' => 'INDIVIDUAL',
            'precio' => 80000,
            'moneda' => 'COP',
            'duracion_dias' => 30,
            'estado_plan' => 'ACTIVO',
            'cupos_totales' => 20,
            'cupos_ocupados' => 0,
        ]
    );
}

test('aprobar solicitud pendiente crea cliente activo y cuenta cupo del plan', function () {
    $admin = procesoAdmin();
    procesoRolId('CLIENTE');
    $plan = procesoPlanVirtual();

    $solicitud = SolicitudCompra::query()->create([
        'codigo_solicitud' => 'SOL-PROC-1',
        'id_plan' => $plan->id_plan,
        'origen' => 'WEB',
        'modalidad' => 'VIRTUAL',
        'tipo_cliente' => 'INDIVIDUAL',
        'nombres' => 'Novedad',
        'apellidos' => 'Pago',
        'documento_identidad' => '100200300',
        'correo' => 'novedad.pago@test.local',
        'telefono' => '3001112233',
        'precio_lista' => $plan->precio,
        'descuento' => 0,
        'total' => $plan->precio,
        'acepto_terminos' => true,
        'estado_solicitud' => 'PENDIENTE',
    ]);

    Pago::query()->create([
        'id_solicitud' => $solicitud->id_solicitud,
        'id_plan' => $plan->id_plan,
        'monto' => $plan->precio,
        'moneda' => 'COP',
        'metodo_pago' => 'CAJA',
        'pasarela' => 'ADMIN',
        'estado_pago' => 'PENDIENTE',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.solicitudes.aprobar', $solicitud->id_solicitud))
        ->assertRedirect(route('admin.solicitudes.index'));

    $solicitud->refresh();
    expect($solicitud->estado_solicitud)->toBe('VALIDADA');

    $usuario = User::query()->where('correo', 'novedad.pago@test.local')->first();
    expect($usuario)->not->toBeNull()
        ->and($usuario->estado)->toBe('ACTIVO');

    $plan->refresh();
    expect((int) $plan->cupos_ocupados)->toBe(1);
});

test('validar pago pendiente activa la solicitud asociada', function () {
    $admin = procesoAdmin();
    procesoRolId('CLIENTE');
    $plan = procesoPlanVirtual();

    $solicitud = SolicitudCompra::query()->create([
        'codigo_solicitud' => 'SOL-PROC-2',
        'id_plan' => $plan->id_plan,
        'origen' => 'WEB',
        'modalidad' => 'VIRTUAL',
        'tipo_cliente' => 'INDIVIDUAL',
        'nombres' => 'Caja',
        'apellidos' => 'Manual',
        'documento_identidad' => '100200301',
        'correo' => 'caja.manual@test.local',
        'telefono' => '3001112244',
        'precio_lista' => $plan->precio,
        'descuento' => 0,
        'total' => $plan->precio,
        'acepto_terminos' => true,
        'estado_solicitud' => 'PENDIENTE',
    ]);

    $pago = Pago::query()->create([
        'id_solicitud' => $solicitud->id_solicitud,
        'id_plan' => $plan->id_plan,
        'monto' => $plan->precio,
        'moneda' => 'COP',
        'metodo_pago' => 'CAJA',
        'pasarela' => 'ADMIN',
        'estado_pago' => 'PENDIENTE',
    ]);

    $this->actingAs($admin)
        ->put(route('admin.pagos.update', $pago->id_pago), [
            'estado_pago' => 'VALIDADO',
            'observacion_admin' => 'Pago en caja recibido',
        ])
        ->assertRedirect(route('admin.pagos.show', $pago->id_pago));

    expect($solicitud->fresh()->estado_solicitud)->toBe('VALIDADA')
        ->and($pago->fresh()->estado_pago)->toBe('VALIDADO');
});

test('eliminar cliente desactiva el usuario y libera el cupo del horario', function () {
    $admin = procesoAdmin();
    $rolCliente = procesoRolId('CLIENTE');

    $horario = HorarioPresencial::query()->create([
        'codigo' => 'H-PROC-1',
        'nombre' => 'Mañana proceso',
        'dias' => 'Lunes a Viernes',
        'hora_inicio' => '07:00:00',
        'hora_fin' => '08:00:00',
        'cupo_maximo' => 10,
        'cupo_ocupado' => 1,
        'activo' => true,
        'orden' => 1,
    ]);

    $user = new User([
        'nombres' => 'Baja',
        'apellidos' => 'Cliente',
        'correo' => 'baja.cliente@test.local',
        'email' => 'baja.cliente@test.local',
        'estado' => 'ACTIVO',
        'estado_cuenta' => 'ACTIVO',
        'acepto_terminos' => true,
    ]);
    $user->assignPassword('cliente12345');
    $user->save();
    $user->roles()->attach($rolCliente);

    $cliente = Cliente::query()->create([
        'id_user' => $user->id_user,
        'modalidad' => 'PRESENCIAL',
        'estado_cliente' => 'ACTIVA',
        'id_horario' => $horario->id_horario,
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.clientes.destroy', $cliente->id_cliente))
        ->assertRedirect(route('admin.clientes.presenciales'));

    expect(Cliente::query()->find($cliente->id_cliente))->toBeNull()
        ->and($user->fresh()->estado)->toBe('INACTIVO')
        ->and((int) $horario->fresh()->cupo_ocupado)->toBe(0);
});

test('asignar coach a cliente virtual abre el chat coach-cliente', function () {
    $admin = procesoAdmin();
    $rolCliente = procesoRolId('CLIENTE');
    $rolCoach = procesoRolId('COACH');

    $coachUser = new User([
        'nombres' => 'Coach',
        'apellidos' => 'Proceso',
        'correo' => 'coach.proceso@test.local',
        'email' => 'coach.proceso@test.local',
        'estado' => 'ACTIVO',
        'estado_cuenta' => 'ACTIVO',
        'acepto_terminos' => true,
    ]);
    $coachUser->assignPassword('coach12345');
    $coachUser->save();
    $coachUser->roles()->attach($rolCoach);

    $coach = Coach::query()->create([
        'id_user' => $coachUser->id_user,
        'codigo_coach' => 'C-PROC-1',
        'especialidad' => 'Fuerza',
        'estado_coach' => 'ACTIVO',
    ]);

    $clienteUser = new User([
        'nombres' => 'Alumno',
        'apellidos' => 'Virtual',
        'correo' => 'alumno.virtual@test.local',
        'email' => 'alumno.virtual@test.local',
        'estado' => 'ACTIVO',
        'estado_cuenta' => 'ACTIVO',
        'acepto_terminos' => true,
    ]);
    $clienteUser->assignPassword('cliente12345');
    $clienteUser->save();
    $clienteUser->roles()->attach($rolCliente);

    $cliente = Cliente::query()->create([
        'id_user' => $clienteUser->id_user,
        'modalidad' => 'VIRTUAL',
        'estado_cliente' => 'ACTIVA',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.clientes.coach', $cliente->id_cliente), [
            'id_coach' => $coach->id_coach,
            'motivo' => 'Asignación de proceso',
        ])
        ->assertRedirect(route('admin.clientes.virtuales'));

    expect(Chat::query()->where('tipo', 'COACH_CLIENTE')->where('id_coach', $coach->id_coach)->exists())->toBeTrue();
});

test('no se elimina un horario con clientes asignados, se desactiva', function () {
    $admin = procesoAdmin();
    $rolCliente = procesoRolId('CLIENTE');

    $horario = HorarioPresencial::query()->create([
        'codigo' => 'H-PROC-2',
        'nombre' => 'Tarde proceso',
        'dias' => 'Lunes a Viernes',
        'hora_inicio' => '18:00:00',
        'hora_fin' => '19:00:00',
        'cupo_maximo' => 12,
        'cupo_ocupado' => 1,
        'activo' => true,
        'orden' => 2,
    ]);

    $user = new User([
        'nombres' => 'Sala',
        'apellidos' => 'Ocupada',
        'correo' => 'sala.ocupada@test.local',
        'email' => 'sala.ocupada@test.local',
        'estado' => 'ACTIVO',
        'acepto_terminos' => true,
    ]);
    $user->assignPassword('cliente12345');
    $user->save();
    $user->roles()->attach($rolCliente);

    Cliente::query()->create([
        'id_user' => $user->id_user,
        'modalidad' => 'PRESENCIAL',
        'estado_cliente' => 'ACTIVA',
        'id_horario' => $horario->id_horario,
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.horarios.destroy', $horario->id_horario))
        ->assertRedirect();

    $horario->refresh();
    expect(HorarioPresencial::query()->find($horario->id_horario))->not->toBeNull()
        ->and((bool) $horario->activo)->toBeFalse();
});
