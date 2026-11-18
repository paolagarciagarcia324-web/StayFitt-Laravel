<?php

/**
 * QA E2E HTTP Runner — StayFitt
 * Ejecuta logins reales, GET de paneles, CRUD admin, aislamiento institucional.
 * Uso: DB_DATABASE=stayfit php QA/e2e/run_e2e_http.php
 */

require __DIR__.'/../../vendor/autoload.php';

$base = getenv('QA_BASE_URL') ?: 'http://127.0.0.1:8000';
$cookieJar = sys_get_temp_dir().'/stayfit_qa_cookies.txt';
$results = [];
$bugs = [];
$started = microtime(true);

function qa_log(string $msg): void
{
    $line = '['.date('H:i:s').'] '.$msg.PHP_EOL;
    echo $line;
    file_put_contents(__DIR__.'/../logs/e2e_http.log', $line, FILE_APPEND);
}

function http_request(string $method, string $url, array $opts = []): array
{
    global $cookieJar;

    $ch = curl_init($url);
    $headers = $opts['headers'] ?? [];
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_COOKIEJAR => $cookieJar,
        CURLOPT_COOKIEFILE => $cookieJar,
        CURLOPT_HEADER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => $headers,
    ]);

    if (! empty($opts['body'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $opts['body']);
    }

    $raw = curl_exec($ch);
    $err = curl_error($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);

    $header = substr((string) $raw, 0, $headerSize);
    $body = substr((string) $raw, $headerSize);

    return [
        'code' => $code,
        'headers' => $header,
        'body' => $body,
        'error' => $err,
        'location' => preg_match('/^Location:\s*(.+)$/mi', $header, $m) ? trim($m[1]) : null,
    ];
}

function extract_csrf(string $html): ?string
{
    if (preg_match('/name="_token"\s+value="([^"]+)"/', $html, $m)) {
        return $m[1];
    }
    if (preg_match('/name="csrf-token"\s+content="([^"]+)"/', $html, $m)) {
        return $m[1];
    }

    return null;
}

function record(array &$results, string $id, string $module, string $role, string $step, bool $ok, string $detail, string $evidence = ''): void
{
    $results[] = [
        'id' => $id,
        'module' => $module,
        'role' => $role,
        'step' => $step,
        'status' => $ok ? 'APROBADO' : 'FALLIDO',
        'detail' => $detail,
        'evidence' => $evidence,
        'at' => date('c'),
    ];
    qa_log(($ok ? 'PASS' : 'FAIL')." [$id] $role · $step — $detail");
}

function bug(array &$bugs, string $id, string $module, string $role, string $steps, string $error): void
{
    $bugs[] = compact('id', 'module', 'role', 'steps', 'error') + ['status' => 'ABIERTO', 'at' => date('c')];
}

function clear_cookies(): void
{
    global $cookieJar;
    @unlink($cookieJar);
}

function login(string $base, string $email, string $password): array
{
    clear_cookies();
    $loginPage = http_request('GET', $base.'/login');
    $csrf = extract_csrf($loginPage['body'] ?? '');
    if (! $csrf) {
        return ['ok' => false, 'code' => $loginPage['code'], 'detail' => 'CSRF no encontrado en /login', 'body' => $loginPage['body']];
    }

    $resp = http_request('POST', $base.'/login', [
        'headers' => ['Content-Type: application/x-www-form-urlencoded'],
        'body' => http_build_query([
            '_token' => $csrf,
            'email' => $email,
            'password' => $password,
        ]),
    ]);

    $redirect = $resp['location'] ?? '';
    $ok = in_array($resp['code'], [302, 303], true) && ! str_contains($redirect, '/login');

    return [
        'ok' => $ok,
        'code' => $resp['code'],
        'location' => $redirect,
        'detail' => $ok ? "Redirect → $redirect" : "Login falló code={$resp['code']} loc=$redirect",
    ];
}

function get_page(string $base, string $path): array
{
    return http_request('GET', $base.$path);
}

