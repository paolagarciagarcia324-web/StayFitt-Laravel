<?php

/**
 * Deja solo 2 sesiones demo FigueFit coherentes.
 * Run: php database/seeders/backfill_sesiones_calendario.php
 */

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

if (! Schema::hasTable('sesiones')) {
    echo "Tabla sesiones no existe.\n";
    exit(1);
}

$coachId = (int) (DB::table('coaches')->value('id_coach') ?: 0);
if ($coachId < 1) {
    echo "No hay coaches; no se pueden crear sesiones.\n";
    exit(1);
}

$coachIds = DB::table('coaches')->orderBy('id_coach')->pluck('id_coach')->all();
$coachB = $coachIds[1] ?? $coachIds[0];
$now = now();

DB::table('sesiones')->delete();

DB::table('sesiones')->insert([
    [
        'id_coach' => $coachIds[0],
        'tipo' => 'GRUPAL',
        'titulo' => 'Clase funcional FigueFit',
        'fecha' => now()->addDays(2)->toDateString(),
        'hora_inicio' => '07:00:00',
        'hora_fin' => '08:00:00',
        'modalidad' => 'PRESENCIAL',
        'cupo_maximo' => 12,
        'cupo_reservado' => 4,
        'estado' => 'PROGRAMADA',
        'creado_en' => $now,
    ],
    [
        'id_coach' => $coachB,
        'tipo' => 'INDIVIDUAL',
        'titulo' => 'Seguimiento 1:1 nutrición',
        'fecha' => now()->addDays(5)->toDateString(),
        'hora_inicio' => '16:00:00',
        'hora_fin' => '16:45:00',
        'modalidad' => 'VIRTUAL',
        'cupo_maximo' => 1,
        'cupo_reservado' => 1,
        'estado' => 'PROGRAMADA',
        'creado_en' => $now,
    ],
]);

echo "Sesiones demo: 2 (Clase funcional FigueFit + Seguimiento 1:1 nutrición)\n";
