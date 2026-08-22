<?php

/**
 * Alinea pendientes: solo novedades que requieren admin.
 * Run: php database/seeders/align_solicitudes_novedades.php
 */

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$motivoMateo = 'Novedad: pago reportado por pasarela pero no reflejado en conciliación. Requiere validación admin.';

$mateoId = DB::table('solicitudes_compra')
    ->where('correo', 'mateo.s@demo.figuefit.test')
    ->value('id_solicitud');

if ($mateoId) {
    DB::table('solicitudes_compra')->where('id_solicitud', $mateoId)->update([
        'estado_solicitud' => 'PENDIENTE',
        'observacion_admin' => $motivoMateo,
        'datos_extra_json' => json_encode([
            'tipo' => 'NOVEDAD',
            'motivo' => 'PAGO_NO_REFLEJADO',
            'requiere_validacion_admin' => true,
        ]),
        'fecha_revision' => null,
        'revisada_en' => null,
    ]);
    DB::table('pagos')->where('id_solicitud', $mateoId)->update([
        'estado_pago' => 'VALIDADO',
        'observacion_admin' => $motivoMateo,
        'comprobante_url' => 'images/comprobantes/recibo-ejemplo.png',
    ]);
}

$query = DB::table('solicitudes_compra')->where('estado_solicitud', 'PENDIENTE');
if ($mateoId) {
    $query->where('id_solicitud', '!=', $mateoId);
}
$otros = $query->pluck('id_solicitud');

$n = 0;
foreach ($otros as $id) {
    $obs = DB::table('solicitudes_compra')->where('id_solicitud', $id)->value('observacion_admin');
    DB::table('solicitudes_compra')->where('id_solicitud', $id)->update([
        'estado_solicitud' => 'VALIDADA',
        'fecha_revision' => now(),
        'revisada_en' => now(),
        'observacion_admin' => $obs ?: 'Activación automática por pasarela (demo)',
    ]);
    $n++;
}

DB::table('clientes')
    ->where('estado_cliente', 'PENDIENTE_PAGO')
    ->update(['estado_cliente' => 'ACTIVO', 'fecha_activacion' => now()]);

echo 'novedad_mateo='.($mateoId ? 'ok' : 'missing').PHP_EOL;
echo 'validadas='.$n.PHP_EOL;
echo 'pendientes='.DB::table('solicitudes_compra')->where('estado_solicitud', 'PENDIENTE')->count().PHP_EOL;
echo "OK\n";
