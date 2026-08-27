<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instituciones', function (Blueprint $table) {
            $table->string('logo_url', 500)->nullable()->after('nombre');
            $table->string('tipo_institucion', 80)->nullable()->after('logo_url');
            $table->string('contacto_cargo', 120)->nullable()->after('contacto_nombre');
            $table->unsignedInteger('id_user_responsable')->nullable()->after('estado');
            $table->unsignedInteger('id_plan_contratado')->nullable()->after('id_user_responsable');

            $table->foreign('id_user_responsable')->references('id_user')->on('usuarios')->nullOnDelete();
            $table->foreign('id_plan_contratado')->references('id_plan')->on('planes')->nullOnDelete();
        });

        Schema::create('invitaciones_institucionales', function (Blueprint $table) {
            $table->increments('id_invitacion');
            $table->unsignedInteger('id_institucion');
            $table->unsignedInteger('id_plan')->nullable();
            $table->string('correo', 150);
            $table->string('nombre_sugerido', 120)->nullable();
            $table->string('token', 80)->unique();
            $table->string('estado', 20)->default('PENDIENTE');
            $table->dateTime('fecha_expiracion');
            $table->unsignedInteger('id_user_creador')->nullable();
            $table->unsignedInteger('id_cliente_vinculado')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('aceptada_en')->nullable();

            $table->foreign('id_institucion')->references('id_institucion')->on('instituciones')->cascadeOnDelete();
            $table->foreign('id_plan')->references('id_plan')->on('planes')->nullOnDelete();
            $table->foreign('id_user_creador')->references('id_user')->on('usuarios')->nullOnDelete();
            $table->foreign('id_cliente_vinculado')->references('id_cliente')->on('clientes')->nullOnDelete();
        });

        Schema::create('comunicados_institucionales', function (Blueprint $table) {
            $table->increments('id_comunicado');
            $table->unsignedInteger('id_institucion');
            $table->unsignedInteger('id_user_emisor');
            $table->string('titulo', 200);
            $table->text('mensaje');
            $table->string('canal', 30)->default('IN_APP');
            $table->unsignedInteger('destinatarios')->default(0);
            $table->timestamp('creado_en')->useCurrent();

            $table->foreign('id_institucion')->references('id_institucion')->on('instituciones')->cascadeOnDelete();
            $table->foreign('id_user_emisor')->references('id_user')->on('usuarios');
        });

        $exists = DB::table('roles')
            ->where('codigo', 'RESPONSABLE_INSTITUCIONAL')
            ->orWhere('codigo_rol', 'RESPONSABLE_INSTITUCIONAL')
            ->exists();

        if (! $exists) {
            DB::table('roles')->insert([
                'codigo' => 'RESPONSABLE_INSTITUCIONAL',
                'codigo_rol' => 'RESPONSABLE_INSTITUCIONAL',
                'nombre' => 'Responsable Institucional',
                'descripcion' => 'Portal B2B de convenios empresariales',
                'estado' => 'ACTIVO',
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('comunicados_institucionales');
        Schema::dropIfExists('invitaciones_institucionales');

        Schema::table('instituciones', function (Blueprint $table) {
            $table->dropForeign(['id_user_responsable']);
            $table->dropForeign(['id_plan_contratado']);
            $table->dropColumn([
                'logo_url',
                'tipo_institucion',
                'contacto_cargo',
                'id_user_responsable',
                'id_plan_contratado',
            ]);
        });

        DB::table('roles')->where('codigo', 'RESPONSABLE_INSTITUCIONAL')->delete();
    }
};
