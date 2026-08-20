<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id_rol');
            $table->string('codigo', 40)->unique();
            $table->string('codigo_rol', 40)->nullable();
            $table->string('nombre', 80);
            $table->string('descripcion', 255)->nullable();
            $table->string('estado', 20)->default('ACTIVO');
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->nullable();
        });

        Schema::create('usuarios', function (Blueprint $table) {
            $table->increments('id_user');
            $table->string('nombres', 80);
            $table->string('apellidos', 80);
            $table->string('Tipo_documento', 30)->nullable();
            $table->string('documento_identidad', 30)->nullable();
            $table->string('correo', 150)->unique();
            $table->string('email', 150)->nullable()->unique();
            $table->string('codigo_pais', 5)->default('+57');
            $table->string('telefono', 30)->nullable();
            $table->string('password_hash', 255);
            $table->string('foto_perfil_url', 500)->nullable();
            $table->string('idioma', 10)->default('es');
            $table->string('zona_horaria', 50)->default('America/Bogota');
            $table->boolean('acepto_terminos')->default(false);
            $table->dateTime('fecha_aceptacion_terminos')->nullable();
            $table->string('estado', 20)->default('PENDIENTE');
            $table->string('estado_cuenta', 20)->nullable();
            $table->dateTime('correo_verificado_en')->nullable();
            $table->dateTime('email_verificado_en')->nullable();
            $table->dateTime('ultimo_acceso_en')->nullable();
            $table->dateTime('ultimo_acceso')->nullable();
            $table->rememberToken();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->nullable();
            $table->index('estado');
        });

        Schema::create('user_roles', function (Blueprint $table) {
            $table->increments('id_user_rol');
            $table->unsignedInteger('id_user');
            $table->unsignedInteger('id_rol');
            $table->unsignedInteger('asignado_por')->nullable();
            $table->timestamp('asignado_en')->useCurrent();
            $table->timestamp('creado_en')->useCurrent();
            $table->unique(['id_user', 'id_rol']);
            $table->foreign('id_user')->references('id_user')->on('usuarios')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('id_rol')->references('id_rol')->on('roles')->restrictOnDelete();
            $table->foreign('asignado_por')->references('id_user')->on('usuarios')->nullOnDelete();
        });

        Schema::create('tokens_recuperacion', function (Blueprint $table) {
            $table->increments('id_token');
            $table->unsignedInteger('id_user');
            $table->string('token_hash', 255);
            $table->dateTime('expira_en');
            $table->dateTime('usado_en')->nullable();
            $table->string('ip_solicitud', 45)->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->foreign('id_user')->references('id_user')->on('usuarios')->cascadeOnDelete();
        });

        Schema::create('sesiones_auth', function (Blueprint $table) {
            $table->increments('id_sesion_auth');
            $table->unsignedInteger('id_user');
            $table->string('id_sesion_app', 128);
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->dateTime('iniciada_en')->useCurrent();
            $table->dateTime('cerrada_en')->nullable();
            $table->foreign('id_user')->references('id_user')->on('usuarios')->cascadeOnDelete();
        });

        Schema::create('instituciones', function (Blueprint $table) {
            $table->increments('id_institucion');
            $table->string('nombre', 150);
            $table->string('nit', 30)->nullable()->unique();
            $table->string('contacto_nombre', 120)->nullable();
            $table->string('correo', 150)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('ciudad', 80)->nullable();
            $table->string('direccion', 200)->nullable();
            $table->string('convenio', 200)->nullable();
            $table->unsignedInteger('cupos_totales')->default(0);
            $table->unsignedInteger('cupos_usados')->default(0);
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->string('estado', 20)->default('ACTIVA');
            $table->text('observaciones')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->nullable();
        });

        Schema::create('coaches', function (Blueprint $table) {
            $table->increments('id_coach');
            $table->unsignedInteger('id_user')->unique();
            $table->string('codigo_coach', 40)->nullable()->unique();
            $table->string('especialidad', 120)->nullable();
            $table->text('biografia')->nullable();
            $table->string('certificaciones', 500)->nullable();
            $table->string('foto_url', 500)->nullable();
            $table->decimal('calificacion_promedio', 3, 2)->default(0);
            $table->unsignedInteger('total_valoraciones')->default(0);
            $table->string('estado_coach', 20)->default('ACTIVO');
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->nullable();
            $table->foreign('id_user')->references('id_user')->on('usuarios');
        });

        Schema::create('clientes', function (Blueprint $table) {
            $table->increments('id_cliente');
            $table->unsignedInteger('id_user')->unique();
            $table->string('tipo_cliente', 20)->default('INDIVIDUAL');
            $table->string('modalidad', 20);
            $table->string('tipo_documento', 20)->default('CC');
            $table->string('documento', 30)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('genero', 20)->nullable();
            $table->string('ciudad', 80)->nullable();
            $table->decimal('peso_inicial_kg', 6, 2)->nullable();
            $table->decimal('estatura_cm', 6, 2)->nullable();
            $table->string('objetivo_principal', 80)->nullable();
            $table->text('alergias')->nullable();
            $table->text('condiciones_medicas')->nullable();
            $table->text('lesiones')->nullable();
            $table->text('medicamentos')->nullable();
            $table->string('contacto_emergencia_nombre', 120)->nullable();
            $table->string('contacto_emergencia_telefono', 30)->nullable();
            $table->unsignedInteger('id_institucion')->nullable();
            $table->unsignedInteger('id_coach')->nullable();
            $table->unsignedInteger('id_coach_asignado')->nullable();
            $table->unsignedInteger('id_horario')->nullable();
            $table->string('estado_cliente', 20)->default('PROSPECTO');
            $table->date('fecha_alta')->nullable();
            $table->dateTime('fecha_activacion')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->nullable();
            $table->index('estado_cliente');
            $table->index('modalidad');
            $table->foreign('id_user')->references('id_user')->on('usuarios');
            $table->foreign('id_institucion')->references('id_institucion')->on('instituciones')->nullOnDelete();
            $table->foreign('id_coach')->references('id_coach')->on('coaches')->nullOnDelete();
        });

        Schema::create('preferencias_cliente', function (Blueprint $table) {
            $table->increments('id_preferencia');
            $table->unsignedInteger('id_cliente')->unique();
            $table->boolean('notif_email')->default(true);
            $table->boolean('notif_push')->default(true);
            $table->boolean('notif_whatsapp')->default(true);
            $table->string('horario_preferido', 20)->nullable();
            $table->unsignedSmallInteger('recordatorio_minutos')->default(60);
            $table->string('idioma', 10)->default('es');
            $table->string('privacidad_fotos', 20)->default('COACH_ADMIN');
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->nullable();
            $table->foreign('id_cliente')->references('id_cliente')->on('clientes')->cascadeOnDelete();
        });

        Schema::create('historial_estados_cliente', function (Blueprint $table) {
            $table->increments('id_historial');
            $table->unsignedInteger('id_cliente');
            $table->string('estado_anterior', 30)->nullable();
            $table->string('estado_nuevo', 30);
            $table->string('motivo', 255)->nullable();
            $table->unsignedInteger('id_user_responsable')->nullable();
            $table->unsignedInteger('cambiado_por')->nullable();
            $table->dateTime('fecha_cambio')->useCurrent();
            $table->timestamp('creado_en')->useCurrent();
            $table->foreign('id_cliente')->references('id_cliente')->on('clientes')->cascadeOnDelete();
            $table->foreign('id_user_responsable')->references('id_user')->on('usuarios')->nullOnDelete();
        });

        Schema::create('historial_asignacion_coach', function (Blueprint $table) {
            $table->increments('id_asignacion');
            $table->unsignedInteger('id_cliente');
            $table->unsignedInteger('id_coach_anterior')->nullable();
            $table->unsignedInteger('id_coach_nuevo');
            $table->unsignedInteger('id_admin')->nullable();
            $table->string('motivo', 255)->nullable();
            $table->dateTime('asignado_en')->useCurrent();
            $table->foreign('id_cliente')->references('id_cliente')->on('clientes')->cascadeOnDelete();
            $table->foreign('id_coach_nuevo')->references('id_coach')->on('coaches');
        });

        Schema::create('valoraciones_coach', function (Blueprint $table) {
            $table->increments('id_valoracion');
            $table->unsignedInteger('id_coach');
            $table->unsignedInteger('id_cliente');
            $table->unsignedTinyInteger('calificacion');
            $table->text('comentario')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->unique(['id_coach', 'id_cliente']);
            $table->foreign('id_coach')->references('id_coach')->on('coaches');
            $table->foreign('id_cliente')->references('id_cliente')->on('clientes');
        });

        Schema::create('medidas_antropometricas_iniciales', function (Blueprint $table) {
            $table->increments('id_medida_inicial');
            $table->unsignedInteger('id_cliente')->unique();
            $table->decimal('peso_kg', 6, 2);
            $table->decimal('estatura_cm', 6, 2);
            $table->decimal('cintura_cm', 6, 2)->nullable();
            $table->decimal('cadera_cm', 6, 2)->nullable();
            $table->decimal('brazo_cm', 6, 2)->nullable();
            $table->decimal('pierna_cm', 6, 2)->nullable();
            $table->string('origen', 30)->default('REGISTRO_PRESENCIAL');
            $table->dateTime('registrada_en')->useCurrent();
            $table->foreign('id_cliente')->references('id_cliente')->on('clientes')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medidas_antropometricas_iniciales');
        Schema::dropIfExists('valoraciones_coach');
        Schema::dropIfExists('historial_asignacion_coach');
        Schema::dropIfExists('historial_estados_cliente');
        Schema::dropIfExists('preferencias_cliente');
        Schema::dropIfExists('clientes');
        Schema::dropIfExists('coaches');
        Schema::dropIfExists('instituciones');
        Schema::dropIfExists('sesiones_auth');
        Schema::dropIfExists('tokens_recuperacion');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('roles');
    }
};
