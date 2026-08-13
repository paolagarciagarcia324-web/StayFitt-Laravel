<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
INSERT INTO roles (codigo, nombre, descripcion) VALUES
('ADMIN', 'Administrador', 'Gestion total del sistema StayFit'),
('COACH', 'Coach', 'Gestion de clientes, rutinas, nutricion y asistencia'),
('CLIENTE', 'Cliente Individual', 'Cliente con plan individual virtual o presencial'),
('CLIENTE_INSTITUCIONAL', 'Cliente Institucional', 'Cliente vinculado a institucion/convenio');
SQL);

        DB::unprepared(<<<'SQL'
INSERT INTO modulos_servicio (codigo, nombre, descripcion, icono, orden) VALUES
('PERFIL', 'Perfil', 'Datos personales, fisicos y contrasena', 'user', 1),
('ENTRENAMIENTO', 'Entrenamiento', 'Rutinas y ejercicios asignados', 'dumbbell', 2),
('NUTRICION', 'Nutricion', 'Plan alimentario + chat IA de plato', 'apple', 3),
('CONTENIDO_VIRTUAL', 'Contenido Virtual', 'Videos con desbloqueo progresivo e IA', 'video', 4),
('PROGRESO', 'Progreso', 'Fotos, medidas, timeline y graficas', 'chart', 5),
('AGENDA', 'Agenda', 'Calendario, rachas y recordatorios', 'calendar', 6),
('PAGOS', 'Pagos', 'Facturas, vigencia y renovacion', 'credit-card', 7),
('COMUNICACION', 'Comunicacion', 'Chat individual y comunidad', 'message', 8),
('TIENDA', 'Tienda', 'Productos FigueFit', 'shopping-bag', 9),
('SESIONES_GRUPALES', 'Sesiones Grupales', 'Exclusivo institucional', 'users', 10);
SQL);

        DB::unprepared(<<<'SQL'
INSERT INTO categorias_producto (nombre, slug, orden) VALUES
('Ropa', 'ropa', 1),
('Accesorios', 'accesorios', 2),
('Suplementos', 'suplementos', 3);
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DELETE FROM `modulos_servicio` WHERE `codigo` IN ('PERFIL','ENTRENAMIENTO','NUTRICION','CONTENIDO_VIRTUAL','PROGRESO','AGENDA','PAGOS','COMUNICACION','TIENDA','SESIONES_GRUPALES');
SQL);
        DB::unprepared(<<<'SQL'
DELETE FROM `categorias_producto` WHERE `slug` IN ('ropa','accesorios','suplementos');
SQL);
        DB::unprepared(<<<'SQL'
DELETE FROM `roles` WHERE `codigo` IN ('ADMIN','COACH','CLIENTE','CLIENTE_INSTITUCIONAL');
SQL);
    }
};
