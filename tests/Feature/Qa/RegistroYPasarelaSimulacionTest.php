<?php

use App\Models\ConfiguracionPasarela;
use App\Models\HorarioPresencial;
use App\Models\Plan;
use App\Models\SolicitudCompra;
use App\Models\TransaccionPasarela;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function ensureBaseData(): array
{
    $idRolCliente = DB::table('roles')->where('codigo', 'CLIENTE')->value('id_rol');
    if (! $idRolCliente) {
        $idRolCliente = DB::table('roles')->insertGetId([
            'codigo' => 'CLIENTE',
            'codigo_rol' => 'CLIENTE',
            'nombre' => 'Cliente',
            'estado' => 'ACTIVO',
        ]);
    }

    $planVirtual = Plan::firstOrCreate(
        ['codigo' => 'PLAN-TEST-VIRTUAL'],
        [
            'nombre' => 'Plan Virtual Test',
            'descripcion' => 'Plan virtual de pruebas',
            'modalidad' => 'VIRTUAL',
            'tipo_cliente' => 'INDIVIDUAL',
            'precio' => 75000,
            'moneda' => 'COP',
            'duracion_dias' => 30,
            'estado_plan' => 'ACTIVO',
            'slug' => 'plan-virtual-test',
        ]
    );

    $planPresencial = Plan::firstOrCreate(
        ['codigo' => 'PRESENCIAL-PLUS'],
        [
            'nombre' => 'Plan Presencial Sala',
            'descripcion' => 'Plan presencial de pruebas',
            'modalidad' => 'PRESENCIAL',
            'tipo_cliente' => 'INDIVIDUAL',
            'precio' => 90000,
            'moneda' => 'COP',
            'duracion_dias' => 30,
            'estado_plan' => 'ACTIVO',
            'slug' => 'plan-presencial-sala',
        ]
    );

    $horario = HorarioPresencial::firstOrCreate(
        ['codigo' => 'H-TEST-01'],
        [
            'nombre' => 'Mañana Sala A',
            'dias' => 'Lunes a Viernes',
            'hora_inicio' => '07:00:00',
            'hora_fin' => '08:00:00',
            'cupo_maximo' => 20,
            'cupo_ocupado' => 0,
            'activo' => true,
        ]
    );

    return compact('planVirtual', 'planPresencial', 'horario');
}

test('vista de pre-registro virtual muestra botones sutiles ver detalle y modal de politicas', function () {
    $data = ensureBaseData();
    $plan = $data['planVirtual'];

    $response = $this->get(route('pre-registro.create', $plan));

    $response->assertOk();
    $response->assertSee('Ver detalle');
    $response->assertSee('data-policy-trigger="recurrente"', false);
    $response->assertSee('data-policy-trigger="terminos"', false);
    $response->assertSee('ff-policy-modal');
    $response->assertSee('Política de Cobros Recurrentes y Suscripción');
    $response->assertSee('name="pago_mismos_datos"', false);
    $response->assertSee('Datos del pagador');
});

test('vista de registro presencial muestra botones sutiles ver detalle y modal de politicas', function () {
    $data = ensureBaseData();

    $response = $this->get(route('registro-presencial.create'));

    $response->assertOk();
    $response->assertSee('Ver detalle');
    $response->assertSee('data-policy-trigger="recurrente"', false);
    $response->assertSee('data-policy-trigger="terminos"', false);
    $response->assertSee('ff-policy-modal');
    $response->assertSee('Política de Cobros Recurrentes y Suscripción');
    $response->assertSee('name="pago_mismos_datos"', false);
    $response->assertSee('Datos del pagador');
});

