<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programas_virtuales', function (Blueprint $table) {
            $table->increments('id_programa');
            $table->string('nombre', 120);
            $table->text('descripcion')->nullable();
            $table->unsignedInteger('id_plan')->nullable();
            $table->string('estado', 20)->default('ACTIVO');
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->nullable();
            $table->foreign('id_plan')->references('id_plan')->on('planes')->nullOnDelete();
        });

        Schema::create('categorias_video', function (Blueprint $table) {
            $table->increments('id_categoria_video');
            $table->unsignedInteger('id_programa');
            $table->string('nombre', 80);
            $table->unsignedSmallInteger('orden')->default(1);
            $table->timestamp('creado_en')->useCurrent();
            $table->foreign('id_programa')->references('id_programa')->on('programas_virtuales')->cascadeOnDelete();
        });

        Schema::create('videos', function (Blueprint $table) {
            $table->increments('id_video');
            $table->unsignedInteger('id_categoria_video');
            $table->unsignedSmallInteger('dia_orden');
            $table->string('titulo', 150);
            $table->text('descripcion')->nullable();
            $table->string('url_video', 500);
            $table->unsignedInteger('duracion_segundos')->nullable();
            $table->boolean('requiere_ia')->default(true);
            $table->string('estado', 20)->default('ACTIVO');
            $table->timestamp('creado_en')->useCurrent();
            $table->unique(['id_categoria_video', 'dia_orden']);
            $table->foreign('id_categoria_video')->references('id_categoria_video')->on('categorias_video')->cascadeOnDelete();
        });

        Schema::create('progreso_videos', function (Blueprint $table) {
            $table->increments('id_progreso_video');
            $table->unsignedInteger('id_cliente');
            $table->unsignedInteger('id_video');
            $table->unsignedInteger('id_plan_cliente')->nullable();
            $table->string('estado', 20)->default('BLOQUEADO');
            $table->dateTime('iniciado_en')->nullable();
            $table->dateTime('completado_en')->nullable();
            $table->decimal('porcentaje', 5, 2)->default(0);
            $table->boolean('llama_racha')->default(false);
            $table->unique(['id_cliente', 'id_video']);
            $table->foreign('id_cliente')->references('id_cliente')->on('clientes');
            $table->foreign('id_video')->references('id_video')->on('videos');
        });

        Schema::create('validaciones_ia_video', function (Blueprint $table) {
            $table->increments('id_validacion_ia');
            $table->unsignedInteger('id_progreso_video');
            $table->unsignedInteger('id_cliente');
            $table->unsignedInteger('repeticiones_detectadas')->default(0);
            $table->unsignedInteger('repeticiones_correctas')->default(0);
            $table->unsignedInteger('alertas_postura')->default(0);
            $table->string('resultado', 20)->default('PENDIENTE');
            $table->json('detalle_json')->nullable();
            $table->dateTime('registrada_en')->useCurrent();
            $table->foreign('id_progreso_video')->references('id_progreso_video')->on('progreso_videos')->cascadeOnDelete();
        });

        Schema::create('planes_entrenamiento', function (Blueprint $table) {
            $table->increments('id_plan_entrenamiento');
            $table->unsignedInteger('id_cliente')->nullable();
            $table->unsignedInteger('id_coach');
            $table->unsignedInteger('id_plan_cliente')->nullable();
            $table->string('nombre', 120);
            $table->string('objetivo', 120)->nullable();
            $table->string('modalidad', 20);
            $table->string('estado', 30)->default('BORRADOR');
            $table->unsignedInteger('id_admin_validador')->nullable();
            $table->dateTime('validado_en')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->nullable();
            $table->foreign('id_coach')->references('id_coach')->on('coaches');
            $table->foreign('id_cliente')->references('id_cliente')->on('clientes')->nullOnDelete();
        });

        Schema::create('rutinas', function (Blueprint $table) {
            $table->increments('id_rutina');
            $table->unsignedInteger('id_plan_entrenamiento');
            $table->string('nombre', 120);
            $table->text('descripcion')->nullable();
            $table->unsignedTinyInteger('dia_semana')->nullable();
            $table->unsignedSmallInteger('orden')->default(1);
            $table->unsignedSmallInteger('duracion_min')->nullable();
            $table->string('estado', 20)->default('ACTIVA');
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->nullable();
            $table->foreign('id_plan_entrenamiento')->references('id_plan_entrenamiento')->on('planes_entrenamiento')->cascadeOnDelete();
        });

        Schema::create('ejercicios', function (Blueprint $table) {
            $table->increments('id_ejercicio');
            $table->string('nombre', 120);
            $table->string('grupo_muscular', 80)->nullable();
            $table->text('descripcion')->nullable();
            $table->text('instrucciones')->nullable();
            $table->timestamp('creado_en')->useCurrent();
        });

        Schema::create('rutina_ejercicios', function (Blueprint $table) {
            $table->increments('id_rutina_ejercicio');
            $table->unsignedInteger('id_rutina');
            $table->unsignedInteger('id_ejercicio');
            $table->unsignedSmallInteger('series')->nullable();
            $table->string('repeticiones', 40)->nullable();
            $table->unsignedSmallInteger('descanso_seg')->nullable();
            $table->decimal('peso_sugerido_kg', 6, 2)->nullable();
            $table->unsignedTinyInteger('rir')->nullable();
            $table->string('ritmo_ejecucion', 40)->nullable();
            $table->unsignedSmallInteger('orden')->default(1);
            $table->string('notas', 255)->nullable();
            $table->foreign('id_rutina')->references('id_rutina')->on('rutinas')->cascadeOnDelete();
            $table->foreign('id_ejercicio')->references('id_ejercicio')->on('ejercicios');
        });

        Schema::create('material_entrenamiento', function (Blueprint $table) {
            $table->increments('id_material');
            $table->unsignedInteger('id_ejercicio')->nullable();
            $table->unsignedInteger('id_rutina')->nullable();
            $table->string('tipo', 20);
            $table->string('titulo', 150)->nullable();
            $table->string('url_archivo', 500)->nullable();
            $table->text('contenido_texto')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->foreign('id_ejercicio')->references('id_ejercicio')->on('ejercicios')->nullOnDelete();
            $table->foreign('id_rutina')->references('id_rutina')->on('rutinas')->nullOnDelete();
        });

        Schema::create('registro_rutinas', function (Blueprint $table) {
            $table->increments('id_registro_rutina');
            $table->unsignedInteger('id_cliente');
            $table->unsignedInteger('id_rutina');
            $table->date('fecha_ejecucion');
            $table->boolean('completada')->default(true);
            $table->unsignedTinyInteger('dificultad')->nullable();
            $table->text('observacion_cliente')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->foreign('id_cliente')->references('id_cliente')->on('clientes');
            $table->foreign('id_rutina')->references('id_rutina')->on('rutinas');
        });

        Schema::create('logs_ejercicio_detalle', function (Blueprint $table) {
            $table->increments('id_log');
            $table->unsignedInteger('id_registro_rutina');
            $table->unsignedInteger('id_rutina_ejercicio');
            $table->unsignedSmallInteger('series_realizadas')->nullable();
            $table->string('repeticiones_realizadas', 40)->nullable();
            $table->decimal('peso_kg', 6, 2)->nullable();
            $table->unsignedTinyInteger('rir_real')->nullable();
            $table->string('observacion', 255)->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->foreign('id_registro_rutina')->references('id_registro_rutina')->on('registro_rutinas')->cascadeOnDelete();
        });

        Schema::create('sesiones', function (Blueprint $table) {
            $table->increments('id_sesion');
            $table->unsignedInteger('id_coach');
            $table->string('tipo', 30);
            $table->string('titulo', 150);
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->string('modalidad', 20);
            $table->unsignedSmallInteger('cupo_maximo')->default(1);
            $table->unsignedSmallInteger('cupo_reservado')->default(0);
            $table->string('estado', 20)->default('PROGRAMADA');
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->nullable();
            $table->foreign('id_coach')->references('id_coach')->on('coaches');
        });

        Schema::create('asistencias', function (Blueprint $table) {
            $table->increments('id_asistencia');
            $table->unsignedInteger('id_cliente');
            $table->unsignedInteger('id_coach');
            $table->unsignedInteger('id_sesion')->nullable();
            $table->date('fecha');
            $table->string('estado', 20);
            $table->boolean('llama_racha')->default(false);
            $table->string('observacion', 255)->nullable();
            $table->timestamp('registrada_en')->useCurrent();
            $table->foreign('id_cliente')->references('id_cliente')->on('clientes');
            $table->foreign('id_coach')->references('id_coach')->on('coaches');
            $table->foreign('id_sesion')->references('id_sesion')->on('sesiones')->nullOnDelete();
        });

        Schema::create('rachas_cliente', function (Blueprint $table) {
            $table->increments('id_racha');
            $table->unsignedInteger('id_cliente');
            $table->string('tipo', 20);
            $table->unsignedInteger('dias_consecutivos')->default(0);
            $table->unsignedInteger('mejor_racha')->default(0);
            $table->date('ultima_fecha')->nullable();
            $table->timestamp('actualizado_en')->nullable();
            $table->unique(['id_cliente', 'tipo']);
            $table->foreign('id_cliente')->references('id_cliente')->on('clientes')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rachas_cliente');
        Schema::dropIfExists('asistencias');
        Schema::dropIfExists('sesiones');
        Schema::dropIfExists('logs_ejercicio_detalle');
        Schema::dropIfExists('registro_rutinas');
        Schema::dropIfExists('material_entrenamiento');
        Schema::dropIfExists('rutina_ejercicios');
        Schema::dropIfExists('ejercicios');
        Schema::dropIfExists('rutinas');
        Schema::dropIfExists('planes_entrenamiento');
        Schema::dropIfExists('validaciones_ia_video');
        Schema::dropIfExists('progreso_videos');
        Schema::dropIfExists('videos');
        Schema::dropIfExists('categorias_video');
        Schema::dropIfExists('programas_virtuales');
    }
};
