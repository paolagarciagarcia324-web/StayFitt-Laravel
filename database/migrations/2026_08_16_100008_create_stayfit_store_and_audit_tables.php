<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias_producto', function (Blueprint $table) {
            $table->increments('id_categoria_producto');
            $table->string('nombre', 80);
            $table->string('slug', 100)->nullable();
            $table->string('descripcion', 255)->nullable();
            $table->integer('orden')->default(0);
            $table->string('estado', 20)->default('ACTIVA');
            $table->timestamp('creado_en')->useCurrent();
        });

        Schema::create('productos_tienda', function (Blueprint $table) {
            $table->increments('id_producto');
            $table->unsignedInteger('id_categoria_producto');
            $table->string('sku', 40)->unique();
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 12, 2);
            $table->string('imagen_url', 500)->nullable();
            $table->integer('stock_total')->default(0);
            $table->integer('stock_minimo')->default(2);
            $table->boolean('exclusivo_presencial')->default(true);
            $table->string('estado', 20)->default('ACTIVO');
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->nullable();
            $table->foreign('id_categoria_producto')->references('id_categoria_producto')->on('categorias_producto');
        });

        Schema::create('producto_variantes', function (Blueprint $table) {
            $table->increments('id_variante');
            $table->unsignedInteger('id_producto');
            $table->string('talla', 20)->nullable();
            $table->string('color', 40)->nullable();
            $table->string('sku_variante', 50)->unique();
            $table->integer('stock')->default(0);
            $table->string('estado', 20)->default('ACTIVA');
            $table->foreign('id_producto')->references('id_producto')->on('productos_tienda')->cascadeOnDelete();
        });

        Schema::create('carritos', function (Blueprint $table) {
            $table->increments('id_carrito');
            $table->unsignedInteger('id_cliente');
            $table->string('estado', 20)->default('ABIERTO');
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->nullable();
            $table->foreign('id_cliente')->references('id_cliente')->on('clientes');
        });

        Schema::create('carrito_items', function (Blueprint $table) {
            $table->increments('id_item');
            $table->unsignedInteger('id_carrito');
            $table->unsignedInteger('id_producto');
            $table->unsignedInteger('id_variante')->nullable();
            $table->unsignedInteger('cantidad')->default(1);
            $table->decimal('precio_unitario', 12, 2);
            $table->foreign('id_carrito')->references('id_carrito')->on('carritos')->cascadeOnDelete();
            $table->foreign('id_producto')->references('id_producto')->on('productos_tienda');
        });

        Schema::create('ordenes_tienda', function (Blueprint $table) {
            $table->increments('id_orden');
            $table->string('numero_orden', 40)->unique();
            $table->unsignedInteger('id_cliente');
            $table->unsignedInteger('id_pago')->nullable();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('costo_envio', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->string('tipo_entrega', 30);
            $table->string('direccion_entrega', 255)->nullable();
            $table->string('ciudad_entrega', 80)->nullable();
            $table->string('estado_orden', 30)->default('PENDIENTE_PAGO');
            $table->string('observacion_admin', 255)->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->nullable();
            $table->foreign('id_cliente')->references('id_cliente')->on('clientes');
            $table->foreign('id_pago')->references('id_pago')->on('pagos')->nullOnDelete();
        });

        Schema::create('orden_detalle', function (Blueprint $table) {
            $table->increments('id_detalle');
            $table->unsignedInteger('id_orden');
            $table->unsignedInteger('id_producto');
            $table->unsignedInteger('id_variante')->nullable();
            $table->unsignedInteger('cantidad');
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->foreign('id_orden')->references('id_orden')->on('ordenes_tienda')->cascadeOnDelete();
            $table->foreign('id_producto')->references('id_producto')->on('productos_tienda');
        });

        Schema::create('alertas_stock', function (Blueprint $table) {
            $table->increments('id_alerta_stock');
            $table->unsignedInteger('id_producto');
            $table->unsignedInteger('id_variante')->nullable();
            $table->integer('stock_actual');
            $table->integer('umbral');
            $table->dateTime('notificada_en')->useCurrent();
            $table->foreign('id_producto')->references('id_producto')->on('productos_tienda');
        });

        Schema::create('reportes_generados', function (Blueprint $table) {
            $table->increments('id_reporte');
            $table->unsignedInteger('id_user');
            $table->string('tipo', 30);
            $table->json('parametros_json')->nullable();
            $table->string('formato', 10);
            $table->string('url_archivo', 500)->nullable();
            $table->dateTime('generado_en')->useCurrent();
            $table->foreign('id_user')->references('id_user')->on('usuarios');
        });

        Schema::create('bitacora_sistema', function (Blueprint $table) {
            $table->increments('id_bitacora');
            $table->unsignedInteger('id_user')->nullable();
            $table->string('accion', 80);
            $table->string('entidad', 80);
            $table->string('id_entidad', 40)->nullable();
            $table->json('datos_antes')->nullable();
            $table->json('datos_despues')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('resultado', 20)->default('OK');
            $table->string('mensaje', 500)->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->foreign('id_user')->references('id_user')->on('usuarios')->nullOnDelete();
        });

        Schema::create('configuracion_sistema', function (Blueprint $table) {
            $table->increments('id_config');
            $table->string('clave', 80)->unique();
            $table->text('valor')->nullable();
            $table->string('descripcion', 255)->nullable();
            $table->timestamp('actualizado_en')->nullable();
        });

        Schema::create('matriz_trazabilidad', function (Blueprint $table) {
            $table->increments('id_trazabilidad');
            $table->string('id_rf', 10)->unique();
            $table->string('id_hu', 20);
            $table->string('id_cu', 10);
            $table->string('modulo', 60);
            $table->string('actor_principal', 80);
            $table->string('tablas_relacionadas', 500);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matriz_trazabilidad');
        Schema::dropIfExists('configuracion_sistema');
        Schema::dropIfExists('bitacora_sistema');
        Schema::dropIfExists('reportes_generados');
        Schema::dropIfExists('alertas_stock');
        Schema::dropIfExists('orden_detalle');
        Schema::dropIfExists('ordenes_tienda');
        Schema::dropIfExists('carrito_items');
        Schema::dropIfExists('carritos');
        Schema::dropIfExists('producto_variantes');
        Schema::dropIfExists('productos_tienda');
        Schema::dropIfExists('categorias_producto');
    }
};
