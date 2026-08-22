<?php

/**
 * Completa fecha_fin en eventos que solo tienen fecha_inicio.
 * RETO/RETOS → +1 día; resto → +2 horas.
 * Run: php database/seeders/backfill_eventos_fecha_fin.php
 */

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Evento;
use Illuminate\Support\Carbon;

$updated = 0;

Evento::query()
    ->whereNotNull('fecha_inicio')
    ->where(function ($q) {
        $q->whereNull('fecha_fin')->orWhere('fecha_fin', '');
    })
    ->orderBy('id_evento')
    ->each(function (Evento $evento) use (&$updated) {
        $inicio = $evento->fecha_inicio instanceof Carbon
            ? $evento->fecha_inicio->copy()
            : Carbon::parse($evento->fecha_inicio);

        $tipo = strtoupper((string) $evento->tipo);
        $fin = str_contains($tipo, 'RETO')
            ? $inicio->copy()->addDay()
            : $inicio->copy()->addHours(2);

        $evento->fecha_fin = $fin;
        $evento->save();
        $updated++;
    });

echo "Eventos actualizados con fecha_fin: {$updated}\n";
