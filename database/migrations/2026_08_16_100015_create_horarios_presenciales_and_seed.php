<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horarios_presenciales', function (Blueprint $table) {
            $table->increments('id_horario');
            $table->string('codigo', 40)->unique();
            $table->string('nombre', 120);
            $table->string('dias', 80);
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->unsignedInteger('cupo_maximo')->default(20);
            $table->unsignedInteger('cupo_ocupado')->default(0);
            $table->string('link_whatsapp', 300)->nullable();
            $table->boolean('activo')->default(true);
            $table->unsignedInteger('orden')->default(0);
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->nullable();
        });

        if (Schema::hasColumn('clientes', 'id_horario')) {
            Schema::table('clientes', function (Blueprint $table) {
                $table->foreign('id_horario', 'fk_cli_horario')
                    ->references('id_horario')
                    ->on('horarios_presenciales')
                    ->nullOnDelete();
            });
        }

        $horarios = [
            [
                'codigo' => 'AM-06',
                'nombre' => 'Mañana intensiva',
                'dias' => 'Lun · Mié · Vie',
                'hora_inicio' => '06:00:00',
                'hora_fin' => '07:00:00',
                'cupo_maximo' => 18,
                'link_whatsapp' => 'https://chat.whatsapp.com/figuefit-manana-06',
                'orden' => 1,
            ],
            [
                'codigo' => 'AM-08',
                'nombre' => 'Mañana funcional',
                'dias' => 'Mar · Jue · Sáb',
                'hora_inicio' => '08:00:00',
                'hora_fin' => '09:00:00',
                'cupo_maximo' => 18,
                'link_whatsapp' => 'https://chat.whatsapp.com/figuefit-manana-08',
                'orden' => 2,
            ],
            [
                'codigo' => 'PM-17',
                'nombre' => 'Tarde fuerza',
                'dias' => 'Lun · Mié · Vie',
                'hora_inicio' => '17:00:00',
                'hora_fin' => '18:00:00',
                'cupo_maximo' => 20,
                'link_whatsapp' => 'https://chat.whatsapp.com/figuefit-tarde-17',
                'orden' => 3,
            ],
            [
                'codigo' => 'PM-19',
                'nombre' => 'Noche community',
                'dias' => 'Mar · Jue',
                'hora_inicio' => '19:00:00',
                'hora_fin' => '20:00:00',
                'cupo_maximo' => 22,
                'link_whatsapp' => 'https://chat.whatsapp.com/figuefit-noche-19',
                'orden' => 4,
            ],
        ];

        foreach ($horarios as $horario) {
            DB::table('horarios_presenciales')->insert($horario + [
                'cupo_ocupado' => 0,
                'activo' => 1,
            ]);
        }

        $idPlan = DB::table('planes')->where('codigo', 'PRESENCIAL-PLUS')->value('id_plan');
        if ($idPlan) {
            DB::table('planes')->where('id_plan', $idPlan)->update([
                'nombre' => 'FigueFit Presencial Sala',
                'slug' => 'plan-presencial-sala',
                'descripcion' => 'Entrenamiento presencial en Campoalegre, Huila: sala funcional, seguimiento y comunidad por horario.',
                'caracteristicas_json' => json_encode([
                    'Sesiones en sede Campoalegre',
                    'Horario a elección',
                    'Evaluación antropométrica inicial',
                    'Grupo WhatsApp por horario',
                ]),
                'precio' => 249000,
                'modalidad' => 'PRESENCIAL',
                'tipo_cliente' => 'INDIVIDUAL',
                'estado' => 'ACTIVO',
                'estado_plan' => 'ACTIVO',
                'popular' => 1,
                'etiqueta' => 'PRESENCIAL',
                'orden' => 10,
            ]);
        } else {
            $idPlan = DB::table('planes')->insertGetId([
                'codigo' => 'PRESENCIAL-PLUS',
                'nombre' => 'FigueFit Presencial Sala',
                'slug' => 'plan-presencial-sala',
                'descripcion' => 'Entrenamiento presencial en Campoalegre, Huila: sala funcional, seguimiento y comunidad por horario.',
                'caracteristicas_json' => json_encode([
                    'Sesiones en sede Campoalegre',
                    'Horario a elección',
                    'Evaluación antropométrica inicial',
                    'Grupo WhatsApp por horario',
                ]),
                'precio' => 249000,
                'moneda' => 'COP',
                'duracion_dias' => 30,
                'modalidad' => 'PRESENCIAL',
                'tipo_cliente' => 'INDIVIDUAL',
                'cupo_maximo' => 40,
                'cupos_totales' => 40,
                'orden' => 10,
                'popular' => 1,
                'destacado' => 1,
                'etiqueta' => 'PRESENCIAL',
                'estado' => 'ACTIVO',
                'estado_plan' => 'ACTIVO',
            ]);
        }

        $modulos = ['PERFIL', 'ENTRENAMIENTO', 'NUTRICION', 'AGENDA', 'PROGRESO', 'COMUNICACION', 'TIENDA'];
        DB::table('plan_modulos')->where('id_plan', $idPlan)->delete();
        foreach ($modulos as $i => $codigo) {
            $idModulo = DB::table('modulos_servicio')->where('codigo', $codigo)->value('id_modulo');
            if ($idModulo) {
                DB::table('plan_modulos')->insert([
                    'id_plan' => $idPlan,
                    'id_modulo' => $idModulo,
                    'incluido' => 1,
                    'orden' => $i + 1,
                ]);
            }
        }

        if (Schema::hasTable('configuracion_whatsapp') && ! DB::table('configuracion_whatsapp')->exists()) {
            DB::table('configuracion_whatsapp')->insert([
                'numero_business' => '+573000000000',
                'link_grupo_principal' => 'https://chat.whatsapp.com/figuefit-comunidad',
                'mensaje_bienvenida' => 'Bienvenida a FigueFit presencial. Únete al grupo de tu horario.',
                'horario_atencion' => 'Lun a Sáb 6:00 a.m. – 8:00 p.m.',
                'activa' => 1,
            ]);
        }

        $idConfig = DB::table('configuracion_whatsapp')->where('activa', 1)->value('id_config_wa');
        if ($idConfig && Schema::hasTable('grupos_whatsapp')) {
            foreach (DB::table('horarios_presenciales')->get() as $h) {
                $existe = DB::table('grupos_whatsapp')
                    ->where('criterio', $h->codigo)
                    ->exists();
                if ($existe) {
                    continue;
                }
                DB::table('grupos_whatsapp')->insert([
                    'id_config_wa' => $idConfig,
                    'nombre' => 'WA · '.$h->nombre,
                    'tipo' => 'HORARIO',
                    'criterio' => $h->codigo,
                    'link_invitacion' => $h->link_whatsapp,
                    'estado' => 'ACTIVO',
                ]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('clientes', 'id_horario')) {
            Schema::table('clientes', function (Blueprint $table) {
                $table->dropForeign('fk_cli_horario');
            });
        }
        Schema::dropIfExists('horarios_presenciales');
    }
};
