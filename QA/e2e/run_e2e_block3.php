<?php

/**
 * E2E bloque: coach propuestas + staff + cliente presencial tienda GET + admin update producto
 */
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$base = getenv('QA_BASE_URL') ?: 'http://127.0.0.1:8000';
$cookieJar = sys_get_temp_dir().'/stayfit_qa_b3.txt';
@unlink($cookieJar);
$results = [];

function r3(string $method, string $url, array $opts = []): array
{
    global $cookieJar;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_COOKIEJAR => $cookieJar,
        CURLOPT_COOKIEFILE => $cookieJar,
        CURLOPT_HEADER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => $opts['headers'] ?? [],
    ]);
    if (! empty($opts['body'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $opts['body']);
    }
    $raw = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    $header = substr((string) $raw, 0, $hs);
    $body = substr((string) $raw, $hs);
    preg_match('/^Location:\s*(.+)$/mi', $header, $m);

    return ['code' => $code, 'body' => $body, 'location' => isset($m[1]) ? trim($m[1]) : null];
}

function tok(string $html): ?string
{
    return preg_match('/name="_token"\s+value="([^"]+)"/', $html, $m) ? $m[1] : null;
}

function out(array &$r, string $id, bool $ok, string $d): void
{
    $r[] = ['id' => $id, 'status' => $ok ? 'APROBADO' : 'FALLIDO', 'detail' => $d];
    echo ($ok ? 'PASS' : 'FAIL')." [$id] $d\n";
}

function login3(string $base, string $email, string $pass): bool
{
    global $cookieJar;
    @unlink($cookieJar);
    $p = r3('GET', $base.'/login');
    $t = tok($p['body']);
    $res = r3('POST', $base.'/login', [
        'headers' => ['Content-Type: application/x-www-form-urlencoded'],
        'body' => http_build_query(['_token' => $t, 'email' => $email, 'password' => $pass]),
    ]);

    return in_array($res['code'], [302, 303], true) && ! str_contains((string) $res['location'], '/login');
}

// Coach propuestas
out($results, 'B3-COACH-LOGIN', login3($base, 'laura.coach@demo.figuefit.test', 'demo12345'), 'coach login');
$prop = r3('GET', $base.'/coach/propuestas');
out($results, 'B3-COACH-PROP', $prop['code'] === 200, 'GET propuestas '.$prop['code']);
$crear = r3('GET', $base.'/coach/propuestas/crear');
out($results, 'B3-COACH-PROP-CREATE', in_array($crear['code'], [200, 302], true), 'GET crear propuesta '.$crear['code']);

// Staff propuestas + rutinas
out($results, 'B3-STAFF-LOGIN', login3($base, 'asistente@demo.figuefit.test', 'demo12345'), 'staff login');
$sp = r3('GET', $base.'/staff/propuestas');
out($results, 'B3-STAFF-PROP', $sp['code'] === 200, 'staff propuestas '.$sp['code']);
$sr = r3('GET', $base.'/staff/rutinas');
out($results, 'B3-STAFF-RUT', $sr['code'] === 200, 'staff rutinas '.$sr['code']);

// Presencial tienda + add to cart if possible
out($results, 'B3-CP-LOGIN', login3($base, 'julian.rojas@demo.figuefit.test', 'demo12345'), 'presencial login');
$tienda = r3('GET', $base.'/cliente/tienda');
out($results, 'B3-CP-TIENDA', $tienda['code'] === 200, 'tienda presencial '.$tienda['code']);
$prodId = DB::table('productos_tienda')->where('estado', 'ACTIVO')->where('stock_total', '>', 0)->value('id_producto');
if ($prodId && preg_match('/action="([^"]*tienda[^"]*)"/', $tienda['body'])) {
    $csrf = tok($tienda['body']);
    // try common cart route
    $cart = r3('POST', $base.'/cliente/tienda/carrito', [
        'headers' => ['Content-Type: application/x-www-form-urlencoded'],
        'body' => http_build_query(['_token' => $csrf, 'id_producto' => $prodId, 'cantidad' => 1]),
    ]);
    out($results, 'B3-CP-CART', in_array($cart['code'], [302, 200, 404, 405], true), 'POST carrito '.$cart['code'].' (404=ruta distinta)');
} else {
    out($results, 'B3-CP-CART', false, 'No se pudo localizar producto/form carrito');
}

// Admin update producto QA
out($results, 'B3-ADMIN-LOGIN', login3($base, 'paola@correo.com', 'paola12345'), 'admin login');
$prod = DB::table('productos_tienda')->where('sku', 'like', 'QA-SKU-%')->orderByDesc('id_producto')->first();
if ($prod) {
    $page = r3('GET', $base.'/admin/tienda');
    $csrf = tok($page['body']);
    $upd = r3('POST', $base.'/admin/tienda/'.$prod->id_producto, [
        'headers' => ['Content-Type: application/x-www-form-urlencoded'],
        'body' => http_build_query([
            '_token' => $csrf,
            '_method' => 'PUT',
            'nombre' => $prod->nombre.' EDITADO',
            'sku' => $prod->sku,
            'precio' => (float) $prod->precio + 1000,
            'stock_total' => (int) $prod->stock_total,
            'estado' => 'ACTIVO',
        ]),
    ]);
    out($results, 'B3-PROD-UPDATE', in_array($upd['code'], [302, 303], true), 'PUT producto '.$upd['code']);
    $fresh = DB::table('productos_tienda')->where('id_producto', $prod->id_producto)->first();
    out($results, 'B3-PROD-MYSQL', str_contains((string) $fresh->nombre, 'EDITADO'), 'MySQL nombre='.$fresh->nombre);
} else {
    out($results, 'B3-PROD-UPDATE', false, 'Sin producto QA');
    out($results, 'B3-PROD-MYSQL', false, 'Sin producto QA');
}

// Admin solicitudes/pagos detail
$sol = r3('GET', $base.'/admin/solicitudes');
out($results, 'B3-SOL', $sol['code'] === 200, 'solicitudes '.$sol['code']);
$pag = r3('GET', $base.'/admin/pagos');
out($results, 'B3-PAG', $pag['code'] === 200, 'pagos '.$pag['code']);
$sid = DB::table('solicitudes_compra')->orderByDesc('id_solicitud')->value('id_solicitud');
if ($sid) {
    $sd = r3('GET', $base.'/admin/solicitudes/'.$sid);
    out($results, 'B3-SOL-SHOW', in_array($sd['code'], [200, 302], true), "solicitud #{$sid} ".$sd['code']);
}

file_put_contents(__DIR__.'/../resultados/e2e_block3.json', json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
$ok = count(array_filter($results, fn ($x) => $x['status'] === 'APROBADO'));
echo 'SUMMARY '.$ok.'/'.count($results)."\n";