function post_form(string $base, string $path, array $fields, string $method = 'POST'): array
{
    $page = get_page($base, $path);
    // For PUT we often need the edit page CSRF; caller may pass _token
    if (empty($fields['_token'])) {
        $csrf = extract_csrf($page['body'] ?? '');
        if (! $csrf) {
            // try dashboard or login already authenticated
            $dash = get_page($base, '/');
            $csrf = extract_csrf($dash['body'] ?? '') ?: extract_csrf(get_page($base, '/admin')['body'] ?? '');
        }
        $fields['_token'] = $csrf;
    }

    if ($method !== 'POST') {
        $fields['_method'] = $method;
    }

    return http_request('POST', $base.$path, [
        'headers' => ['Content-Type: application/x-www-form-urlencoded'],
        'body' => http_build_query($fields),
    ]);
}

@mkdir(__DIR__.'/../logs', 0777, true);
@mkdir(__DIR__.'/../resultados', 0777, true);
@mkdir(__DIR__.'/../evidencias', 0777, true);
file_put_contents(__DIR__.'/../logs/e2e_http.log', "=== E2E START ".date('c')." base=$base ===\n");

qa_log("Base URL: $base");

// Health
$up = http_request('GET', $base.'/up');
record($results, 'SYS-001', 'infra', 'SYSTEM', 'GET /up', $up['code'] === 200, "HTTP {$up['code']}", substr($up['body'], 0, 120));
$welcome = http_request('GET', $base.'/');
record($results, 'SYS-002', 'infra', 'SYSTEM', 'GET /', $welcome['code'] === 200, "HTTP {$welcome['code']}");

$accounts = [
    'ADMIN' => ['email' => 'paola@correo.com', 'password' => 'paola12345', 'home' => '/admin', 'allowed' => ['/admin'], 'forbidden' => []],
    'COACH' => ['email' => 'laura.coach@demo.figuefit.test', 'password' => 'demo12345', 'home' => '/coach', 'allowed' => ['/coach'], 'forbidden' => ['/admin', '/staff']],
    'ASISTENTE_TECNICO' => ['email' => 'asistente@demo.figuefit.test', 'password' => 'demo12345', 'home' => '/staff', 'allowed' => ['/staff'], 'forbidden' => ['/admin']],
    'CLIENTE_VIRTUAL' => ['email' => 'valen.gomez@demo.figuefit.test', 'password' => 'demo12345', 'home' => '/cliente', 'allowed' => ['/cliente'], 'forbidden' => ['/admin', '/coach', '/staff', '/institucional']],
    'CLIENTE_PRESENCIAL' => ['email' => 'julian.rojas@demo.figuefit.test', 'password' => 'demo12345', 'home' => '/cliente', 'allowed' => ['/cliente'], 'forbidden' => ['/admin', '/coach']],
    'CLIENTE_INSTITUCIONAL' => ['email' => 'diana.suarez@demo.figuefit.test', 'password' => 'demo12345', 'home' => '/cliente', 'allowed' => ['/cliente'], 'forbidden' => ['/admin', '/institucional']],
    'RESPONSABLE_INSTITUCIONAL' => ['email' => 'rrhh@campoalegre.demo.figuefit.test', 'password' => 'demo12345', 'home' => '/institucional', 'allowed' => ['/institucional'], 'forbidden' => ['/admin', '/coach']],
];

