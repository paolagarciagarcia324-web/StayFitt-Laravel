<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos_tienda', function (Blueprint $table) {
            if (! Schema::hasColumn('productos_tienda', 'calificacion')) {
                $table->decimal('calificacion', 2, 1)->default(0)->after('precio');
            }
        });
    }

    public function down(): void
    {
        Schema::table('productos_tienda', function (Blueprint $table) {
            if (Schema::hasColumn('productos_tienda', 'calificacion')) {
                $table->dropColumn('calificacion');
            }
        });
    }
};
