<?php

/**
 * Completa fecha_pago/creado_en y sincroniza planes_cliente.fecha_fin con duración del plan.
 * Run: php database/seeders/backfill_pagos_fechas_vencimiento.php
 */

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Pago;
use App\Models\Plan;
use App\Models\PlanCliente;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

$planesDuracion = Plan::query()->pluck('duracion_dias', 'id_plan');
$defaultPlan = (int) (Plan::query()->value('id_plan') ?: 1);
$fechasOk = 0;
$pcOk = 0;

Pago::query()->orderBy('id_pago')->each(function (Pago $pago) use ($planesDuracion, $defaultPlan, &$fechasOk, &$pcOk) {
    $fecha = $pago->fecha_pago ?? $pago->fecha_validacion ?? $pago->creado_en ?? now();
    if (! $fecha instanceof Carbon) {
        $fecha = Carbon::parse($fecha);
    }

    $updates = [];
    if (! $pago->fecha_pago) {
        $updates['fecha_pago'] = $fecha;
    }
    if (! $pago->creado_en) {
        $updates['creado_en'] = $fecha;
    }
    if (! $pago->id_plan) {
        $updates['id_plan'] = $pago->solicitud?->id_plan ?: $defaultPlan;
    }
    if ($updates !== []) {
        $pago->update($updates);
        $pago->refresh();
        $fechasOk++;
    }

    $idPlan = (int) ($pago->id_plan ?: $defaultPlan);
    $dias = (int) ($planesDuracion[$idPlan] ?? 30);
    $inicio = $fecha->copy()->startOfDay();
    $fin = $inicio->copy()->addDays($dias);

    $pc = PlanCliente::query()->where('id_pago', $pago->id_pago)->first();
    if ($pc) {
        $pc->update([
            'fecha_inicio' => $inicio->toDateString(),
            'fecha_fin' => $fin->toDateString(),
            'id_plan' => $idPlan,
        ]);
        if (! $pago->id_plan_cliente) {
            $pago->update(['id_plan_cliente' => $pc->id_plan_cliente]);
        }
        $pcOk++;

        return;
    }

    // VALIDADO o RECHAZADO: crear plan_cliente de trazabilidad si hay cliente
    $idCliente = $pago->id_cliente ?: $pago->solicitud?->id_cliente;
    if (! $idCliente) {
        return;
    }

    $pcid = DB::table('planes_cliente')->insertGetId([
        'id_cliente' => $idCliente,
        'id_plan' => $idPlan,
        'id_solicitud' => $pago->id_solicitud,
        'id_pago' => $pago->id_pago,
        'fecha_inicio' => $inicio->toDateString(),
        'fecha_fin' => $fin->toDateString(),
        'estado' => $pago->estado_pago === 'VALIDADO' ? 'ACTIVO' : 'CANCELADO',
        'estado_plan_cliente' => $pago->estado_pago === 'VALIDADO' ? 'ACTIVO' : 'CANCELADO',
        'creado_en' => $fecha,
    ]);
    $pago->update(['id_plan_cliente' => $pcid]);
    $pcOk++;
});

echo "fechas_actualizadas={$fechasOk}\n";
echo "planes_cliente_sync={$pcOk}\n";
echo 'pagos='.Pago::query()->count()."\n";
echo "OK\n";
