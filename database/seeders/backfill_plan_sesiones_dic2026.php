<?php

/**
 * Plan de sesiones FigueFit (tablero Lun–Vie) — dic. 2026 / imagen plan físico.
 * Run: php database/seeders/backfill_plan_sesiones_dic2026.php
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

$now = now();
$hi = '05:30:00';
$hf = '06:30:00';

$plan = [
    ['2026-11-30', 'ENTRENAMIENTO DE FUERZA DE PIERNA Y GLÚTEO', 'PROGRAMADA', $hi, $hf],
    ['2026-12-01', 'ENTRENAMIENTO DE FUERZA DE BRAZOS Y ESPALDA', 'PROGRAMADA', $hi, $hf],
    ['2026-12-02', 'CARDIO RUMBA', 'PROGRAMADA', $hi, $hf],
    ['2026-12-03', 'ENTRENAMIENTO DE FUERZA DE PIERNA Y GLÚTEO', 'PROGRAMADA', $hi, $hf],
    ['2026-12-04', 'ENTRENAMIENTO DE FUERZA DE BRAZOS Y ABDOMEN', 'PROGRAMADA', $hi, $hf],
    ['2026-12-07', 'NO HAY CLASE', 'CANCELADA', $hi, $hf],
    ['2026-12-08', 'NO HAY CLASE', 'CANCELADA', $hi, $hf],
    ['2026-12-09', 'CARDIO EN PISTA', 'PROGRAMADA', $hi, $hf],
    ['2026-12-10', 'ENTRENAMIENTO DE FUERZA DE PIERNA Y GLÚTEO', 'PROGRAMADA', $hi, $hf],
    ['2026-12-11', 'ENTRENAMIENTO DE FUERZA DE BRAZOS Y ESPALDA', 'PROGRAMADA', $hi, $hf],
    ['2026-12-14', 'ENTRENAMIENTO DE FUERZA DE PIERNA Y GLÚTEO', 'PROGRAMADA', $hi, $hf],
    ['2026-12-15', 'ENTRENAMIENTO DE FUERZA DE BRAZOS Y ESPALDA', 'PROGRAMADA', $hi, $hf],
    ['2026-12-16', 'CARDIO HIIT', 'PROGRAMADA', $hi, $hf],
    ['2026-12-17', 'ENTRENAMIENTO DE FUERZA DE PIERNA Y GLÚTEO', 'PROGRAMADA', $hi, $hf],
    ['2026-12-18', 'ENTRENAMIENTO DE FUERZA DE BRAZOS Y ABDOMEN', 'PROGRAMADA', $hi, $hf],
    ['2026-12-21', 'ENTRENAMIENTO DE FUERZA DE PIERNA Y GLÚTEO', 'PROGRAMADA', $hi, $hf],
    ['2026-12-22', 'ENTRENAMIENTO DE FUERZA DE BRAZOS Y ESPALDA', 'PROGRAMADA', $hi, $hf],
    ['2026-12-23', 'CIERRE DEL AÑO 6:30 pm', 'PROGRAMADA', '18:30:00', '19:30:00'],
    ['2026-12-24', 'NO HAY CLASE', 'CANCELADA', $hi, $hf],
    ['2026-12-25', 'NO HAY CLASE', 'CANCELADA', $hi, $hf],
    ['2026-12-28', 'ENTRENAMIENTO DE FUERZA DE PIERNA Y GLÚTEO', 'PROGRAMADA', $hi, $hf],
    ['2026-12-29', 'ENTRENAMIENTO DE FUERZA DE BRAZOS Y ESPALDA', 'PROGRAMADA', $hi, $hf],
    ['2026-12-30', 'PILATES ENFOQUE EN ABDOMEN', 'PROGRAMADA', $hi, $hf],
    ['2026-12-31', 'NO HAY CLASE', 'CANCELADA', $hi, $hf],
    ['2027-01-01', 'AÑO NUEVO NO HAY CLASE', 'CANCELADA', $hi, $hf],
];

$fechas = array_column($plan, 0);
DB::table('sesiones')->whereIn('fecha', $fechas)->delete();

$rows = [];
foreach ($plan as [$fecha, $titulo, $estado, $horaInicio, $horaFin]) {
    $rows[] = [
        'id_coach' => $coachId,
        'tipo' => 'GRUPAL',
        'titulo' => $titulo,
        'fecha' => $fecha,
        'hora_inicio' => $horaInicio,
        'hora_fin' => $horaFin,
        'modalidad' => 'PRESENCIAL',
        'cupo_maximo' => 18,
        'cupo_reservado' => 0,
        'estado' => $estado,
        'creado_en' => $now,
        'actualizado_en' => $now,
    ];
}

DB::table('sesiones')->insert($rows);

echo 'Plan dic. 2026 cargado: '.count($rows)." sesiones.\n";
echo "Abre Calendario → mes 2026-12\n";
