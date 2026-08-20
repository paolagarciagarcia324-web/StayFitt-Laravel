<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            Schema::table('planes_cliente', function (Blueprint $table) {
                $table->foreign('id_pago', 'fk_pc_pago')->references('id_pago')->on('pagos')->nullOnDelete();
            });
            Schema::table('pagos', function (Blueprint $table) {
                $table->foreign('id_plan_cliente', 'fk_pag_pc')->references('id_plan_cliente')->on('planes_cliente')->nullOnDelete();
            });
            Schema::table('suscripciones_recurrentes', function (Blueprint $table) {
                $table->foreign('id_plan_cliente', 'fk_sus_pc')->references('id_plan_cliente')->on('planes_cliente')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('suscripciones_recurrentes', function (Blueprint $table) {
            $table->dropForeign('fk_sus_pc');
        });
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropForeign('fk_pag_pc');
        });
        Schema::table('planes_cliente', function (Blueprint $table) {
            $table->dropForeign('fk_pc_pago');
        });
    }
};