test('pre-registro virtual permite cintura menor a 40cm y sin cobros recurrentes obligatorios', function () {
    $data = ensureBaseData();
    $plan = $data['planVirtual'];

    $payload = [
        'nombres' => 'Laura',
        'apellidos' => 'Gomez',
        'tipo_documento' => 'CC',
        'documento_identidad' => '1020304050',
        'correo' => 'laura.gomez.qa@test.com',
        'telefono' => '3001234567',
        'ciudad' => 'Campoalegre',
        'genero' => 'FEMENINO',
        'peso_kg' => 52.5,
        'estatura_cm' => 160,
        'cintura_cm' => 35.0, // menor a 40 cm
        'cadera_cm' => 88.0,
        'brazo_cm' => 24.0,
        'pierna_cm' => 50.0,
        'contacto_emergencia_nombre' => 'Carlos Gomez',
        'contacto_emergencia_telefono' => '3007654321',
        'acepto_terminos' => '1', // obligatorio
        // 'acepto_recurrente' omitido (NO es obligatorio)
        'metodo_pago' => 'TARJETA',
        'numero_tarjeta' => '4000000000000002',
        'vencimiento' => '12/28',
        'cvv' => '123',
        'titular' => 'Laura Gomez',
        'direccion_pago' => 'Calle 10 # 5-20',
        'ciudad_pago' => 'Campoalegre',
        'pago_tipo_documento' => 'CC',
        'pago_documento' => '1020304050',
        'pago_correo' => 'laura.gomez.qa@test.com',
        'pago_telefono' => '3001234567',
    ];

    $response = $this->post(route('pre-registro.store', $plan), $payload);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    $this->assertDatabaseHas('usuarios', [
        'correo' => 'laura.gomez.qa@test.com',
        'estado' => 'ACTIVO',
    ]);

    $this->assertDatabaseHas('solicitudes_compra', [
        'correo' => 'laura.gomez.qa@test.com',
        'estado_solicitud' => 'VALIDADA',
    ]);
});

test('pre-registro virtual exige terminos y condiciones con mensaje en espanol', function () {
    $data = ensureBaseData();
    $plan = $data['planVirtual'];

    $payload = [
        'nombres' => 'Andrea',
        'apellidos' => 'Ruiz',
        'tipo_documento' => 'CC',
        'documento_identidad' => '1020304051',
        'correo' => 'andrea.ruiz.qa@test.com',
        'telefono' => '3001234568',
        'ciudad' => 'Neiva',
        'genero' => 'FEMENINO',
        'peso_kg' => 55,
        'estatura_cm' => 165,
        'cintura_cm' => 38,
        'cadera_cm' => 90,
        'brazo_cm' => 26,
        'pierna_cm' => 52,
        'contacto_emergencia_nombre' => 'Maria Ruiz',
        'contacto_emergencia_telefono' => '3009876543',
        // 'acepto_terminos' ausente intencionalmente
        'pago_mismos_datos' => '1',
        'metodo_pago' => 'PSE',
        'banco_pse' => 'Bancolombia',
        'pago_tipo_documento' => 'CC',
        'pago_documento' => '1020304051',
        'pago_correo' => 'andrea.ruiz.qa@test.com',
        'pago_telefono' => '3001234568',
    ];

    $response = $this->post(route('pre-registro.store', $plan), $payload);

    $response->assertSessionHasErrors(['acepto_terminos']);
    $errors = session('errors')->get('acepto_terminos');
    expect($errors[0])->toContain('términos y condiciones');
});

test('registro presencial se completa con exito con cintura flexible y sin forzar recurrencia', function () {
    $data = ensureBaseData();
    $horario = $data['horario'];

    $payload = [
        'nombres' => 'Camila',
        'apellidos' => 'Perez',
        'tipo_documento' => 'CC',
        'documento_identidad' => '1099887766',
        'correo' => 'camila.presencial.qa@test.com',
        'telefono' => '3112233445',
        'ciudad' => 'Campoalegre',
        'fecha_nacimiento' => '1998-05-15',
        'genero' => 'FEMENINO',
        'objetivo_principal' => 'Acondicionamiento físico',
        'id_horario' => $horario->id_horario,
        'peso_kg' => 49.0,
        'estatura_cm' => 158.0,
        'cintura_cm' => 36.5, // menor a 40cm
        'cadera_cm' => 85.0,
        'brazo_cm' => 22.0,
        'pierna_cm' => 48.0,
        'contacto_emergencia_nombre' => 'Sofia Perez',
        'contacto_emergencia_telefono' => '3119988776',
        'acepto_terminos' => '1',
        // 'acepto_recurrente' NO enviado
        'metodo_pago' => 'TARJETA',
        'numero_tarjeta' => '4000000000000002',
        'vencimiento' => '10/27',
        'cvv' => '456',
        'titular' => 'Camila Perez',
        'direccion_pago' => 'Cra 5 # 3-10',
        'ciudad_pago' => 'Campoalegre',
        'pago_tipo_documento' => 'CC',
        'pago_documento' => '1099887766',
        'pago_correo' => 'camila.presencial.qa@test.com',
        'pago_telefono' => '3112233445',
    ];

    $response = $this->post(route('registro-presencial.store'), $payload);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    $this->assertDatabaseHas('usuarios', [
        'correo' => 'camila.presencial.qa@test.com',
        'estado' => 'ACTIVO',
    ]);

    $this->assertDatabaseHas('solicitudes_compra', [
        'correo' => 'camila.presencial.qa@test.com',
        'modalidad' => 'PRESENCIAL',
        'estado_solicitud' => 'VALIDADA',
    ]);
});

