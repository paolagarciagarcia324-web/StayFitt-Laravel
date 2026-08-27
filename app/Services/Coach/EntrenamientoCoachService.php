<?php

namespace App\Services\Coach;

use App\Models\Cliente;
use App\Models\Coach;
use App\Models\Ejercicio;
use App\Models\PlanEntrenamiento;
use App\Models\Rutina;
use App\Models\RutinaEjercicio;
use Illuminate\Support\Facades\DB;

class EntrenamientoCoachService
{
    public function crearPlan(Coach $coach, Cliente $cliente, array $data): PlanEntrenamiento
    {
        return PlanEntrenamiento::create([
            'id_cliente' => $cliente->id_cliente,
            'id_coach' => $coach->id_coach,
            'id_plan_cliente' => $cliente->planActivo()?->id_plan_cliente,
            'nombre' => $data['nombre'],
            'objetivo' => $data['objetivo'] ?? null,
            'modalidad' => strtoupper($cliente->modalidad ?? 'VIRTUAL'),
            'estado' => $data['estado'] ?? 'ACTIVO',
        ]);
    }

    public function crearRutina(PlanEntrenamiento $plan, array $data, array $ejercicios = []): Rutina
    {
        return DB::transaction(function () use ($plan, $data, $ejercicios) {
            $orden = (int) Rutina::query()
                ->where('id_plan_entrenamiento', $plan->id_plan_entrenamiento)
                ->max('orden') + 1;

            $rutina = Rutina::create([
                'id_plan_entrenamiento' => $plan->id_plan_entrenamiento,
                'nombre' => $data['nombre'],
                'descripcion' => $this->descripcionConNivel($data),
                'dia_semana' => $data['dia_semana'] ?? null,
                'orden' => $data['orden'] ?? $orden,
                'duracion_min' => $data['duracion_min'] ?? null,
                'estado' => 'ACTIVA',
            ]);

            foreach ($ejercicios as $i => $ej) {
                $this->agregarEjercicio($rutina, $ej, $i + 1);
            }

            return $rutina->load('ejercicios.ejercicio');
        });
    }

    public function agregarEjercicio(Rutina $rutina, array $data, ?int $orden = null): RutinaEjercicio
    {
        $ejercicioId = $data['id_ejercicio'] ?? null;

        if (! $ejercicioId && ! empty($data['nombre_ejercicio'])) {
            $ejercicio = Ejercicio::create([
                'nombre' => $data['nombre_ejercicio'],
                'grupo_muscular' => $data['grupo_muscular'] ?? null,
            ]);
            $ejercicioId = $ejercicio->id_ejercicio;
        }

        return RutinaEjercicio::create([
            'id_rutina' => $rutina->id_rutina,
            'id_ejercicio' => $ejercicioId,
            'series' => $data['series'] ?? 3,
            'repeticiones' => $data['repeticiones'] ?? '12',
            'descanso_seg' => $data['descanso_seg'] ?? 60,
            'peso_sugerido_kg' => $data['peso_sugerido_kg'] ?? null,
            'notas' => $data['notas'] ?? null,
            'orden' => $orden ?? ((int) RutinaEjercicio::query()->where('id_rutina', $rutina->id_rutina)->max('orden') + 1),
        ]);
    }

    private function descripcionConNivel(array $data): ?string
    {
        $parts = [];
        if (! empty($data['nivel'])) {
            $parts[] = 'Nivel: '.$data['nivel'];
        }
        if (! empty($data['descripcion'])) {
            $parts[] = $data['descripcion'];
        }

        return $parts ? implode("\n", $parts) : null;
    }
}
