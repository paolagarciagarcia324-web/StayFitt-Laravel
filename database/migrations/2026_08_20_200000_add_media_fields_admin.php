<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('planes', function (Blueprint $table) {
            if (! Schema::hasColumn('planes', 'imagen_url')) {
                $table->string('imagen_url', 500)->nullable()->after('descripcion');
            }
        });

        Schema::table('eventos', function (Blueprint $table) {
            if (! Schema::hasColumn('eventos', 'imagen_url')) {
                $table->string('imagen_url', 500)->nullable()->after('descripcion');
            }
        });

        Schema::table('configuracion_pasarelas', function (Blueprint $table) {
            if (! Schema::hasColumn('configuracion_pasarelas', 'logo_url')) {
                $table->string('logo_url', 500)->nullable()->after('nombre');
            }
        });

        Schema::table('ordenes_tienda', function (Blueprint $table) {
            if (! Schema::hasColumn('ordenes_tienda', 'comprobante_url')) {
                $table->string('comprobante_url', 500)->nullable()->after('observacion_admin');
            }
        });
    }

    public function down(): void
    {
        Schema::table('planes', function (Blueprint $table) {
            if (Schema::hasColumn('planes', 'imagen_url')) {
                $table->dropColumn('imagen_url');
            }
        });

        Schema::table('eventos', function (Blueprint $table) {
            if (Schema::hasColumn('eventos', 'imagen_url')) {
                $table->dropColumn('imagen_url');
            }
        });

        Schema::table('configuracion_pasarelas', function (Blueprint $table) {
            if (Schema::hasColumn('configuracion_pasarelas', 'logo_url')) {
                $table->dropColumn('logo_url');
            }
        });

        Schema::table('ordenes_tienda', function (Blueprint $table) {
            if (Schema::hasColumn('ordenes_tienda', 'comprobante_url')) {
                $table->dropColumn('comprobante_url');
            }
        });
    }
};
