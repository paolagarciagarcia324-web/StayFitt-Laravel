<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$base = getenv('QA_BASE_URL') ?: 'http://127.0.0.1:8000';
$jar = sys_get_temp_dir().'/stayfit_qa_cart.txt';
@unlink($jar);
$out = [];

function httpc(string $method, string $url, ?string $body = null): array
{
    global $jar;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_COOKIEJAR => $jar,
        CURLOPT_COOKIEFILE => $jar,
        CURLOPT_HEADER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => $body ? ['Content-Type: application/x-www-form-urlencoded'] : [],
    ]);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }
    $raw = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    $header = substr((string) $raw, 0, $hs);
    preg_match('/^Location:\s*(.+)$/mi', $header, $m);

    return ['code' => $code, 'body' => substr((string) $raw, $hs), 'location' => $m[1] ?? null];
}

function csrfx(string $html): ?string
{
    return preg_match('/name="_token"\s+value="([^"]+)"/', $html, $m) ? $m[1] : null;
}

function say(string $id, bool $ok, string $d): void
{
    global $out;
    $out[] = compact('id') + ['status' => $ok ? 'APROBADO' : 'FALLIDO', 'detail' => $d];
    echo ($ok ? 'PASS' : 'FAIL')." [$id] $d\n";
}

$login = httpc('GET', $base.'/login');
$r = httpc('POST', $base.'/login', http_build_query([
    '_token' => csrfx($login['body']),
    'email' => 'julian.rojas@demo.figuefit.test',
    'password' => 'demo12345',
]));
say('CART-LOGIN', in_array($r['code'], [302, 303], true), 'login '.$r['code']);

$tienda = httpc('GET', $base.'/cliente/tienda');
say('CART-TIENDA', $tienda['code'] === 200, 'tienda '.$tienda['code']);
$csrf = csrfx($tienda['body']);
$prodId = (int) DB::table('productos_tienda')->where('estado', 'ACTIVO')->where('stock_total', '>', 0)->orderByDesc('id_producto')->value('id_producto');
$stockAntes = (int) DB::table('productos_tienda')->where('id_producto', $prodId)->value('stock_total');

$add = httpc('POST', $base.'/cliente/tienda/'.$prodId.'/agregar', http_build_query([
    '_token' => $csrf,
    'cantidad' => 1,
]));
say('CART-ADD', in_array($add['code'], [302, 303, 200], true), "POST agregar #$prodId ".$add['code'].' loc='.$add['location']);

$carrito = httpc('GET', $base.'/cliente/tienda/carrito');
say('CART-VIEW', $carrito['code'] === 200, 'GET carrito '.$carrito['code']);
$csrf2 = csrfx($carrito['body']) ?: csrfx(httpc('GET', $base.'/cliente/tienda')['body']);

$checkout = httpc('POST', $base.'/cliente/tienda/checkout', http_build_query([
    '_token' => $csrf2,
    'tipo_entrega' => 'RETIRO',
    'observacion' => 'Pedido QA E2E',
]));
say('CART-CHECKOUT', in_array($checkout['code'], [302, 303, 200], true) && ! str_contains((string) $checkout['location'], 'carrito'), 'checkout '.$checkout['code'].' loc='.$checkout['location']);

$ordenes = httpc('GET', $base.'/cliente/tienda/ordenes');
say('CART-ORDENES', $ordenes['code'] === 200, 'ordenes cliente '.$ordenes['code']);

@unlink($jar);
$l2 = httpc('GET', $base.'/login');
httpc('POST', $base.'/login', http_build_query([
    '_token' => csrfx($l2['body']),
    'email' => 'paola@correo.com',
    'password' => 'paola12345',
]));
$adminTienda = httpc('GET', $base.'/admin/tienda');
say('CART-ADMIN-TIENDA', $adminTienda['code'] === 200, 'admin tienda '.$adminTienda['code']);

$stockDespues = (int) DB::table('productos_tienda')->where('id_producto', $prodId)->value('stock_total');
$ordenNueva = DB::table('ordenes_tienda')->where('observacion_admin', 'Pedido QA E2E')->orWhere('observacion_admin', 'like', '%QA E2E%')->orderByDesc('id_orden')->first();
if (! $ordenNueva) {
    $ordenNueva = DB::table('ordenes_tienda')->orderByDesc('id_orden')->first();
}
say('CART-MYSQL-ORDEN', (bool) $ordenNueva && str_contains((string) ($ordenNueva->observacion_admin ?? ''), 'QA'), 'orden id='.($ordenNueva->id_orden ?? 'null').' obs='.($ordenNueva->observacion_admin ?? '').' estado='.($ordenNueva->estado_orden ?? $ordenNueva->estado ?? ''));
say('CART-STOCK', $stockDespues <= $stockAntes, "stock antes=$stockAntes despues=$stockDespues");

file_put_contents(__DIR__.'/../resultados/e2e_cart.json', json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
$ok = count(array_filter($out, fn ($x) => $x['status'] === 'APROBADO'));
echo "SUMMARY $ok/".count($out)."\n";