test('simulacion de pasarela aprueba compra incluso si todas las pasarelas en base de datos estan inactivas', function () {
    $data = ensureBaseData();
    $plan = $data['planVirtual'];

    // Forzar todas las pasarelas inactivas
    ConfiguracionPasarela::query()->update(['activa' => false]);

    $payload = [
        'nombres' => 'Diana',
        'apellidos' => 'Mora',
        'tipo_documento' => 'CC',
        'documento_identidad' => '1033445566',
        'correo' => 'diana.fallback.qa@test.com',
        'telefono' => '3123456789',
        'ciudad' => 'Neiva',
        'genero' => 'FEMENINO',
        'peso_kg' => 58,
        'estatura_cm' => 163,
        'cintura_cm' => 37,
        'cadera_cm' => 92,
        'brazo_cm' => 25,
        'pierna_cm' => 51,
        'contacto_emergencia_nombre' => 'Felipe Mora',
        'contacto_emergencia_telefono' => '3129876543',
        'acepto_terminos' => '1',
        'pago_mismos_datos' => '1',
        'metodo_pago' => 'PSE',
        'banco_pse' => 'Davivienda',
        'pago_tipo_documento' => 'CC',
        'pago_documento' => '1033445566',
        'pago_correo' => 'diana.fallback.qa@test.com',
        'pago_telefono' => '3123456789',
    ];

    $response = $this->post(route('pre-registro.store', $plan), $payload);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    $solicitud = SolicitudCompra::where('correo', 'diana.fallback.qa@test.com')->first();
    expect($solicitud)->not->toBeNull();
    expect($solicitud->estado_solicitud)->toBe('VALIDADA');
});

test('pre-registro propaga renovacion_automatica en verdadero cuando usuario marca voluntariamente acepto_recurrente', function () {
    $data = ensureBaseData();
    $plan = $data['planVirtual'];

    $payload = [
        'nombres' => 'Valeria',
        'apellidos' => 'Ospina',
        'tipo_documento' => 'CC',
        'documento_identidad' => '1044556677',
        'correo' => 'valeria.recurrente@test.com',
        'telefono' => '3151234567',
        'ciudad' => 'Campoalegre',
        'genero' => 'FEMENINO',
        'peso_kg' => 54,
        'estatura_cm' => 162,
        'cintura_cm' => 38,
        'cadera_cm' => 91,
        'brazo_cm' => 24,
        'pierna_cm' => 50,
        'contacto_emergencia_nombre' => 'Mateo Ospina',
        'contacto_emergencia_telefono' => '3159876543',
        'acepto_terminos' => '1',
        'acepto_recurrente' => '1', // voluntariamente marcado
        'metodo_pago' => 'TARJETA',
        'numero_tarjeta' => '4000000000000002',
        'vencimiento' => '11/27',
        'cvv' => '999',
        'titular' => 'Valeria Ospina',
        'direccion_pago' => 'Calle 8 # 4-12',
        'ciudad_pago' => 'Campoalegre',
        'pago_tipo_documento' => 'CC',
        'pago_documento' => '1044556677',
        'pago_correo' => 'valeria.recurrente@test.com',
        'pago_telefono' => '3151234567',
    ];

    $response = $this->post(route('pre-registro.store', $plan), $payload);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    $this->assertDatabaseHas('planes_cliente', [
        'renovacion_automatica' => 1,
    ]);
});

test('pre-registro rechaza datos de pago vacios cuando el pagador no se completa y no se marco la opcion de mismos datos', function () {
    $data = ensureBaseData();
    $plan = $data['planVirtual'];

    $payload = [
        'nombres' => 'Mariana',
        'apellidos' => 'Castro',
        'tipo_documento' => 'CC',
        'documento_identidad' => '1055667788',
        'correo' => 'mariana.castro@test.com',
        'telefono' => '3161234567',
        'ciudad' => 'Campoalegre',
        'genero' => 'FEMENINO',
        'peso_kg' => 50,
        'estatura_cm' => 159,
        'cintura_cm' => 34,
        'cadera_cm' => 87,
        'brazo_cm' => 23,
        'pierna_cm' => 49,
        'contacto_emergencia_nombre' => 'Elena Castro',
        'contacto_emergencia_telefono' => '3169876543',
        'acepto_terminos' => '1',
        // Omite intencionalmente los datos del pagador y NO marca pago_mismos_datos
        'metodo_pago' => 'TARJETA',
        'numero_tarjeta' => '4000000000000002',
        'vencimiento' => '05/28',
        'cvv' => '789',
        'direccion_pago' => 'Barrio Centro',
    ];

    $response = $this->post(route('pre-registro.store', $plan), $payload);

    $response->assertSessionHasErrors([
        'pago_documento',
        'pago_correo',
        'pago_telefono',
        'titular',
        'ciudad_pago',
    ]);
});