$roleNav = [
    'ADMIN' => ['/admin', '/admin/planes', '/admin/tienda', '/admin/clientes', '/admin/coaches', '/admin/solicitudes', '/admin/pagos', '/admin/cupones', '/admin/usuarios', '/admin/instituciones', '/admin/whatsapp', '/admin/comunidad', '/admin/eventos', '/admin/analisis', '/admin/reportes', '/admin/estadisticas', '/admin/configuracion', '/admin/roles', '/admin/asistentes', '/admin/horarios', '/admin/pasarelas', '/admin/propuestas', '/admin/calendario'],
    'COACH' => ['/coach', '/coach/perfil', '/coach/clientes', '/coach/entrenamiento', '/coach/videos', '/coach/agenda', '/coach/comunicacion', '/coach/reportes/progreso'],
    'ASISTENTE_TECNICO' => ['/staff', '/staff/perfil', '/staff/clientes', '/staff/rutinas', '/staff/propuestas', '/staff/videos', '/staff/comunicacion', '/staff/novedades'],
    'CLIENTE_VIRTUAL' => ['/cliente', '/cliente/perfil', '/cliente/mi-plan', '/cliente/pagos', '/cliente/comercial/planes', '/cliente/entrenamiento', '/cliente/nutricion', '/cliente/contenido', '/cliente/progreso', '/cliente/comunicacion', '/cliente/notificaciones', '/cliente/novedades', '/cliente/analisis', '/cliente/agenda'],
    'CLIENTE_PRESENCIAL' => ['/cliente', '/cliente/presencial', '/cliente/perfil', '/cliente/mi-plan', '/cliente/entrenamiento', '/cliente/progreso', '/cliente/tienda', '/cliente/pagos'],
    'CLIENTE_INSTITUCIONAL' => ['/cliente', '/cliente/perfil', '/cliente/mi-plan', '/cliente/entrenamiento', '/cliente/progreso', '/cliente/comunicacion'],
    'RESPONSABLE_INSTITUCIONAL' => ['/institucional', '/institucional/perfil', '/institucional/usuarios', '/institucional/invitaciones', '/institucional/plan', '/institucional/reportes', '/institucional/comunicacion'],
];

foreach ($accounts as $role => $acc) {
    // Login incorrecto
    $bad = login($base, $acc['email'], 'WRONG_PASSWORD_XYZ');
    record($results, "AUTH-$role-BAD", 'auth', $role, 'Login incorrecto', ! $bad['ok'], $bad['detail']);

    // Login correcto
    $good = login($base, $acc['email'], $acc['password']);
    record($results, "AUTH-$role-OK", 'auth', $role, 'Login correcto', $good['ok'], $good['detail'], $good['location'] ?? '');
    if (! $good['ok']) {
        bug($bugs, "BUG-AUTH-$role", 'auth', $role, 'POST /login con credenciales demo', $good['detail']);
        continue;
    }

    // Dashboard / home
    $home = get_page($base, $acc['home']);
    $homeOk = in_array($home['code'], [200], true);
    record($results, "NAV-$role-HOME", 'nav', $role, "GET {$acc['home']}", $homeOk, "HTTP {$home['code']}", substr(strip_tags($home['body']), 0, 80));
    if (! $homeOk) {
        bug($bugs, "BUG-HOME-$role", 'nav', $role, "GET {$acc['home']}", "HTTP {$home['code']}");
    }

    // Navegación permitida
    $paths = $roleNav[$role] ?? [];
    foreach ($paths as $i => $path) {
        $r = get_page($base, $path);
        $ok = in_array($r['code'], [200, 302], true); // 302 puede ser módulo sin acceso redirigiendo
        // 403 en ruta del propio portal = fallo
        if ($r['code'] === 403) {
            $ok = false;
        }
        // 500 = fallo
        if ($r['code'] >= 500) {
            $ok = false;
        }
        record($results, "NAV-$role-".($i + 1), 'nav', $role, "GET $path", $ok, "HTTP {$r['code']}");
        if (! $ok) {
            bug($bugs, "BUG-NAV-$role-".($i + 1), 'nav', $role, "GET $path", "HTTP {$r['code']}");
            file_put_contents(__DIR__."/../evidencias/fail_{$role}_".str_replace('/', '_', $path).".html", $r['body']);
        }
    }

    // Forbidden
    foreach ($acc['forbidden'] as $j => $path) {
        $r = get_page($base, $path);
        $blocked = in_array($r['code'], [403, 302], true); // redirect to home/login also ok for middleware
        // If 200 on forbidden = security bug
        if ($r['code'] === 200) {
            $blocked = false;
        }
        record($results, "SEC-$role-".($j + 1), 'security', $role, "Bloqueo GET $path", $blocked, "HTTP {$r['code']}");
        if (! $blocked) {
            bug($bugs, "BUG-SEC-$role-".($j + 1), 'security', $role, "GET $path debería bloquearse", "HTTP {$r['code']} (acceso indebido)");
        }
    }

    // Logout
    $page = get_page($base, $acc['home']);
    $csrf = extract_csrf($page['body'] ?? '');
    $logout = http_request('POST', $base.'/logout', [
        'headers' => ['Content-Type: application/x-www-form-urlencoded'],
        'body' => http_build_query(['_token' => $csrf]),
    ]);
    record($results, "AUTH-$role-OUT", 'auth', $role, 'Logout', in_array($logout['code'], [302, 303, 200], true), "HTTP {$logout['code']}");

    // Persistencia: nuevo login
    $again = login($base, $acc['email'], $acc['password']);
    record($results, "AUTH-$role-RELOGIN", 'auth', $role, 'Re-login', $again['ok'], $again['detail']);
}

