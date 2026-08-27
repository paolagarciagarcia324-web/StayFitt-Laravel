<?php

use App\Models\AccesoClienteModulo;
use App\Models\Chat;
use App\Models\ChatParticipante;
use App\Models\Cliente;
use App\Models\Coach;
use App\Models\ConfiguracionWhatsapp;
use App\Models\GrupoWhatsapp;
use App\Models\Mensaje;
use App\Models\ModuloServicio;
use App\Models\Plan;
use App\Models\PlanCliente;
use App\Models\Rol;
use App\Models\User;
use App\Services\Comunicacion\ChatService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createComunicacionModulo(): ModuloServicio
{
    return ModuloServicio::query()->firstOrCreate(
        ['codigo' => 'COMUNICACION'],
        [
            'nombre' => 'Comunicación',
            'estado' => 'ACTIVO',
        ]
    );
}

function createClienteConModulo(): array
{
    $modulo = createComunicacionModulo();

    $rolCliente = Rol::query()->firstOrCreate(
        ['codigo' => 'CLIENTE'],
        [
            'codigo_rol' => 'CLIENTE',
            'nombre' => 'Cliente',
            'estado' => 'ACTIVO',
        ]
    );

    $rolCoach = Rol::query()->firstOrCreate(
        ['codigo' => 'COACH'],
        [
            'codigo_rol' => 'COACH',
            'nombre' => 'Coach',
            'estado' => 'ACTIVO',
        ]
    );

    $coachUser = new User([
        'nombres' => 'Coach',
        'apellidos' => 'Test',
        'correo' => 'coach-com@test.local',
        'email' => 'coach-com@test.local',
        'estado' => 'ACTIVO',
        'estado_cuenta' => 'ACTIVO',
        'acepto_terminos' => true,
    ]);
    $coachUser->assignPassword('coach12345');
    $coachUser->save();
    $coachUser->roles()->attach($rolCoach->id_rol);

    $coach = Coach::query()->create([
        'id_user' => $coachUser->id_user,
        'codigo_coach' => 'C-TEST01',
        'especialidad' => 'General',
        'estado_coach' => 'ACTIVO',
    ]);

    $clienteUser = new User([
        'nombres' => 'Cliente',
        'apellidos' => 'Com',
        'correo' => 'cliente-com@test.local',
        'email' => 'cliente-com@test.local',
        'estado' => 'ACTIVO',
        'estado_cuenta' => 'ACTIVO',
        'acepto_terminos' => true,
    ]);
    $clienteUser->assignPassword('cliente12345');
    $clienteUser->save();
    $clienteUser->roles()->attach($rolCliente->id_rol);

    $cliente = Cliente::query()->create([
        'id_user' => $clienteUser->id_user,
        'id_coach' => $coach->id_coach,
        'modalidad' => 'VIRTUAL',
        'tipo_cliente' => 'INDIVIDUAL',
        'estado_cliente' => 'ACTIVA',
        'fecha_alta' => now()->toDateString(),
    ]);

    $plan = Plan::query()->create([
        'nombre' => 'Plan test',
        'slug' => 'plan-test-com',
        'modalidad' => 'VIRTUAL',
        'duracion_dias' => 30,
        'precio' => 100000,
        'estado_plan' => 'ACTIVO',
    ]);

    $planCliente = PlanCliente::query()->create([
        'id_cliente' => $cliente->id_cliente,
        'id_plan' => $plan->id_plan,
        'fecha_inicio' => now()->toDateString(),
        'fecha_fin' => now()->addDays(30)->toDateString(),
        'estado_plan_cliente' => 'ACTIVO',
    ]);

    AccesoClienteModulo::query()->create([
        'id_plan_cliente' => $planCliente->id_plan_cliente,
        'id_cliente' => $cliente->id_cliente,
        'id_modulo' => $modulo->id_modulo,
        'fecha_inicio' => now()->toDateString(),
        'fecha_fin' => now()->addDays(30)->toDateString(),
        'estado_acceso' => 'ACTIVO',
    ]);

    return compact('coachUser', 'coach', 'clienteUser', 'cliente', 'plan');
}

