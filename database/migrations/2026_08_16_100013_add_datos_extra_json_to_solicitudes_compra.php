<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes_compra', function (Blueprint $table) {
            if (! Schema::hasColumn('solicitudes_compra', 'datos_extra_json')) {
                $table->json('datos_extra_json')->nullable()->after('observacion_usuario');
            }
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_compra', function (Blueprint $table) {
            if (Schema::hasColumn('solicitudes_compra', 'datos_extra_json')) {
                $table->dropColumn('datos_extra_json');
            }
        });
    }
};
