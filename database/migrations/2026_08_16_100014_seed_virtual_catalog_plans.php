<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $planes = [
            [
                'buscar' => ['VIRTUAL-ESENCIAL', 'VIRTUAL-START', 'plan-virtual-esencial', 'plan-virtual-start'],
                'modulos' => ['PERFIL', 'ENTRENAMIENTO', 'CONTENIDO_VIRTUAL', 'PROGRESO', 'COMUNICACION'],
                'data' => [
                    'codigo' => 'VIRTUAL-START',
                    'nombre' => 'FigueFit Virtual Start',
                    'slug' => 'plan-virtual-start',
                    'descripcion' => 'El punto de partida para entrenar en casa: rutinas claras, videos y un coach que te acompaña por chat.',
                    'caracteristicas_json' => json_encode([
                        'Rutinas 4 días por semana',
                        'Biblioteca de videos guiados',
                        'Chat con coach',
                        'Registro de peso y medidas',
                    ]),
                    'precio' => 89000,
                    'duracion_dias' => 30,
                    'modalidad' => 'VIRTUAL',
                    'tipo_cliente' => 'INDIVIDUAL',
                    'cupo_maximo' => 80,
                    'cupos_totales' => 80,
                    'orden' => 1,
                    'popular' => 0,
                    'destacado' => 0,
                    'etiqueta' => null,
                    'estado' => 'ACTIVO',
                    'estado_plan' => 'ACTIVO',
                ],
            ],
            [
                'buscar' => ['PRESENCIAL-PLUS', 'VIRTUAL-PRO', 'plan-presencial-plus', 'plan-virtual-pro'],
                'modulos' => ['PERFIL', 'ENTRENAMIENTO', 'NUTRICION', 'CONTENIDO_VIRTUAL', 'PROGRESO', 'COMUNICACION', 'PAGOS'],
                'data' => [
                    'codigo' => 'VIRTUAL-PRO',
                    'nombre' => 'FigueFit Virtual Pro',
                    'slug' => 'plan-virtual-pro',
                    'descripcion' => 'Entrenamiento y nutrición en un solo plan. Ideal si ya entrenas y quieres resultados con seguimiento completo.',
                    'caracteristicas_json' => json_encode([
                        'Plan de entrenamiento personalizado',
                        'Menú nutricional semanal',
                        'Videos con desbloqueo progresivo',
                        'Metas, fotos y evolución',
                        'Comunidad FigueFit',
                    ]),
                    'precio' => 159000,
                    'duracion_dias' => 30,
                    'modalidad' => 'VIRTUAL',
                    'tipo_cliente' => 'INDIVIDUAL',
                    'cupo_maximo' => 60,
                    'cupos_totales' => 60,
                    'orden' => 2,
                    'popular' => 1,
                    'destacado' => 1,
                    'etiqueta' => 'MÁS ELEGIDO',
                    'estado' => 'ACTIVO',
                    'estado_plan' => 'ACTIVO',
                ],
            ],
            [
                'buscar' => ['MIXTO-PREMIUM', 'VIRTUAL-EMPRESAS', 'plan-mixto-premium', 'plan-virtual-empresas'],
                'modulos' => ['PERFIL', 'ENTRENAMIENTO', 'CONTENIDO_VIRTUAL', 'PROGRESO', 'COMUNICACION'],
                'data' => [
                    'codigo' => 'VIRTUAL-EMPRESAS',
                    'nombre' => 'FigueFit Virtual Empresas',
                    'slug' => 'plan-virtual-empresas',
                    'descripcion' => 'Bienestar virtual para equipos de empresas o universidades: un solo plan, varios colaboradores y reporte de avance.',
                    'caracteristicas_json' => json_encode([
                        'Hasta 25 colaboradores',
                        'Rutinas y retos grupales',
                        'Coach asignado al grupo',
                        'Tablero de avance institucional',
                        'Reporte mensual para RR. HH.',
                    ]),
                    'precio' => 2490000,
                    'duracion_dias' => 90,
                    'modalidad' => 'VIRTUAL',
                    'tipo_cliente' => 'INSTITUCIONAL',
                    'cupo_maximo' => 25,
                    'cupos_totales' => 25,
                    'orden' => 3,
                    'popular' => 0,
                    'destacado' => 1,
                    'etiqueta' => 'INSTITUCIONAL',
                    'estado' => 'ACTIVO',
                    'estado_plan' => 'ACTIVO',
                ],
            ],
        ];

        foreach ($planes as $plan) {
            $existente = DB::table('planes')
                ->where(function ($query) use ($plan) {
                    $query->whereIn('codigo', $plan['buscar'])
                        ->orWhereIn('slug', $plan['buscar']);
                })
                ->first();

            if ($existente) {
                DB::table('planes')->where('id_plan', $existente->id_plan)->update($plan['data']);
                $idPlan = $existente->id_plan;
            } else {
                $idPlan = DB::table('planes')->insertGetId($plan['data'] + [
                    'moneda' => 'COP',
                ]);
            }

            DB::table('plan_modulos')->where('id_plan', $idPlan)->delete();

            foreach ($plan['modulos'] as $i => $codigo) {
                $idModulo = DB::table('modulos_servicio')->where('codigo', $codigo)->value('id_modulo');
                if (! $idModulo) {
                    continue;
                }

                DB::table('plan_modulos')->insert([
                    'id_plan' => $idPlan,
                    'id_modulo' => $idModulo,
                    'incluido' => 1,
                    'orden' => $i + 1,
                ]);
            }
        }

        DB::table('planes')
            ->whereIn('modalidad', ['PRESENCIAL', 'MIXTO'])
            ->update([
                'estado_plan' => 'INACTIVO',
                'estado' => 'INACTIVO',
            ]);
    }

    public function down(): void
    {
        //
    }
};