test('pre-registro permite reutilizar datos personales solo cuando el usuario marca explícitamente pago_mismos_datos', function () {
    $data = ensureBaseData();
    $plan = $data['planVirtual'];

    $payload = [
        'nombres' => 'Mariana',
        'apellidos' => 'Castro',
        'tipo_documento' => 'CC',
        'documento_identidad' => '1055667788',
        'correo' => 'mariana.castro@test.com',
        'telefono' => '3161234567',
        'ciudad' => 'Campoalegre',
        'genero' => 'FEMENINO',
        'peso_kg' => 50,
        'estatura_cm' => 159,
        'cintura_cm' => 34,
        'cadera_cm' => 87,
        'brazo_cm' => 23,
        'pierna_cm' => 49,
        'contacto_emergencia_nombre' => 'Elena Castro',
        'contacto_emergencia_telefono' => '3169876543',
        'acepto_terminos' => '1',
        'pago_mismos_datos' => '1', // El usuario solicita explícitamente reutilizar sus datos
        'metodo_pago' => 'TARJETA',
        'numero_tarjeta' => '4000000000000002',
        'vencimiento' => '05/28',
        'cvv' => '789',
        'direccion_pago' => 'Barrio Centro',
    ];

    $response = $this->post(route('pre-registro.store', $plan), $payload);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    $this->assertDatabaseHas('usuarios', [
        'correo' => 'mariana.castro@test.com',
    ]);
});

test('pre-registro y pasarela aceptan y respetan al 100% datos de un tercero pagador (conyuge o familiar)', function () {
    $data = ensureBaseData();
    $plan = $data['planVirtual'];

    // Alumna: Sofia Martinez
    // Pagador: Jorge Ramirez (esposo, con cédula, correo, teléfono y ciudad diferentes)
    $payload = [
        'nombres' => 'Sofia',
        'apellidos' => 'Martinez',
        'tipo_documento' => 'CC',
        'documento_identidad' => '1088776655',
        'correo' => 'sofia.alumna@test.com',
        'telefono' => '3181112233',
        'ciudad' => 'Campoalegre',
        'genero' => 'FEMENINO',
        'peso_kg' => 56,
        'estatura_cm' => 164,
        'cintura_cm' => 38,
        'cadera_cm' => 92,
        'brazo_cm' => 25,
        'pierna_cm' => 51,
        'contacto_emergencia_nombre' => 'Jorge Ramirez',
        'contacto_emergencia_telefono' => '3109998877',
        'acepto_terminos' => '1',
        // Datos del pagador (esposo) totalmente distintos
        'metodo_pago' => 'TARJETA',
        'numero_tarjeta' => '4000000000000002',
        'vencimiento' => '09/27',
        'cvv' => '321',
        'titular' => 'Jorge Ramirez',
        'direccion_pago' => 'Av. Circunvalar # 12-40',
        'ciudad_pago' => 'Neiva',
        'pago_tipo_documento' => 'CE',
        'pago_documento' => '987654321',
        'pago_correo' => 'jorge.esposo.pagador@empresa.com',
        'pago_telefono' => '3109998877',
    ];

    $response = $this->post(route('pre-registro.store', $plan), $payload);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    // 1. El usuario y cliente creado en el sistema pertenecen a la alumna
    $this->assertDatabaseHas('usuarios', [
        'correo' => 'sofia.alumna@test.com',
        'nombres' => 'Sofia',
        'apellidos' => 'Martinez',
        'documento_identidad' => '1088776655',
    ]);

    // 2. La solicitud de compra registra los datos personales de la alumna y los datos del pagador
    $solicitud = SolicitudCompra::where('correo', 'sofia.alumna@test.com')->first();
    expect($solicitud)->not->toBeNull();
    expect($solicitud->estado_solicitud)->toBe('VALIDADA');
    expect($solicitud->nombres)->toBe('Sofia');
    expect($solicitud->datos_extra_json['pagador']['titular'])->toBe('Jorge Ramirez');
    expect($solicitud->datos_extra_json['pagador']['documento'])->toBe('987654321');
    expect($solicitud->datos_extra_json['pagador']['correo'])->toBe('jorge.esposo.pagador@empresa.com');

    // 3. La transacción de la pasarela registra el titular y datos del pagador tercero sin sobrescribir
    $transaccion = TransaccionPasarela::where('id_solicitud', $solicitud->id_solicitud)->first();
    expect($transaccion)->not->toBeNull();
    expect($transaccion->payload_envio['datos']['titular'])->toBe('Jorge Ramirez');
    expect($transaccion->payload_envio['datos']['documento'])->toBe('987654321');
    expect($transaccion->payload_envio['datos']['correo'])->toBe('jorge.esposo.pagador@empresa.com');
    expect($transaccion->payload_envio['datos']['tipo_documento'])->toBe('CE');
});

