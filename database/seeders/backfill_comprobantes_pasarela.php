<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Pago;
use App\Services\ComprobantePasarelaService;

$svc = app(ComprobantePasarelaService::class);
$n = 0;

Pago::query()
    ->with('solicitud')
    ->orderBy('id_pago')
    ->chunkById(50, function ($pagos) use ($svc, &$n) {
        foreach ($pagos as $p) {
            $pas = strtoupper((string) ($p->pasarela ?: 'PASARELA'));
            if (in_array($pas, ['', 'NINGUNA', 'PENDIENTE'], true)) {
                $pas = 'PASARELA';
            }
            $svc->asegurar($p, $p->solicitud, $pas);
            $n++;
        }
    }, 'id_pago');

echo "ok={$n}\n";
echo "http=".Pago::query()->where('comprobante_url', 'like', 'http%')->count()."\n";
echo "local=".Pago::query()->where('comprobante_url', 'like', 'images/%')->count()."\n";
