<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->increments('id_chat');
            $table->string('tipo', 20);
            $table->unsignedInteger('id_plan')->nullable();
            $table->unsignedInteger('id_coach')->nullable();
            $table->string('nombre', 120)->nullable();
            $table->string('estado', 20)->default('ACTIVO');
            $table->timestamp('creado_en')->useCurrent();
            $table->foreign('id_plan')->references('id_plan')->on('planes')->nullOnDelete();
            $table->foreign('id_coach')->references('id_coach')->on('coaches')->nullOnDelete();
        });

        Schema::create('chat_participantes', function (Blueprint $table) {
            $table->increments('id_chat_participante');
            $table->unsignedInteger('id_chat');
            $table->unsignedInteger('id_user');
            $table->string('rol_en_chat', 30);
            $table->dateTime('unido_en')->useCurrent();
            $table->unique(['id_chat', 'id_user']);
            $table->foreign('id_chat')->references('id_chat')->on('chats')->cascadeOnDelete();
            $table->foreign('id_user')->references('id_user')->on('usuarios')->cascadeOnDelete();
        });

        Schema::create('mensajes', function (Blueprint $table) {
            $table->increments('id_mensaje');
            $table->unsignedInteger('id_chat');
            $table->unsignedInteger('id_user_emisor');
            $table->text('contenido');
            $table->string('tipo_mensaje', 20)->default('TEXTO');
            $table->string('url_adjunto', 500)->nullable();
            $table->boolean('leido')->default(false);
            $table->dateTime('enviado_en')->useCurrent();
            $table->timestamp('creado_en')->useCurrent();
            $table->foreign('id_chat')->references('id_chat')->on('chats')->cascadeOnDelete();
            $table->foreign('id_user_emisor')->references('id_user')->on('usuarios');
        });

        Schema::create('notificaciones', function (Blueprint $table) {
            $table->increments('id_notificacion');
            $table->unsignedInteger('id_user');
            $table->string('tipo', 30);
            $table->string('titulo', 150);
            $table->string('mensaje', 500);
            $table->boolean('leida')->default(false);
            $table->string('canal', 20)->default('IN_APP');
            $table->string('referencia_tipo', 40)->nullable();
            $table->unsignedBigInteger('referencia_id')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->index(['id_user', 'leida']);
            $table->foreign('id_user')->references('id_user')->on('usuarios')->cascadeOnDelete();
        });

        Schema::create('configuracion_whatsapp', function (Blueprint $table) {
            $table->increments('id_config_wa');
            $table->string('numero_business', 30);
            $table->string('api_token_cifrado', 500)->nullable();
            $table->string('link_grupo_principal', 300)->nullable();
            $table->text('mensaje_bienvenida')->nullable();
            $table->string('horario_atencion', 120)->nullable();
            $table->json('respuestas_automaticas')->nullable();
            $table->boolean('activa')->default(true);
            $table->timestamp('actualizado_en')->nullable();
        });

        Schema::create('grupos_whatsapp', function (Blueprint $table) {
            $table->increments('id_grupo_wa');
            $table->unsignedInteger('id_config_wa');
            $table->string('nombre', 120);
            $table->string('tipo', 20);
            $table->string('criterio', 80)->nullable();
            $table->string('link_invitacion', 300);
            $table->string('estado', 20)->default('ACTIVO');
            $table->timestamp('creado_en')->useCurrent();
            $table->foreign('id_config_wa')->references('id_config_wa')->on('configuracion_whatsapp');
        });

        Schema::create('participantes_grupo_whatsapp', function (Blueprint $table) {
            $table->increments('id_part_wa');
            $table->unsignedInteger('id_grupo_wa');
            $table->unsignedInteger('id_cliente');
            $table->string('estado', 20)->default('INVITADO');
            $table->dateTime('unido_en')->nullable();
            $table->unique(['id_grupo_wa', 'id_cliente']);
            $table->foreign('id_grupo_wa')->references('id_grupo_wa')->on('grupos_whatsapp')->cascadeOnDelete();
            $table->foreign('id_cliente')->references('id_cliente')->on('clientes');
        });

        Schema::create('invitaciones_whatsapp', function (Blueprint $table) {
            $table->increments('id_invitacion_wa');
            $table->unsignedInteger('id_cliente');
            $table->unsignedInteger('id_grupo_wa')->nullable();
            $table->string('link_mostrado', 300);
            $table->dateTime('mostrada_en')->useCurrent();
            $table->dateTime('aceptada_en')->nullable();
            $table->boolean('cerrada_sin_aceptar')->default(false);
            $table->boolean('persistencia_local')->default(false);
            $table->foreign('id_cliente')->references('id_cliente')->on('clientes');
            $table->foreign('id_grupo_wa')->references('id_grupo_wa')->on('grupos_whatsapp')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitaciones_whatsapp');
        Schema::dropIfExists('participantes_grupo_whatsapp');
        Schema::dropIfExists('grupos_whatsapp');
        Schema::dropIfExists('configuracion_whatsapp');
        Schema::dropIfExists('notificaciones');
        Schema::dropIfExists('mensajes');
        Schema::dropIfExists('chat_participantes');
        Schema::dropIfExists('chats');
    }
};
