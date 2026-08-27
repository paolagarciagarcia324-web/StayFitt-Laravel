<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Contenido demo para el módulo Cliente Virtual (entrenamiento, nutrición, videos, progreso, eventos).
 * Idempotente: se puede ejecutar varias veces.
 */
class DemoClienteContenidoSeeder extends Seeder
{
    public function run(): void
    {
        $clienteUserId = DB::table('usuarios')->where('correo', 'valen.gomez@demo.figuefit.test')->value('id_user');
        if (! $clienteUserId) {
            $this->command?->warn('DemoClienteContenidoSeeder: no existe valen.gomez@demo.figuefit.test. Ejecuta DemoAdminSeeder primero.');

            return;
        }

        $cliente = DB::table('clientes')->where('id_user', $clienteUserId)->first();
        if (! $cliente) {
            return;
        }

        $coachId = $cliente->id_coach
            ?? DB::table('coaches')->orderBy('id_coach')->value('id_coach');
        $planClienteId = DB::table('planes_cliente')
            ->where('id_cliente', $cliente->id_cliente)
            ->where('estado_plan_cliente', 'ACTIVO')
            ->value('id_plan_cliente');

        $now = now();

        // Programa + videos
        $programaId = DB::table('programas_virtuales')->where('nombre', 'Programa Virtual FigueFit Demo')->value('id_programa');
        if (! $programaId) {
            $programaId = DB::table('programas_virtuales')->insertGetId([
                'nombre' => 'Programa Virtual FigueFit Demo',
                'descripcion' => 'Contenido base para clientes virtuales demo.',
                'estado' => 'ACTIVO',
                'creado_en' => $now,
            ]);
        }

        $catId = DB::table('categorias_video')->where('id_programa', $programaId)->where('nombre', 'Fundamentos')->value('id_categoria_video');
        if (! $catId) {
            $catId = DB::table('categorias_video')->insertGetId([
                'id_programa' => $programaId,
                'nombre' => 'Fundamentos',
                'orden' => 1,
                'creado_en' => $now,
            ]);
        }

        foreach ([
            [1, 'Activación y movilidad', 'https://www.youtube.com/watch?v=ml6cT4AZdqI'],
            [2, 'Fuerza full body', 'https://www.youtube.com/watch?v=UoC_O3HzsH0'],
            [3, 'Core y estabilidad', 'https://www.youtube.com/watch?v=DHD1-2P94DI'],
        ] as [$dia, $titulo, $url]) {
            $exists = DB::table('videos')->where('id_categoria_video', $catId)->where('dia_orden', $dia)->exists();
            if (! $exists) {
                DB::table('videos')->insert([
                    'id_categoria_video' => $catId,
                    'dia_orden' => $dia,
                    'titulo' => $titulo,
                    'descripcion' => "Sesión demo día {$dia}.",
                    'url_video' => $url,
                    'duracion_segundos' => 600 + ($dia * 60),
                    'requiere_ia' => false,
                    'estado' => 'ACTIVO',
                    'creado_en' => $now,
                ]);
            }
        }

        // Ejercicios + plan entrenamiento + rutinas
        $ejercicios = [];
        foreach ([
            ['Sentadilla goblet', 'Piernas'],
            ['Press de pecho', 'Pecho'],
            ['Remo con banda', 'Espalda'],
            ['Plancha', 'Core'],
        ] as [$nombre, $grupo]) {
            $id = DB::table('ejercicios')->where('nombre', $nombre)->value('id_ejercicio');
            if (! $id) {
                $id = DB::table('ejercicios')->insertGetId([
                    'nombre' => $nombre,
                    'grupo_muscular' => $grupo,
                    'descripcion' => "Ejercicio demo {$nombre}",
                    'instrucciones' => 'Mantén control y respiración.',
                    'creado_en' => $now,
                ]);
            }
            $ejercicios[] = $id;
        }

        $planEntId = DB::table('planes_entrenamiento')
            ->where('id_cliente', $cliente->id_cliente)
            ->where('nombre', 'Plan fuerza Valentina')
            ->value('id_plan_entrenamiento');

        if (! $planEntId && $coachId) {
            $planEntId = DB::table('planes_entrenamiento')->insertGetId([
                'id_cliente' => $cliente->id_cliente,
                'id_coach' => $coachId,
                'id_plan_cliente' => $planClienteId,
                'nombre' => 'Plan fuerza Valentina',
                'objetivo' => $cliente->objetivo_principal ?: 'Recomposición',
                'modalidad' => 'VIRTUAL',
                'estado' => 'ACTIVO',
                'creado_en' => $now,
            ]);

            $rutinaId = DB::table('rutinas')->insertGetId([
                'id_plan_entrenamiento' => $planEntId,
                'nombre' => 'Full body A',
                'descripcion' => 'Circuito de fuerza técnica.',
                'dia_semana' => 1,
                'orden' => 1,
                'duracion_min' => 45,
                'estado' => 'ACTIVA',
                'creado_en' => $now,
            ]);

            foreach ($ejercicios as $i => $ejId) {
                DB::table('rutina_ejercicios')->insert([
                    'id_rutina' => $rutinaId,
                    'id_ejercicio' => $ejId,
                    'series' => 3,
                    'repeticiones' => '10-12',
                    'descanso_seg' => 60,
                    'peso_sugerido_kg' => 8 + $i,
                    'orden' => $i + 1,
                    'notas' => 'Tempo controlado',
                ]);
            }

            DB::table('registro_rutinas')->insert([
                'id_cliente' => $cliente->id_cliente,
                'id_rutina' => $rutinaId,
                'fecha_ejecucion' => $now->copy()->subDays(2)->toDateString(),
                'completada' => true,
                'dificultad' => 6,
                'observacion_cliente' => 'Buen ritmo',
                'creado_en' => $now,
            ]);
        }

        // Nutrición
        $planNutId = DB::table('planes_nutricionales')
            ->where('id_cliente', $cliente->id_cliente)
            ->where('nombre', 'Plan nutricional demo')
            ->value('id_plan_nutricional');

        if (! $planNutId && $coachId) {
            $planNutId = DB::table('planes_nutricionales')->insertGetId([
                'id_cliente' => $cliente->id_cliente,
                'id_coach' => $coachId,
                'id_plan_cliente' => $planClienteId,
                'nombre' => 'Plan nutricional demo',
                'objetivo' => 'Déficit ligero',
                'kcal_objetivo' => 1800,
                'proteinas_g' => 120,
                'carbohidratos_g' => 180,
                'grasas_g' => 55,
                'recomendaciones' => 'Prioriza proteína en cada comida e hidratación 2.5 L.',
                'estado' => 'ACTIVO',
                'creado_en' => $now,
            ]);

            foreach ([
                ['DESAYUNO', 'Bowl proteico', 'Avena + yogurt + frutos rojos', 1],
                ['ALMUERZO', 'Pollo y quinoa', '150g pechuga + quinoa + ensalada', 2],
                ['CENA', 'Salmón al horno', 'Salmón + vegetales al vapor', 3],
            ] as [$tipo, $nombre, $receta, $orden]) {
                DB::table('comidas')->insert([
                    'id_plan_nutricional' => $planNutId,
                    'tipo_comida' => $tipo,
                    'nombre' => $nombre,
                    'receta' => $receta,
                    'porcion' => '1 ración',
                    'calorias' => 450,
                    'proteinas_g' => 35,
                    'carbohidratos_g' => 40,
                    'grasas_g' => 12,
                    'orden' => $orden,
                    'creado_en' => $now,
                ]);
            }
        }

        // Progreso
        if (! DB::table('registros_progreso')->where('id_cliente', $cliente->id_cliente)->exists()) {
            foreach ([0, 7, 14] as $i => $dias) {
                $peso = 68 - ($i * 0.4);
                DB::table('registros_progreso')->insert([
                    'id_cliente' => $cliente->id_cliente,
                    'id_coach' => $coachId,
                    'id_plan_cliente' => $planClienteId,
                    'fecha_registro' => $now->copy()->subDays(14 - $dias)->toDateString(),
                    'peso_kg' => $peso,
                    'estatura_cm' => 165,
                    'imc' => round($peso / (1.65 * 1.65), 2),
                    'cintura_cm' => 74 - $i,
                    'nivel_energia' => 7,
                    'calidad_sueno' => 8,
                    'agua_litros' => 2.2,
                    'es_linea_base' => $i === 0,
                    'creado_en' => $now,
                ]);
            }
        }

        if (! DB::table('metas_cliente')->where('id_cliente', $cliente->id_cliente)->exists()) {
            DB::table('metas_cliente')->insert([
                'id_cliente' => $cliente->id_cliente,
                'nombre' => 'Bajar a 65 kg',
                'metrica' => 'PESO',
                'valor_inicial' => 68,
                'valor_actual' => 67.2,
                'valor_objetivo' => 65,
                'unidad' => 'kg',
                'fecha_inicio' => $now->copy()->subDays(14)->toDateString(),
                'fecha_objetivo' => $now->copy()->addDays(45)->toDateString(),
                'estado' => 'ACTIVA',
                'creado_en' => $now,
            ]);
        }

        // Evento publicado si no hay futuros
        if (! DB::table('eventos')->where('estado', 'PUBLICADO')->where('fecha_inicio', '>=', $now)->exists()) {
            DB::table('eventos')->insert([
                'id_admin' => DB::table('usuarios')->orderBy('id_user')->value('id_user'),
                'nombre' => 'Live movilidad FigueFit',
                'descripcion' => 'Sesión abierta para clientes virtuales.',
                'tipo' => 'LIVE',
                'fecha_inicio' => $now->copy()->addDays(3),
                'fecha_fin' => $now->copy()->addDays(3)->addHours(1),
                'modalidad' => 'VIRTUAL',
                'cupo_maximo' => 50,
                'cupo_inscritos' => 0,
                'estado' => 'PUBLICADO',
                'creado_en' => $now,
            ]);
        }

        // Preferencias
        if (! DB::table('preferencias_cliente')->where('id_cliente', $cliente->id_cliente)->exists()) {
            DB::table('preferencias_cliente')->insert([
                'id_cliente' => $cliente->id_cliente,
                'notif_email' => 1,
                'notif_push' => 1,
                'notif_whatsapp' => 0,
                'idioma' => 'es',
                'creado_en' => $now,
            ]);
        }

        $this->command?->info('Contenido demo cliente virtual listo (valen.gomez@demo.figuefit.test).');
    }
}