test('registro presencial acepta pagador tercero con PSE y respeta sus datos independientes', function () {
    $data = ensureBaseData();
    $horario = $data['horario'];

    // Alumna presencial: Daniela Vargas
    // Pagador PSE: Carlos Vargas (padre)
    $payload = [
        'nombres' => 'Daniela',
        'apellidos' => 'Vargas',
        'tipo_documento' => 'TI',
        'documento_identidad' => '1099001122',
        'correo' => 'daniela.hija@test.com',
        'telefono' => '3141112233',
        'ciudad' => 'Campoalegre',
        'fecha_nacimiento' => '2005-08-20',
        'genero' => 'FEMENINO',
        'objetivo_principal' => 'Tonificación',
        'id_horario' => $horario->id_horario,
        'peso_kg' => 51.0,
        'estatura_cm' => 161.0,
        'cintura_cm' => 37.0,
        'cadera_cm' => 89.0,
        'brazo_cm' => 23.5,
        'pierna_cm' => 49.0,
        'contacto_emergencia_nombre' => 'Carlos Vargas',
        'contacto_emergencia_telefono' => '3102223344',
        'acepto_terminos' => '1',
        // Pagador PSE (padre)
        'metodo_pago' => 'PSE',
        'banco_pse' => 'Bancolombia',
        'titular' => 'Carlos Vargas',
        'ciudad_pago' => 'Neiva',
        'pago_tipo_documento' => 'CC',
        'pago_documento' => '19876543',
        'pago_correo' => 'carlos.papa.pagador@banco.com',
        'pago_telefono' => '3102223344',
    ];

    $response = $this->post(route('registro-presencial.store'), $payload);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    $this->assertDatabaseHas('usuarios', [
        'correo' => 'daniela.hija@test.com',
        'nombres' => 'Daniela',
        'documento_identidad' => '1099001122',
    ]);

    $solicitud = SolicitudCompra::where('correo', 'daniela.hija@test.com')->first();
    expect($solicitud)->not->toBeNull();
    expect($solicitud->datos_extra_json['pagador']['titular'])->toBe('Carlos Vargas');
    expect($solicitud->datos_extra_json['pagador']['ciudad'])->toBe('Neiva');
    expect($solicitud->datos_extra_json['pagador']['documento'])->toBe('19876543');
    expect($solicitud->datos_extra_json['pagador']['correo'])->toBe('carlos.papa.pagador@banco.com');

    $transaccion = TransaccionPasarela::where('id_solicitud', $solicitud->id_solicitud)->first();
    expect($transaccion)->not->toBeNull();
    expect($transaccion->payload_envio['datos']['titular'])->toBe('Carlos Vargas');
    expect($transaccion->payload_envio['datos']['ciudad'])->toBe('Neiva');
});

test('validacion de cintura no permite valores menores o iguales a cero y responde en espanol', function () {
    $data = ensureBaseData();
    $plan = $data['planVirtual'];

    $payload = [
        'nombres' => 'Lucia',
        'apellidos' => 'Mendez',
        'tipo_documento' => 'CC',
        'documento_identidad' => '1066778899',
        'correo' => 'lucia.mendez@test.com',
        'telefono' => '3171234567',
        'ciudad' => 'Neiva',
        'genero' => 'FEMENINO',
        'peso_kg' => 52,
        'estatura_cm' => 160,
        'cintura_cm' => 0, // Invalido: menor o igual a cero
        'cadera_cm' => 88,
        'brazo_cm' => 24,
        'pierna_cm' => 50,
        'contacto_emergencia_nombre' => 'Rosa Mendez',
        'contacto_emergencia_telefono' => '3179876543',
        'acepto_terminos' => '1',
        'pago_mismos_datos' => '1',
        'metodo_pago' => 'PSE',
        'banco_pse' => 'Bancolombia',
        'pago_tipo_documento' => 'CC',
        'pago_documento' => '1066778899',
        'pago_correo' => 'lucia.mendez@test.com',
        'pago_telefono' => '3171234567',
    ];

    $response = $this->post(route('pre-registro.store', $plan), $payload);

    $response->assertSessionHasErrors(['cintura_cm']);
    $error = session('errors')->get('cintura_cm')[0];
    expect($error)->toBe('La medida de cintura debe ser mayor a 0.');
});

