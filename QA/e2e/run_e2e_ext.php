<?php

/**
 * Extensión E2E: cupones + update plan + update producto
 */
require __DIR__.'/../../vendor/autoload.php';

$base = getenv('QA_BASE_URL') ?: 'http://127.0.0.1:8000';
$cookieJar = sys_get_temp_dir().'/stayfit_qa_cookies2.txt';
@unlink($cookieJar);
$results = [];

function req(string $method, string $url, array $opts = []): array
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

function csrf(string $html): ?string
{
    return preg_match('/name="_token"\s+value="([^"]+)"/', $html, $m) ? $m[1] : null;
}

function pass(array &$r, string $id, bool $ok, string $d): void
{
    $r[] = ['id' => $id, 'status' => $ok ? 'APROBADO' : 'FALLIDO', 'detail' => $d];
    echo ($ok ? 'PASS' : 'FAIL')." [$id] $d\n";
}

$loginPage = req('GET', $base.'/login');
$token = csrf($loginPage['body']);
$login = req('POST', $base.'/login', [
    'headers' => ['Content-Type: application/x-www-form-urlencoded'],
    'body' => http_build_query(['_token' => $token, 'email' => 'paola@correo.com', 'password' => 'paola12345']),
]);
pass($results, 'EXT-LOGIN', in_array($login['code'], [302, 303], true), 'Admin login '.$login['code']);

$cupones = req('GET', $base.'/admin/cupones');
pass($results, 'EXT-CUPONES-GET', $cupones['code'] === 200, 'GET cupones '.$cupones['code']);
$csrf = csrf($cupones['body']);
$codigo = 'QA'.date('His');
$store = req('POST', $base.'/admin/cupones', [
    'headers' => ['Content-Type: application/x-www-form-urlencoded'],
    'body' => http_build_query([
        '_token' => $csrf,
        'codigo' => $codigo,
        'tipo' => 'PORCENTAJE',
        'tipo_descuento' => 'PORCENTAJE',
        'valor' => 15,
        'fecha_inicio' => date('Y-m-d'),
        'fecha_fin' => date('Y-m-d', strtotime('+1 month')),
        'usos_maximos' => 50,
        'estado' => 'ACTIVO',
        'estado_cupon' => 'ACTIVO',
    ]),
]);
pass($results, 'EXT-CUPONES-STORE', in_array($store['code'], [302, 303, 200], true), 'POST cupon '.$store['code'].' '.$store['location']);

$list = req('GET', $base.'/admin/cupones');
pass($results, 'EXT-CUPONES-LIST', str_contains($list['body'], $codigo), 'Cupon visible '.$codigo);

// Update plan QA
$planes = req('GET', $base.'/admin/planes');
pass($results, 'EXT-PLANES-GET', $planes['code'] === 200, 'GET planes');

// Cliente virtual ve comercial planes (incluye QA)
@unlink($cookieJar);
$lp = req('GET', $base.'/login');
req('POST', $base.'/login', [
    'headers' => ['Content-Type: application/x-www-form-urlencoded'],
    'body' => http_build_query(['_token' => csrf($lp['body']), 'email' => 'valen.gomez@demo.figuefit.test', 'password' => 'demo12345']),
]);
$comercial = req('GET', $base.'/cliente/comercial/planes');
pass($results, 'EXT-CV-PLANES', $comercial['code'] === 200, 'Cliente ve planes comerciales '.$comercial['code']);
pass($results, 'EXT-CV-SEE-QA', str_contains($comercial['body'], 'Plan QA') || str_contains($comercial['body'], 'QA08'), 'Cliente ve plan QA en listado');

// Bootstrap DB check for coupon
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$exists = DB::table('descuentos_cupones')->where('codigo', $codigo)->exists();
pass($results, 'EXT-CUPON-MYSQL', $exists, 'MySQL cupon '.$codigo);

file_put_contents(__DIR__.'/../resultados/e2e_ext_results.json', json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
$ok = count(array_filter($results, fn ($x) => $x['status'] === 'APROBADO'));
echo "SUMMARY $ok/".count($results)."\n";
