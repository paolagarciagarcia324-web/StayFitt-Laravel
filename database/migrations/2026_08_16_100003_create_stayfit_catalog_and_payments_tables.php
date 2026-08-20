<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planes', function (Blueprint $table) {
            $table->increments('id_plan');
            $table->string('codigo', 40)->nullable()->unique();
            $table->string('nombre', 150);
            $table->string('slug', 170)->unique();
            $table->text('descripcion')->nullable();
            $table->json('caracteristicas_json')->nullable();
            $table->text('inclusiones')->nullable();
            $table->text('beneficios')->nullable();
            $table->string('modalidad', 20);
            $table->string('tipo_cliente', 20)->default('INDIVIDUAL');
            $table->decimal('precio', 12, 2)->default(0);
            $table->char('moneda', 3)->default('COP');
            $table->unsignedInteger('duracion_dias')->default(30);
            $table->unsignedInteger('cupos_totales')->default(0);
            $table->unsignedInteger('cupos_reservados')->default(0);
            $table->unsignedInteger('cupos_ocupados')->default(0);
            $table->unsignedInteger('cupo_maximo')->nullable();
            $table->boolean('requiere_coach')->default(false);
            $table->boolean('renovacion_permitida')->default(true);
            $table->boolean('renovacion_automatica')->default(false);
            $table->unsignedInteger('dias_gracia')->default(3);
            $table->integer('orden')->default(0);
            $table->boolean('popular')->default(false);
            $table->boolean('destacado')->default(false);
            $table->string('etiqueta', 50)->nullable();
            $table->string('estado', 20)->default('ACTIVO');
            $table->string('estado_plan', 20)->default('BORRADOR');
            $table->unsignedInteger('creado_por')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->nullable();
            $table->foreign('creado_por')->references('id_user')->on('usuarios')->nullOnDelete();
        });

        Schema::create('modulos_servicio', function (Blueprint $table) {
            $table->increments('id_modulo');
            $table->string('codigo', 50)->unique();
            $table->string('codigo_modulo', 40)->nullable();
            $table->string('nombre', 100);
            $table->string('nombre_modulo', 80)->nullable();
            $table->string('descripcion', 255)->nullable();
            $table->string('icono', 50)->nullable();
            $table->integer('orden')->default(0);
            $table->string('exclusivo_modalidad', 20)->default('TODAS');
            $table->string('estado', 20)->default('ACTIVO');
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->nullable();
        });

        Schema::create('plan_modulos', function (Blueprint $table) {
            $table->increments('id_plan_modulo');
            $table->unsignedInteger('id_plan');
            $table->unsignedInteger('id_modulo');
            $table->boolean('incluido')->default(true);
            $table->integer('orden')->default(0);
            $table->timestamp('creado_en')->useCurrent();
            $table->unique(['id_plan', 'id_modulo']);
            $table->foreign('id_plan')->references('id_plan')->on('planes')->cascadeOnDelete();
            $table->foreign('id_modulo')->references('id_modulo')->on('modulos_servicio')->cascadeOnDelete();
        });

        Schema::create('enlaces_registro_institucional', function (Blueprint $table) {
            $table->increments('id_enlace');
            $table->unsignedInteger('id_institucion');
            $table->unsignedInteger('id_plan');
            $table->string('token', 80)->unique();
            $table->unsignedInteger('cupos_maximos');
            $table->unsignedInteger('usos_actuales')->default(0);
            $table->dateTime('fecha_expiracion');
            $table->string('estado', 20)->default('ACTIVO');
            $table->unsignedInteger('creado_por')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->foreign('id_institucion')->references('id_institucion')->on('instituciones');
            $table->foreign('id_plan')->references('id_plan')->on('planes');
        });

        Schema::create('descuentos_cupones', function (Blueprint $table) {
            $table->increments('id_descuento');
            $table->string('codigo', 40)->unique();
            $table->string('tipo', 20)->default('PORCENTAJE');
            $table->string('tipo_descuento', 20)->default('PORCENTAJE');
            $table->decimal('valor', 12, 2);
            $table->unsignedInteger('id_plan')->nullable();
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin');
            $table->unsignedInteger('usos_maximos')->nullable();
            $table->unsignedInteger('uso_maximo')->nullable();
            $table->unsignedInteger('usos_actuales')->default(0);
            $table->string('estado', 20)->default('ACTIVO');
            $table->string('estado_cupon', 20)->default('ACTIVO');
            $table->unsignedInteger('creado_por')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->nullable();
            $table->foreign('id_plan')->references('id_plan')->on('planes')->nullOnDelete();
        });

        Schema::create('solicitudes_compra', function (Blueprint $table) {
            $table->increments('id_solicitud');
            $table->string('codigo_solicitud', 40)->unique();
            $table->unsignedInteger('id_plan');
            $table->unsignedInteger('id_user')->nullable();
            $table->unsignedInteger('id_cliente')->nullable();
            $table->unsignedInteger('id_enlace')->nullable();
            $table->unsignedInteger('id_descuento')->nullable();
            $table->unsignedInteger('id_institucion')->nullable();
            $table->string('origen', 30)->default('COMPROBANTE');
            $table->string('modalidad', 20);
            $table->string('tipo_cliente', 20)->default('INDIVIDUAL');
            $table->string('nombres', 80);
            $table->string('apellidos', 80);
            $table->string('tipo_documento', 20)->default('CC');
            $table->string('documento', 30)->nullable();
            $table->string('documento_identidad', 30);
            $table->string('correo', 150);
            $table->string('telefono', 30)->nullable();
            $table->string('ciudad', 80)->nullable();
            $table->decimal('precio_lista', 12, 2)->default(0);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->boolean('acepto_terminos')->default(false);
            $table->dateTime('fecha_aceptacion_terminos')->nullable();
            $table->text('observacion_usuario')->nullable();
            $table->string('estado_solicitud', 20)->default('PENDIENTE');
            $table->boolean('reserva_cupo')->default(true);
            $table->dateTime('reserva_hasta')->nullable();
            $table->text('observacion_admin')->nullable();
            $table->unsignedInteger('id_admin_revisor')->nullable();
            $table->unsignedInteger('revisado_por')->nullable();
            $table->dateTime('revisada_en')->nullable();
            $table->dateTime('fecha_revision')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->nullable();
            $table->index('estado_solicitud');
            $table->foreign('id_plan')->references('id_plan')->on('planes');
            $table->foreign('id_user')->references('id_user')->on('usuarios')->nullOnDelete();
            $table->foreign('id_cliente')->references('id_cliente')->on('clientes')->nullOnDelete();
            $table->foreign('id_descuento')->references('id_descuento')->on('descuentos_cupones')->nullOnDelete();
            $table->foreign('id_institucion')->references('id_institucion')->on('instituciones')->nullOnDelete();
            $table->foreign('revisado_por')->references('id_user')->on('usuarios')->nullOnDelete();
        });

        Schema::create('usos_cupones', function (Blueprint $table) {
            $table->increments('id_uso_cupon');
            $table->unsignedInteger('id_descuento');
            $table->unsignedInteger('id_solicitud')->nullable();
            $table->string('correo', 150)->nullable();
            $table->dateTime('usado_en')->useCurrent();
            $table->foreign('id_descuento')->references('id_descuento')->on('descuentos_cupones');
            $table->foreign('id_solicitud')->references('id_solicitud')->on('solicitudes_compra')->nullOnDelete();
        });

        Schema::create('planes_cliente', function (Blueprint $table) {
            $table->increments('id_plan_cliente');
            $table->unsignedInteger('id_cliente');
            $table->unsignedInteger('id_plan');
            $table->unsignedInteger('id_solicitud')->nullable();
            $table->unsignedInteger('id_pago')->nullable();
            $table->unsignedInteger('id_suscripcion')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('estado', 20)->default('PENDIENTE');
            $table->string('estado_plan_cliente', 20)->default('ACTIVO');
            $table->boolean('renovacion_automatica')->default(false);
            $table->unsignedInteger('dias_gracia')->default(3);
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->nullable();
            $table->index(['id_cliente', 'estado_plan_cliente']);
            $table->foreign('id_cliente')->references('id_cliente')->on('clientes');
            $table->foreign('id_plan')->references('id_plan')->on('planes');
            $table->foreign('id_solicitud')->references('id_solicitud')->on('solicitudes_compra')->nullOnDelete();
        });

        Schema::create('pagos', function (Blueprint $table) {
            $table->increments('id_pago');
            $table->unsignedInteger('id_solicitud')->nullable();
            $table->unsignedInteger('id_plan_cliente')->nullable();
            $table->unsignedInteger('id_cliente')->nullable();
            $table->unsignedInteger('id_plan')->nullable();
            $table->string('tipo_pago', 30)->default('PLAN');
            $table->string('origen_pago', 30)->default('SOLICITUD_PLAN');
            $table->decimal('monto', 12, 2);
            $table->char('moneda', 3)->default('COP');
            $table->string('metodo', 30)->nullable();
            $table->string('metodo_pago', 30)->default('TRANSFERENCIA');
            $table->string('pasarela', 30)->default('NINGUNA');
            $table->string('referencia', 80)->nullable();
            $table->string('referencia_pago', 120)->nullable();
            $table->string('comprobante_url', 500)->nullable();
            $table->string('estado_pago', 30)->default('PENDIENTE');
            $table->dateTime('fecha_pago')->nullable();
            $table->dateTime('fecha_validacion')->nullable();
            $table->unsignedInteger('id_admin_validador')->nullable();
            $table->unsignedInteger('validado_por')->nullable();
            $table->text('observacion')->nullable();
            $table->text('observacion_admin')->nullable();
            $table->decimal('monto_descuento', 12, 2)->default(0);
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->nullable();
            $table->unique('id_solicitud');
            $table->index('estado_pago');
            $table->foreign('id_solicitud')->references('id_solicitud')->on('solicitudes_compra')->nullOnDelete();
            $table->foreign('id_cliente')->references('id_cliente')->on('clientes')->nullOnDelete();
            $table->foreign('id_plan')->references('id_plan')->on('planes')->nullOnDelete();
            $table->foreign('validado_por')->references('id_user')->on('usuarios')->nullOnDelete();
        });

        Schema::create('facturas', function (Blueprint $table) {
            $table->increments('id_factura');
            $table->string('numero_factura', 40)->unique();
            $table->unsignedInteger('id_pago');
            $table->string('tipo', 20)->default('PLAN');
            $table->string('razon_social', 150)->nullable();
            $table->string('documento_cliente', 30)->nullable();
            $table->string('correo_cliente', 150)->nullable();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('impuestos', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->string('pdf_url', 500)->nullable();
            $table->string('xml_url', 500)->nullable();
            $table->string('estado', 20)->default('EMITIDA');
            $table->dateTime('emitida_en')->useCurrent();
            $table->timestamp('creado_en')->useCurrent();
            $table->foreign('id_pago')->references('id_pago')->on('pagos');
        });

        Schema::create('suscripciones_recurrentes', function (Blueprint $table) {
            $table->increments('id_suscripcion');
            $table->unsignedInteger('id_cliente');
            $table->unsignedInteger('id_plan');
            $table->unsignedInteger('id_plan_cliente')->nullable();
            $table->string('periodicidad', 20);
            $table->string('token_pasarela', 120)->nullable();
            $table->string('pasarela', 30)->nullable();
            $table->string('estado', 20)->default('ACTIVA');
            $table->date('proximo_cobro')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->nullable();
            $table->foreign('id_cliente')->references('id_cliente')->on('clientes');
            $table->foreign('id_plan')->references('id_plan')->on('planes');
        });

        Schema::create('acceso_cliente_modulo', function (Blueprint $table) {
            $table->increments('id_acceso');
            $table->unsignedInteger('id_cliente');
            $table->unsignedInteger('id_modulo');
            $table->unsignedInteger('id_plan_cliente');
            $table->string('estado', 20)->default('ACTIVO');
            $table->string('estado_acceso', 20)->default('ACTIVO');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->nullable();
            $table->unique(['id_cliente', 'id_modulo', 'id_plan_cliente'], 'uq_acc_cli_mod_pc');
            $table->foreign('id_cliente')->references('id_cliente')->on('clientes');
            $table->foreign('id_modulo')->references('id_modulo')->on('modulos_servicio');
            $table->foreign('id_plan_cliente')->references('id_plan_cliente')->on('planes_cliente')->cascadeOnDelete();
        });

        Schema::create('configuracion_pasarelas', function (Blueprint $table) {
            $table->increments('id_config_pasarela');
            $table->string('codigo_pasarela', 30)->unique();
            $table->string('nombre', 80);
            $table->string('modo', 20)->default('SANDBOX');
            $table->string('comercio_id', 120)->nullable();
            $table->string('api_key_cifrada', 500)->nullable();
            $table->string('api_secret_cifrada', 500)->nullable();
            $table->string('webhook_secret_cifrado', 500)->nullable();
            $table->string('url_checkout', 500)->nullable();
            $table->string('url_webhook', 500)->nullable();
            $table->boolean('activa')->default(true);
            $table->timestamp('actualizado_en')->nullable();
        });

        Schema::create('transacciones_pasarela', function (Blueprint $table) {
            $table->increments('id_transaccion');
            $table->unsignedInteger('id_pago')->nullable();
            $table->unsignedInteger('id_solicitud')->nullable();
            $table->unsignedInteger('id_orden')->nullable();
            $table->string('pasarela', 30);
            $table->string('referencia_interna', 80)->unique();
            $table->string('referencia_externa', 120)->nullable();
            $table->decimal('monto', 12, 2);
            $table->char('moneda', 3)->default('COP');
            $table->string('estado_pasarela', 20)->default('CREATED');
            $table->boolean('firma_valida')->nullable();
            $table->string('idempotency_key', 80)->unique();
            $table->json('payload_envio')->nullable();
            $table->json('webhook_data')->nullable();
            $table->unsignedTinyInteger('intentos_activacion')->default(0);
            $table->boolean('activacion_ok')->default(false);
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizada_en')->nullable();
            $table->foreign('id_pago')->references('id_pago')->on('pagos')->nullOnDelete();
            $table->foreign('id_solicitud')->references('id_solicitud')->on('solicitudes_compra')->nullOnDelete();
        });

        Schema::create('eventos_webhook', function (Blueprint $table) {
            $table->increments('id_evento_webhook');
            $table->unsignedInteger('id_transaccion')->nullable();
            $table->string('pasarela', 30);
            $table->string('event_id_externo', 120)->nullable();
            $table->json('headers_json')->nullable();
            $table->json('payload_json');
            $table->string('firma_recibida', 255)->nullable();
            $table->boolean('firma_valida')->default(false);
            $table->boolean('procesado')->default(false);
            $table->string('error_proceso', 500)->nullable();
            $table->dateTime('recibido_en')->useCurrent();
            $table->foreign('id_transaccion')->references('id_transaccion')->on('transacciones_pasarela')->nullOnDelete();
        });

        Schema::create('conciliaciones_pasarela', function (Blueprint $table) {
            $table->increments('id_conciliacion');
            $table->unsignedInteger('id_transaccion');
            $table->string('estado_consultado', 40)->nullable();
            $table->boolean('coincidencia')->nullable();
            $table->json('respuesta_json')->nullable();
            $table->dateTime('ejecutada_en')->useCurrent();
            $table->foreign('id_transaccion')->references('id_transaccion')->on('transacciones_pasarela');
        });

        Schema::create('intentos_activacion', function (Blueprint $table) {
            $table->increments('id_intento');
            $table->unsignedInteger('id_transaccion');
            $table->unsignedTinyInteger('nro_intento');
            $table->string('resultado', 30);
            $table->string('detalle', 500)->nullable();
            $table->dateTime('ejecutado_en')->useCurrent();
            $table->foreign('id_transaccion')->references('id_transaccion')->on('transacciones_pasarela');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intentos_activacion');
        Schema::dropIfExists('conciliaciones_pasarela');
        Schema::dropIfExists('eventos_webhook');
        Schema::dropIfExists('transacciones_pasarela');
        Schema::dropIfExists('configuracion_pasarelas');
        Schema::dropIfExists('acceso_cliente_modulo');
        Schema::dropIfExists('suscripciones_recurrentes');
        Schema::dropIfExists('facturas');
        Schema::dropIfExists('pagos');
        Schema::dropIfExists('planes_cliente');
        Schema::dropIfExists('usos_cupones');
        Schema::dropIfExists('solicitudes_compra');
        Schema::dropIfExists('descuentos_cupones');
        Schema::dropIfExists('enlaces_registro_institucional');
        Schema::dropIfExists('plan_modulos');
        Schema::dropIfExists('modulos_servicio');
        Schema::dropIfExists('planes');
    }
};