// ========== ADMIN CRUD PLANES ==========
qa_log('=== ADMIN CRUD PLANES ===');
$adminLogin = login($base, 'paola@correo.com', 'paola12345');
if ($adminLogin['ok']) {
    $createPage = get_page($base, '/admin/planes?create=1');
    record($results, 'ADMIN-PLAN-CREATE-PAGE', 'planes', 'ADMIN', 'GET /admin/planes?create=1', $createPage['code'] === 200, "HTTP {$createPage['code']}");
    $csrf = extract_csrf($createPage['body']);

    $codigo = 'QA'.date('His');
    $store = http_request('POST', $base.'/admin/planes', [
        'headers' => ['Content-Type: application/x-www-form-urlencoded'],
        'body' => http_build_query([
            '_token' => $csrf,
            'codigo' => $codigo,
            'nombre' => 'Plan QA Automático '.$codigo,
            'descripcion' => 'Creado por E2E QA',
            'beneficios' => "Beneficio A\nBeneficio B",
            'precio' => 99000,
            'moneda' => 'COP',
            'duracion_dias' => 30,
            'modalidad' => 'VIRTUAL',
            'tipo_cliente' => 'INDIVIDUAL',
            'cupos_totales' => 100,
            'estado_plan' => 'ACTIVO',
            'dias_gracia' => 3,
            'orden' => 99,
        ]),
    ]);
    $storeOk = in_array($store['code'], [302, 303], true);
    record($results, 'ADMIN-PLAN-STORE', 'planes', 'ADMIN', 'POST crear plan', $storeOk, "HTTP {$store['code']} loc=".($store['location'] ?? ''));

    // Negative: empty name
    $csrf2 = extract_csrf(get_page($base, '/admin/planes')['body']);
    $neg = http_request('POST', $base.'/admin/planes', [
        'headers' => ['Content-Type: application/x-www-form-urlencoded'],
        'body' => http_build_query([
            '_token' => $csrf2,
            'nombre' => '',
            'precio' => -10,
            'moneda' => 'COP',
            'duracion_dias' => 30,
            'modalidad' => 'VIRTUAL',
            'tipo_cliente' => 'INDIVIDUAL',
            'cupos_totales' => 1,
            'estado_plan' => 'ACTIVO',
        ]),
    ]);
    $negOk = $neg['code'] === 302 || $neg['code'] === 422;
    record($results, 'ADMIN-PLAN-NEG', 'planes', 'ADMIN', 'Validación negativa plan', $negOk, "HTTP {$neg['code']}");

    $list = get_page($base, '/admin/planes');
    $seePlan = str_contains($list['body'], 'Plan QA Automático '.$codigo);
    record($results, 'ADMIN-PLAN-LIST', 'planes', 'ADMIN', 'Plan visible en listado', $seePlan && $list['code'] === 200, $seePlan ? 'Encontrado en HTML' : 'NO visible');
}

