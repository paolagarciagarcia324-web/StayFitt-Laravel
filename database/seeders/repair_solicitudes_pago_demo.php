<?php

/**
 * Repara solicitudes demo sin pago / con pasarela incompleta y genera comprobantes.
 * Run: php database/seeders/repair_solicitudes_pago_demo.php
 */

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Pago;
use App\Models\SolicitudCompra;
use App\Services\ComprobantePasarelaService;
use Illuminate\Support\Facades\DB;

$pasarelas = ['BOLD', 'EPAYCO', 'MERCADOPAGO'];
$metodos = ['TARJETA', 'PSE', 'TARJETA'];
$creados = 0;
$actualizados = 0;

$solicitudes = SolicitudCompra::query()
    ->with('pago')
    ->orderBy('id_solicitud')
    ->get();

foreach ($solicitudes as $i => $sol) {
    $pasarela = $pasarelas[$i % 3];
    $metodo = $metodos[$i % 3];
    $monto = (float) ($sol->total ?? $sol->precio_lista ?? 89000);

    if (! $sol->pago) {
        Pago::query()->create([
            'id_solicitud' => $sol->id_solicitud,
            'id_cliente' => $sol->id_cliente,
            'id_plan' => $sol->id_plan,
            'tipo_pago' => 'PLAN',
            'origen_pago' => 'SOLICITUD_PLAN',
            'monto' => $monto,
            'moneda' => 'COP',
            'metodo' => $metodo,
            'metodo_pago' => $metodo,
            'pasarela' => $pasarela,
            'referencia' => 'REF-FIX-'.$sol->id_solicitud,
            'referencia_pago' => 'PAY-FIX-'.$sol->id_solicitud,
            'estado_pago' => $sol->estado_solicitud === 'RECHAZADA' ? 'RECHAZADO' : 'VALIDADO',
            'fecha_pago' => $sol->creado_en ?? now(),
            'fecha_validacion' => $sol->estado_solicitud === 'RECHAZADA' ? null : now(),
            'creado_en' => $sol->creado_en ?? now(),
        ]);
        DB::table('solicitudes_compra')->where('id_solicitud', $sol->id_solicitud)->update([
            'origen' => 'PASARELA',
        ]);
        $creados++;
        continue;
    }

    $pago = $sol->pago;
    $pas = strtoupper((string) $pago->pasarela);
    $needs = in_array($pas, ['', 'NINGUNA', 'PENDIENTE'], true)
        || blank($pago->referencia_pago) && blank($pago->referencia)
        || blank($pago->estado_pago)
        || ($sol->estado_solicitud === 'PENDIENTE' && $pago->estado_pago === 'PENDIENTE');

    if ($needs) {
        $pago->update([
            'pasarela' => in_array($pas, ['', 'NINGUNA', 'PENDIENTE'], true) ? $pasarela : $pas,
            'metodo' => $pago->metodo ?: $metodo,
            'metodo_pago' => $pago->metodo_pago ?: $metodo,
            'referencia' => $pago->referencia ?: ('REF-FIX-'.$sol->id_solicitud),
            'referencia_pago' => $pago->referencia_pago ?: ('PAY-FIX-'.$sol->id_solicitud),
            'estado_pago' => $sol->estado_solicitud === 'RECHAZADA'
                ? 'RECHAZADO'
                : ($pago->estado_pago === 'PENDIENTE' ? 'VALIDADO' : $pago->estado_pago),
            'fecha_pago' => $pago->fecha_pago ?: ($sol->creado_en ?? now()),
            'fecha_validacion' => $pago->fecha_validacion ?: (
                $sol->estado_solicitud === 'RECHAZADA' ? null : now()
            ),
        ]);
        DB::table('solicitudes_compra')->where('id_solicitud', $sol->id_solicitud)->update([
            'origen' => 'PASARELA',
        ]);
        $actualizados++;
    }
}

// Sync fecha_revision desde revisada_en cuando falte
$synced = DB::update(
    'UPDATE solicitudes_compra SET fecha_revision = revisada_en WHERE fecha_revision IS NULL AND revisada_en IS NOT NULL'
);

$svc = app(ComprobantePasarelaService::class);
$comps = 0;
Pago::query()
    ->with('solicitud')
    ->orderBy('id_pago')
    ->chunkById(50, function ($pagos) use ($svc, &$comps) {
        foreach ($pagos as $pago) {
            $pas = strtoupper((string) ($pago->pasarela ?: 'PASARELA'));
            if (in_array($pas, ['', 'NINGUNA', 'PENDIENTE'], true)) {
                $pas = 'PASARELA';
            }
            $svc->asegurar($pago, $pago->solicitud, $pas);
            $comps++;
        }
    }, 'id_pago');

echo "pagos_creados={$creados}\n";
echo "pagos_actualizados={$actualizados}\n";
echo "fechas_sync={$synced}\n";
echo "comprobantes={$comps}\n";
echo "OK\n";
