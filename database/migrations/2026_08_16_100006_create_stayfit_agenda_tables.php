<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disponibilidades', function (Blueprint $table) {
            $table->increments('id_disponibilidad');
            $table->unsignedInteger('id_coach');
            $table->unsignedTinyInteger('dia_semana');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->string('modalidad', 20);
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_en')->useCurrent();
            $table->foreign('id_coach')->references('id_coach')->on('coaches');
        });

        Schema::create('sesion_participantes', function (Blueprint $table) {
            $table->increments('id_sesion_participante');
            $table->unsignedInteger('id_sesion');
            $table->unsignedInteger('id_cliente');
            $table->string('estado', 20)->default('INSCRITO');
            $table->dateTime('inscrito_en')->useCurrent();
            $table->timestamp('creado_en')->useCurrent();
            $table->unique(['id_sesion', 'id_cliente']);
            $table->foreign('id_sesion')->references('id_sesion')->on('sesiones')->cascadeOnDelete();
            $table->foreign('id_cliente')->references('id_cliente')->on('clientes');
        });

        Schema::create('eventos', function (Blueprint $table) {
            $table->increments('id_evento');
            $table->unsignedInteger('id_coach')->nullable();
            $table->unsignedInteger('id_admin')->nullable();
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->string('tipo', 30)->default('TALLER');
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin')->nullable();
            $table->string('modalidad', 20);
            $table->unsignedInteger('cupo_maximo')->nullable();
            $table->unsignedInteger('cupo_inscritos')->default(0);
            $table->string('estado', 20)->default('BORRADOR');
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->nullable();
            $table->foreign('id_coach')->references('id_coach')->on('coaches')->nullOnDelete();
            $table->foreign('id_admin')->references('id_user')->on('usuarios')->nullOnDelete();
        });

        Schema::create('evento_participantes', function (Blueprint $table) {
            $table->increments('id_evento_participante');
            $table->unsignedInteger('id_evento');
            $table->unsignedInteger('id_cliente');
            $table->string('estado', 20)->default('INSCRITO');
            $table->dateTime('inscrito_en')->useCurrent();
            $table->timestamp('creado_en')->useCurrent();
            $table->unique(['id_evento', 'id_cliente']);
            $table->foreign('id_evento')->references('id_evento')->on('eventos')->cascadeOnDelete();
            $table->foreign('id_cliente')->references('id_cliente')->on('clientes');
        });

        Schema::create('encuestas_satisfaccion', function (Blueprint $table) {
            $table->increments('id_encuesta');
            $table->unsignedInteger('id_cliente');
            $table->unsignedInteger('id_plan_cliente')->nullable();
            $table->unsignedTinyInteger('calificacion');
            $table->text('comentario')->nullable();
            $table->boolean('recomienda_plan')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->foreign('id_cliente')->references('id_cliente')->on('clientes');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('encuestas_satisfaccion');
        Schema::dropIfExists('evento_participantes');
        Schema::dropIfExists('eventos');
        Schema::dropIfExists('sesion_participantes');
        Schema::dropIfExists('disponibilidades');
    }
};