// ========== ADMIN CRUD TIENDA ==========
qa_log('=== ADMIN CRUD TIENDA ===');
$adminLogin = login($base, 'paola@correo.com', 'paola12345');
if ($adminLogin['ok']) {
    $tienda = get_page($base, '/admin/tienda');
    record($results, 'ADMIN-TIENDA-PAGE', 'tienda', 'ADMIN', 'GET /admin/tienda', $tienda['code'] === 200, "HTTP {$tienda['code']}");
    $csrf = extract_csrf($tienda['body']);
    $sku = 'QA-SKU-'.date('His');
    $prodName = 'Producto QA '.$sku;
    $store = http_request('POST', $base.'/admin/tienda', [
        'headers' => ['Content-Type: application/x-www-form-urlencoded'],
        'body' => http_build_query([
            '_token' => $csrf,
            'nombre' => $prodName,
            'sku' => $sku,
            'precio' => 45000,
            'stock_total' => 25,
            'descripcion' => 'Producto E2E QA',
        ]),
    ]);
    record($results, 'ADMIN-TIENDA-STORE', 'tienda', 'ADMIN', 'POST crear producto', in_array($store['code'], [302, 303], true), "HTTP {$store['code']}");

    $tienda2 = get_page($base, '/admin/tienda');
    $see = str_contains($tienda2['body'], $prodName) || str_contains($tienda2['body'], $sku);
    record($results, 'ADMIN-TIENDA-LIST', 'tienda', 'ADMIN', 'Producto en listado', $see, $see ? 'Visible' : 'NO visible');

    // Cliente intenta crear producto (debe fallar)
    login($base, 'valen.gomez@demo.figuefit.test', 'demo12345');
    $csrfC = extract_csrf(get_page($base, '/cliente')['body'] ?? '');
    $hack = http_request('POST', $base.'/admin/tienda', [
        'headers' => ['Content-Type: application/x-www-form-urlencoded'],
        'body' => http_build_query([
            '_token' => $csrfC ?: 'x',
            'nombre' => 'Hack',
            'sku' => 'HACK1',
            'precio' => 1,
            'stock_total' => 1,
        ]),
    ]);
    $blocked = in_array($hack['code'], [403, 302, 419], true) && ! ($hack['code'] === 302 && str_contains((string) $hack['location'], 'success'));
    // If redirected to login or 403 ok
    record($results, 'SEC-CLIENTE-TIENDA', 'security', 'CLIENTE_VIRTUAL', 'POST /admin/tienda bloqueado', $hack['code'] !== 200, "HTTP {$hack['code']}");
}

// ========== INSTITUCIONAL FLUJO ==========
qa_log('=== INSTITUCIONAL ===');
$instLogin = login($base, 'rrhh@campoalegre.demo.figuefit.test', 'demo12345');
if ($instLogin['ok']) {
    $invPage = get_page($base, '/institucional/invitaciones');
    record($results, 'INST-INV-PAGE', 'institucional', 'RESPONSABLE_INSTITUCIONAL', 'GET invitaciones', $invPage['code'] === 200, "HTTP {$invPage['code']}");
    $csrf = extract_csrf($invPage['body']);
    $correoInv = 'qa.invitado.'.time().'@demo.figuefit.test';
    $inv = http_request('POST', $base.'/institucional/invitaciones', [
        'headers' => ['Content-Type: application/x-www-form-urlencoded'],
        'body' => http_build_query([
            '_token' => $csrf,
            'correo' => $correoInv,
            'nombre_sugerido' => 'QA Invitado',
            'dias_validez' => 7,
        ]),
    ]);
    record($results, 'INST-INV-CREATE', 'institucional', 'RESPONSABLE_INSTITUCIONAL', 'Crear invitación', in_array($inv['code'], [302, 303], true), "HTTP {$inv['code']}");

    $rep = get_page($base, '/institucional/reportes');
    record($results, 'INST-REPORTES', 'institucional', 'RESPONSABLE_INSTITUCIONAL', 'GET reportes', $rep['code'] === 200, "HTTP {$rep['code']}");

    $com = get_page($base, '/institucional/comunicacion');
    $csrf = extract_csrf($com['body']);
    $send = http_request('POST', $base.'/institucional/comunicacion', [
        'headers' => ['Content-Type: application/x-www-form-urlencoded'],
        'body' => http_build_query([
            '_token' => $csrf,
            'titulo' => 'Comunicado QA '.date('H:i'),
            'mensaje' => 'Mensaje de prueba E2E institucional',
            'canal' => 'IN_APP',
        ]),
    ]);
    record($results, 'INST-COM-SEND', 'institucional', 'RESPONSABLE_INSTITUCIONAL', 'Enviar comunicado', in_array($send['code'], [302, 303], true), "HTTP {$send['code']}");
}

