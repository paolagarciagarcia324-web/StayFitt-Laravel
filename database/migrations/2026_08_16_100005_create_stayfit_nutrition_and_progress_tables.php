<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planes_nutricionales', function (Blueprint $table) {
            $table->increments('id_plan_nutricional');
            $table->unsignedInteger('id_cliente')->nullable();
            $table->unsignedInteger('id_coach');
            $table->unsignedInteger('id_plan_cliente')->nullable();
            $table->string('nombre', 120);
            $table->string('objetivo', 120)->nullable();
            $table->unsignedInteger('kcal_objetivo')->nullable();
            $table->decimal('proteinas_g', 8, 2)->nullable();
            $table->decimal('carbohidratos_g', 8, 2)->nullable();
            $table->decimal('grasas_g', 8, 2)->nullable();
            $table->text('recomendaciones')->nullable();
            $table->string('estado', 20)->default('BORRADOR');
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->nullable();
            $table->foreign('id_coach')->references('id_coach')->on('coaches');
            $table->foreign('id_cliente')->references('id_cliente')->on('clientes')->nullOnDelete();
        });

        Schema::create('comidas', function (Blueprint $table) {
            $table->increments('id_comida');
            $table->unsignedInteger('id_plan_nutricional');
            $table->string('tipo_comida', 20);
            $table->string('nombre', 150);
            $table->text('receta')->nullable();
            $table->string('porcion', 80)->nullable();
            $table->decimal('calorias', 8, 2)->nullable();
            $table->decimal('proteinas_g', 8, 2)->nullable();
            $table->decimal('carbohidratos_g', 8, 2)->nullable();
            $table->decimal('grasas_g', 8, 2)->nullable();
            $table->decimal('fibra_g', 8, 2)->nullable();
            $table->decimal('azucar_g', 8, 2)->nullable();
            $table->decimal('sodio_mg', 8, 2)->nullable();
            $table->decimal('colesterol_mg', 8, 2)->nullable();
            $table->string('alergenos', 255)->nullable();
            $table->text('vitaminas_minerales')->nullable();
            $table->unsignedSmallInteger('orden')->default(1);
            $table->timestamp('creado_en')->useCurrent();
            $table->foreign('id_plan_nutricional')->references('id_plan_nutricional')->on('planes_nutricionales')->cascadeOnDelete();
        });

        Schema::create('consultas_ia_nutricion', function (Blueprint $table) {
            $table->increments('id_consulta_ia');
            $table->unsignedInteger('id_cliente');
            $table->unsignedInteger('id_plan_nutricional')->nullable();
            $table->string('tipo', 30);
            $table->string('foto_url', 500);
            $table->json('alimentos_detectados')->nullable();
            $table->boolean('cumple_estructura')->nullable();
            $table->string('alerta', 20)->nullable();
            $table->json('macros_estimados')->nullable();
            $table->json('alternativas_json')->nullable();
            $table->text('respuesta_ia')->nullable();
            $table->dateTime('registrada_en')->useCurrent();
            $table->timestamp('creado_en')->useCurrent();
            $table->foreign('id_cliente')->references('id_cliente')->on('clientes');
        });

        Schema::create('registros_progreso', function (Blueprint $table) {
            $table->increments('id_registro_progreso');
            $table->unsignedInteger('id_cliente');
            $table->unsignedInteger('id_coach')->nullable();
            $table->unsignedInteger('id_plan_cliente')->nullable();
            $table->date('fecha_registro');
            $table->decimal('peso_kg', 6, 2)->nullable();
            $table->decimal('estatura_cm', 6, 2)->nullable();
            $table->decimal('porcentaje_grasa', 5, 2)->nullable();
            $table->decimal('masa_muscular_kg', 6, 2)->nullable();
            $table->decimal('imc', 5, 2)->nullable();
            $table->decimal('cuello_cm', 6, 2)->nullable();
            $table->decimal('hombro_cm', 6, 2)->nullable();
            $table->decimal('pecho_cm', 6, 2)->nullable();
            $table->decimal('brazo_cm', 6, 2)->nullable();
            $table->decimal('antebrazo_cm', 6, 2)->nullable();
            $table->decimal('cintura_cm', 6, 2)->nullable();
            $table->decimal('abdomen_cm', 6, 2)->nullable();
            $table->decimal('cadera_cm', 6, 2)->nullable();
            $table->decimal('gluteo_cm', 6, 2)->nullable();
            $table->decimal('pierna_cm', 6, 2)->nullable();
            $table->decimal('gemelo_cm', 6, 2)->nullable();
            $table->unsignedSmallInteger('fc_reposo')->nullable();
            $table->unsignedSmallInteger('pa_sistolica')->nullable();
            $table->unsignedSmallInteger('pa_diastolica')->nullable();
            $table->unsignedTinyInteger('nivel_energia')->nullable();
            $table->unsignedTinyInteger('calidad_sueno')->nullable();
            $table->unsignedTinyInteger('nivel_estres')->nullable();
            $table->decimal('agua_litros', 4, 2)->nullable();
            $table->text('observacion_coach')->nullable();
            $table->boolean('es_linea_base')->default(false);
            $table->timestamp('creado_en')->useCurrent();
            $table->foreign('id_cliente')->references('id_cliente')->on('clientes');
        });

        Schema::create('fotos_progreso', function (Blueprint $table) {
            $table->increments('id_foto');
            $table->unsignedInteger('id_registro_progreso');
            $table->string('angulo', 20);
            $table->string('url_foto', 500);
            $table->timestamp('creado_en')->useCurrent();
            $table->foreign('id_registro_progreso')->references('id_registro_progreso')->on('registros_progreso')->cascadeOnDelete();
        });

        Schema::create('metas_cliente', function (Blueprint $table) {
            $table->increments('id_meta');
            $table->unsignedInteger('id_cliente');
            $table->string('nombre', 120);
            $table->string('metrica', 60);
            $table->decimal('valor_inicial', 10, 2);
            $table->decimal('valor_actual', 10, 2);
            $table->decimal('valor_objetivo', 10, 2);
            $table->string('unidad', 20)->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_objetivo')->nullable();
            $table->string('estado', 20)->default('ACTIVA');
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->nullable();
            $table->foreign('id_cliente')->references('id_cliente')->on('clientes');
        });

        Schema::create('recordatorios', function (Blueprint $table) {
            $table->increments('id_recordatorio');
            $table->unsignedInteger('id_cliente')->nullable();
            $table->unsignedInteger('id_coach')->nullable();
            $table->string('tipo', 30);
            $table->string('titulo', 150);
            $table->string('mensaje', 500)->nullable();
            $table->string('recurrencia', 20)->default('UNICO');
            $table->string('dias_semana', 20)->nullable();
            $table->dateTime('fecha_hora');
            $table->unsignedSmallInteger('minutos_antes')->default(60);
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_en')->useCurrent();
            $table->foreign('id_cliente')->references('id_cliente')->on('clientes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recordatorios');
        Schema::dropIfExists('metas_cliente');
        Schema::dropIfExists('fotos_progreso');
        Schema::dropIfExists('registros_progreso');
        Schema::dropIfExists('consultas_ia_nutricion');
        Schema::dropIfExists('comidas');
        Schema::dropIfExists('planes_nutricionales');
    }
};