test('cliente sin módulo comunicacion recibe 403', function () {
    $rol = Rol::query()->firstOrCreate(
        ['codigo' => 'CLIENTE'],
        [
            'codigo_rol' => 'CLIENTE',
            'nombre' => 'Cliente',
            'estado' => 'ACTIVO',
        ]
    );

    $user = new User([
        'nombres' => 'Sin',
        'apellidos' => 'Modulo',
        'correo' => 'sin-mod@test.local',
        'email' => 'sin-mod@test.local',
        'estado' => 'ACTIVO',
        'estado_cuenta' => 'ACTIVO',
        'acepto_terminos' => true,
    ]);
    $user->assignPassword('test12345');
    $user->save();
    $user->roles()->attach($rol->id_rol);

    Cliente::query()->create([
        'id_user' => $user->id_user,
        'modalidad' => 'VIRTUAL',
        'tipo_cliente' => 'INDIVIDUAL',
        'estado_cliente' => 'ACTIVA',
        'fecha_alta' => now()->toDateString(),
    ]);

    $this->actingAs($user)
        ->get(route('cliente.comunicacion.index'))
        ->assertForbidden();
});

test('cliente con módulo puede ver comunicacion', function () {
    ['clienteUser' => $user] = createClienteConModulo();

    $this->actingAs($user)
        ->get(route('cliente.comunicacion.index'))
        ->assertOk();
});

test('cliente puede enviar mensaje en chat coach', function () {
    $data = createClienteConModulo();
    $chatService = app(ChatService::class);
    $chat = $chatService->obtenerOcrearChatCoachCliente($data['cliente']->fresh('usuario'));

    expect($chat)->not->toBeNull();

    $this->actingAs($data['clienteUser'])
        ->post(route('cliente.comunicacion.mensajes.store', $chat->id_chat), [
            'contenido' => 'Hola coach, tengo una duda.',
        ])
        ->assertRedirect();

    expect(Mensaje::query()->where('id_chat', $chat->id_chat)->count())->toBe(1);
});

test('coach puede listar y responder chats', function () {
    $data = createClienteConModulo();
    $chatService = app(ChatService::class);
    $chat = $chatService->obtenerOcrearChatCoachCliente($data['cliente']->fresh('usuario'));

    $this->actingAs($data['coachUser'])
        ->get(route('coach.comunicacion.index'))
        ->assertOk();

    $this->actingAs($data['coachUser'])
        ->post(route('coach.comunicacion.mensajes.store', $chat->id_chat), [
            'contenido' => 'Hola, ¿en qué te ayudo?',
        ])
        ->assertRedirect();

    expect(Mensaje::query()->where('id_chat', $chat->id_chat)->count())->toBe(1);
});

test('admin puede cerrar chat y eliminar mensaje', function () {
    $data = createClienteConModulo();
    $chatService = app(ChatService::class);
    $chat = $chatService->obtenerOcrearChatCoachCliente($data['cliente']->fresh('usuario'));
    $mensaje = $chatService->enviarMensaje($chat, $data['clienteUser'], 'Mensaje a moderar');

    $adminRol = Rol::query()->firstOrCreate(
        ['codigo' => 'ADMIN'],
        [
            'codigo_rol' => 'ADMIN',
            'nombre' => 'Admin',
            'estado' => 'ACTIVO',
        ]
    );
    $admin = new User([
        'nombres' => 'Admin',
        'apellidos' => 'Com',
        'correo' => 'admin-com@test.local',
        'email' => 'admin-com@test.local',
        'estado' => 'ACTIVO',
        'estado_cuenta' => 'ACTIVO',
        'acepto_terminos' => true,
    ]);
    $admin->assignPassword('admin12345');
    $admin->save();
    $admin->roles()->attach($adminRol->id_rol);

    $this->actingAs($admin)
        ->put(route('admin.comunidad.update', $chat->id_chat), ['estado' => 'CERRADO'])
        ->assertRedirect();

    expect($chat->fresh()->estado)->toBe('CERRADO');

    $this->actingAs($admin)
        ->delete(route('admin.comunidad.mensajes.destroy', [$chat->id_chat, $mensaje->id_mensaje]))
        ->assertRedirect();

    expect(Mensaje::query()->find($mensaje->id_mensaje))->toBeNull();
});

test('webhook whatsapp verify responde challenge con token valido', function () {
    ConfiguracionWhatsapp::query()->update(['activa' => false]);

    ConfiguracionWhatsapp::query()->create([
        'numero_business' => '+573001234567',
        'activa' => true,
        'api_token_cifrado' => \Illuminate\Support\Facades\Crypt::encryptString('verify-token-test'),
    ]);

    $this->get('/webhooks/whatsapp?'.http_build_query([
        'hub_mode' => 'subscribe',
        'hub_verify_token' => 'verify-token-test',
        'hub_challenge' => '12345',
    ]))->assertOk()->assertSee('12345');
});