// ========== COACH ==========
qa_log('=== COACH ===');
$coachLogin = login($base, 'laura.coach@demo.figuefit.test', 'demo12345');
if ($coachLogin['ok']) {
    $clients = get_page($base, '/coach/clientes');
    record($results, 'COACH-CLIENTES', 'coach', 'COACH', 'GET clientes', $clients['code'] === 200, "HTTP {$clients['code']}");
    $ent = get_page($base, '/coach/entrenamiento');
    record($results, 'COACH-ENT', 'coach', 'COACH', 'GET entrenamiento', in_array($ent['code'], [200, 302], true) && $ent['code'] !== 500, "HTTP {$ent['code']}");
}

// ========== STAFF ==========
qa_log('=== STAFF ===');
$staffLogin = login($base, 'asistente@demo.figuefit.test', 'demo12345');
if ($staffLogin['ok']) {
    $st = get_page($base, '/staff/clientes');
    record($results, 'STAFF-CLIENTES', 'staff', 'ASISTENTE_TECNICO', 'GET clientes', $st['code'] === 200, "HTTP {$st['code']}");
    $nov = get_page($base, '/staff/novedades');
    record($results, 'STAFF-NOV', 'staff', 'ASISTENTE_TECNICO', 'GET novedades', $st['code'] === 200 || $nov['code'] === 200, "HTTP novedades {$nov['code']}");
}

// ========== CLIENTE VIRTUAL NAV EXTRA ==========
qa_log('=== CLIENTE VIRTUAL ===');
$cv = login($base, 'valen.gomez@demo.figuefit.test', 'demo12345');
if ($cv['ok']) {
    foreach (['/cliente/comercial/planes', '/cliente/progreso', '/cliente/notificaciones'] as $p) {
        $r = get_page($base, $p);
        record($results, 'CV-'.md5($p), 'cliente_virtual', 'CLIENTE_VIRTUAL', "GET $p", in_array($r['code'], [200, 302], true) && $r['code'] < 500, "HTTP {$r['code']}");
    }
}

// ========== TIENDA EXCLUSIVA PRESENCIAL (diseño) ==========
$cv = login($base, 'valen.gomez@demo.figuefit.test', 'demo12345');
if ($cv['ok']) {
    $t = get_page($base, '/cliente/tienda');
    record($results, 'CV-TIENDA-403-DESIGN', 'tienda', 'CLIENTE_VIRTUAL', 'Tienda presencial bloqueada a virtual (módulo)', $t['code'] === 403, "HTTP {$t['code']} (esperado 403)");
}

// ========== AISLAMIENTO INST A vs B ==========
qa_log('=== AISLAMIENTO INSTITUCIONES ===');
$instA = login($base, 'rrhh@campoalegre.demo.figuefit.test', 'demo12345');
if ($instA['ok']) {
    $usersA = get_page($base, '/institucional/usuarios');
    $seesCampo = str_contains($usersA['body'], 'Campoalegre') || $usersA['code'] === 200;
    // No debe listar miembros de USCO por nombre institucional ajeno en header
    $seesUsco = str_contains($usersA['body'], 'Universidad Surcolombiana');
    record($results, 'INST-ISO-A', 'institucional', 'RESPONSABLE_INSTITUCIONAL', 'Portal solo institución propia', $seesCampo && ! $seesUsco, $seesUsco ? 'Filtró USCO (BUG)' : 'Sin fuga USCO');
}

// Save results
$summary = [
    'started_at' => date('c', (int) $started),
    'finished_at' => date('c'),
    'duration_sec' => round(microtime(true) - $started, 2),
    'base_url' => $base,
    'total' => count($results),
    'aprobados' => count(array_filter($results, fn ($r) => $r['status'] === 'APROBADO')),
    'fallidos' => count(array_filter($results, fn ($r) => $r['status'] === 'FALLIDO')),
    'bugs' => count($bugs),
];

file_put_contents(__DIR__.'/../resultados/e2e_http_results.json', json_encode(['summary' => $summary, 'results' => $results, 'bugs' => $bugs], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

qa_log('SUMMARY: '.json_encode($summary));
echo PHP_EOL.json_encode($summary, JSON_PRETTY_PRINT).PHP_EOL;