test('pre-registro rechaza datos de pagador vacios en PSE cuando no se activa la opcion de mismos datos', function () {
    $data = ensureBaseData();
    $plan = $data['planVirtual'];

    $payload = [
        'nombres' => 'Valeria',
        'apellidos' => 'Suarez',
        'tipo_documento' => 'CC',
        'documento_identidad' => '1077889900',
        'correo' => 'valeria.suarez@test.com',
        'telefono' => '3189998877',
        'ciudad' => 'Campoalegre',
        'genero' => 'FEMENINO',
        'peso_kg' => 53,
        'estatura_cm' => 161,
        'cintura_cm' => 36,
        'cadera_cm' => 89,
        'brazo_cm' => 24,
        'pierna_cm' => 50,
        'contacto_emergencia_nombre' => 'Pedro Suarez',
        'contacto_emergencia_telefono' => '3187776655',
        'acepto_terminos' => '1',
        // Omite datos del pagador y no envía pago_mismos_datos
        'metodo_pago' => 'PSE',
        'banco_pse' => 'Bancolombia',
    ];

    $response = $this->post(route('pre-registro.store', $plan), $payload);

    $response->assertSessionHasErrors([
        'titular',
        'pago_tipo_documento',
        'pago_documento',
        'pago_correo',
        'pago_telefono',
        'ciudad_pago',
    ]);
});

test('registro con pagador con pasaporte conteniendo letras y guiones en PSE procesa correctamente', function () {
    $data = ensureBaseData();
    $plan = $data['planVirtual'];

    // Alumna: Marcela Castro
    // Pagador PSE: John Smith (extranjero con pasaporte con letras y guiones)
    $payload = [
        'nombres' => 'Marcela',
        'apellidos' => 'Castro',
        'tipo_documento' => 'CC',
        'documento_identidad' => '1099887711',
        'correo' => 'marcela.alumna@test.com',
        'telefono' => '3101112233',
        'ciudad' => 'Campoalegre',
        'genero' => 'FEMENINO',
        'peso_kg' => 55,
        'estatura_cm' => 162,
        'cintura_cm' => 37,
        'cadera_cm' => 90,
        'brazo_cm' => 25,
        'pierna_cm' => 51,
        'contacto_emergencia_nombre' => 'John Smith',
        'contacto_emergencia_telefono' => '+13059998888',
        'acepto_terminos' => '1',
        'metodo_pago' => 'PSE',
        'banco_pse' => 'Nequi',
        'titular' => 'John Smith Jr.',
        'pago_tipo_documento' => 'PP',
        'pago_documento' => 'PAS-98745-COL',
        'pago_correo' => 'john.smith@empresa.com',
        'pago_telefono' => '+13059998888',
        'ciudad_pago' => 'Miami',
    ];

    $response = $this->post(route('pre-registro.store', $plan), $payload);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    $solicitud = SolicitudCompra::where('correo', 'marcela.alumna@test.com')->first();
    expect($solicitud)->not->toBeNull();
    expect($solicitud->datos_extra_json['pagador']['titular'])->toBe('John Smith Jr.');
    expect($solicitud->datos_extra_json['pagador']['documento'])->toBe('PAS-98745-COL');
    expect($solicitud->datos_extra_json['pagador']['tipo_documento'])->toBe('PP');
    expect($solicitud->datos_extra_json['pagador']['ciudad'])->toBe('Miami');

    $transaccion = TransaccionPasarela::where('id_solicitud', $solicitud->id_solicitud)->first();
    expect($transaccion)->not->toBeNull();
    expect($transaccion->payload_envio['datos']['titular'])->toBe('John Smith Jr.');
    expect($transaccion->payload_envio['datos']['documento'])->toBe('PAS-98745-COL');
    expect($transaccion->payload_envio['datos']['ciudad'])->toBe('Miami');
});
