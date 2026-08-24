<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('propuestas_plan', function (Blueprint $table) {
            $table->increments('id_propuesta');
            $table->unsignedInteger('id_coach');
            $table->unsignedInteger('creado_por')->nullable();
            $table->unsignedInteger('id_programa')->nullable();
            $table->unsignedInteger('id_plan_nutricional')->nullable();
            $table->unsignedInteger('id_plan_publicado')->nullable();
            $table->string('titulo_interno', 150);
            $table->text('descripcion_interna')->nullable();
            $table->string('estado', 40)->default('BORRADOR_ENTRENAMIENTO');
            $table->text('notas_coach')->nullable();
            $table->text('notas_admin')->nullable();
            $table->text('motivo_rechazo')->nullable();
            $table->timestamp('enviado_en')->nullable();
            $table->timestamp('revisado_en')->nullable();
            $table->timestamp('publicado_en')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->nullable();

            $table->foreign('id_coach')->references('id_coach')->on('coaches');
            $table->foreign('creado_por')->references('id_user')->on('usuarios')->nullOnDelete();
            $table->foreign('id_programa')->references('id_programa')->on('programas_virtuales')->nullOnDelete();
            $table->foreign('id_plan_nutricional')->references('id_plan_nutricional')->on('planes_nutricionales')->nullOnDelete();
            $table->foreign('id_plan_publicado')->references('id_plan')->on('planes')->nullOnDelete();
        });

        Schema::table('programas_virtuales', function (Blueprint $table) {
            $table->unsignedInteger('id_propuesta')->nullable()->after('id_plan');
        });

        Schema::table('planes_nutricionales', function (Blueprint $table) {
            $table->unsignedInteger('id_propuesta')->nullable()->after('id_plan_cliente');
        });

        Schema::create('novedades_welcome', function (Blueprint $table) {
            $table->increments('id_novedad');
            $table->string('titulo', 150);
            $table->string('subtitulo', 255)->nullable();
            $table->text('descripcion')->nullable();
            $table->string('imagen_url', 500);
            $table->string('tipo', 30)->default('GENERAL');
            $table->string('boton_texto', 80)->default('Ver más');
            $table->string('url_destino', 500)->nullable();
            $table->unsignedInteger('id_plan')->nullable();
            $table->boolean('activo')->default(true);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamp('inicia_en')->nullable();
            $table->timestamp('termina_en')->nullable();
            $table->unsignedInteger('creado_por')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->nullable();

            $table->foreign('id_plan')->references('id_plan')->on('planes')->nullOnDelete();
            $table->foreign('creado_por')->references('id_user')->on('usuarios')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('novedades_welcome');

        Schema::table('planes_nutricionales', function (Blueprint $table) {
            $table->dropColumn('id_propuesta');
        });

        Schema::table('programas_virtuales', function (Blueprint $table) {
            $table->dropColumn('id_propuesta');
        });

        Schema::dropIfExists('propuestas_plan');
    }
};
