<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('plan_modulos')->delete();
        DB::table('planes')->delete();
        DB::table('configuracion_pasarelas')->delete();
        DB::table('categorias_producto')->delete();
        DB::table('modulos_servicio')->delete();
        DB::table('roles')->delete();
        DB::table('configuracion_sistema')->delete();

        DB::table('roles')->insert([
            ['codigo' => 'ADMIN', 'codigo_rol' => 'ADMIN', 'nombre' => 'Administrador', 'descripcion' => 'Gestión total del sistema StayFit', 'estado' => 'ACTIVO'],
            ['codigo' => 'COACH', 'codigo_rol' => 'COACH', 'nombre' => 'Coach', 'descripcion' => 'Acompañamiento de clientes asignados', 'estado' => 'ACTIVO'],
            ['codigo' => 'ASISTENTE_TECNICO', 'codigo_rol' => 'ASISTENTE_TECNICO', 'nombre' => 'Asistente Técnico de Coach', 'descripcion' => 'Apoyo operativo al coach: rutinas, videos, propuestas y novedades welcome', 'estado' => 'ACTIVO'],
            ['codigo' => 'CLIENTE', 'codigo_rol' => 'CLIENTE', 'nombre' => 'Cliente Individual', 'descripcion' => 'Cliente Virtual o Presencial', 'estado' => 'ACTIVO'],
            ['codigo' => 'CLIENTE_INSTITUCIONAL', 'codigo_rol' => 'CLIENTE_INSTITUCIONAL', 'nombre' => 'Cliente Institucional', 'descripcion' => 'Cliente vinculado a institución', 'estado' => 'ACTIVO'],
        ]);

        $modulos = [
            ['PERFIL', 'Perfil', 'Datos personales y de salud', 'user', 1, 'TODAS'],
            ['ENTRENAMIENTO', 'Entrenamiento', 'Rutinas y ejercicios', 'dumbbell', 2, 'TODAS'],
            ['NUTRICION', 'Nutrición', 'Plan alimentario e IA', 'apple', 3, 'TODAS'],
            ['CONTENIDO_VIRTUAL', 'Contenido Virtual', 'Videos con desbloqueo progresivo', 'video', 4, 'VIRTUAL'],
            ['PROGRESO', 'Progreso', 'Medidas, fotos y gráficas', 'chart', 5, 'TODAS'],
            ['AGENDA', 'Agenda', 'Calendario y recordatorios', 'calendar', 6, 'TODAS'],
            ['PAGOS', 'Pagos', 'Facturas y vigencia', 'credit-card', 7, 'TODAS'],
            ['COMUNICACION', 'Comunicación', 'Chat interno o WhatsApp', 'message', 8, 'TODAS'],
            ['TIENDA', 'Tienda', 'Productos FigueFit', 'shopping-bag', 9, 'PRESENCIAL'],
            ['SESIONES_GRUPALES', 'Sesiones Grupales', 'Exclusivo institucional', 'users', 10, 'TODAS'],
        ];

        foreach ($modulos as [$codigo, $nombre, $descripcion, $icono, $orden, $modalidad]) {
            DB::table('modulos_servicio')->insert([
                'codigo' => $codigo,
                'codigo_modulo' => $codigo,
                'nombre' => $nombre,
                'nombre_modulo' => $nombre,
                'descripcion' => $descripcion,
                'icono' => $icono,
                'orden' => $orden,
                'exclusivo_modalidad' => $modalidad,
                'estado' => 'ACTIVO',
            ]);
        }

        foreach ([
            ['WOMPI', 'Wompi (Bancolombia)', '/api/webhook/pasarela/wompi'],
            ['PAYU', 'PayU Latam', '/api/webhook/pasarela/payu'],
            ['MERCADOPAGO', 'MercadoPago', '/api/webhook/pasarela/mercadopago'],
            ['NEQUI', 'Nequi', '/api/webhook/pasarela/nequi'],
            ['DAVIPLATA', 'Daviplata', '/api/webhook/pasarela/daviplata'],
        ] as [$codigo, $nombre, $webhook]) {
            DB::table('configuracion_pasarelas')->insert([
                'codigo_pasarela' => $codigo,
                'nombre' => $nombre,
                'modo' => 'SANDBOX',
                'url_webhook' => $webhook,
                'activa' => 1,
            ]);
        }

        DB::table('categorias_producto')->insert([
            ['nombre' => 'Camisetas', 'slug' => 'camisetas', 'descripcion' => 'Camisetas FigueFit', 'orden' => 1, 'estado' => 'ACTIVA'],
            ['nombre' => 'Accesorios', 'slug' => 'accesorios', 'descripcion' => 'Bandas, matas y botellas', 'orden' => 2, 'estado' => 'ACTIVA'],
            ['nombre' => 'Suplementos', 'slug' => 'suplementos', 'descripcion' => 'Proteínas y vitaminas', 'orden' => 3, 'estado' => 'ACTIVA'],
            ['nombre' => 'Identificación de marca', 'slug' => 'marca', 'descripcion' => 'Gorras, termos y mochilas', 'orden' => 4, 'estado' => 'ACTIVA'],
        ]);

        DB::table('planes')->insert([
            [
                'codigo' => 'VIRTUAL-START',
                'nombre' => 'FigueFit Virtual Start',
                'slug' => 'plan-virtual-start',
                'descripcion' => 'El punto de partida para entrenar en casa: rutinas claras, videos y un coach que te acompaña por chat.',
                'caracteristicas_json' => json_encode([
                    'Rutinas 4 días por semana',
                    'Biblioteca de videos guiados',
                    'Chat con coach',
                    'Registro de peso y medidas',
                ]),
                'precio' => 89000,
                'moneda' => 'COP',
                'duracion_dias' => 30,
                'modalidad' => 'VIRTUAL',
                'tipo_cliente' => 'INDIVIDUAL',
                'cupo_maximo' => 80,
                'cupos_totales' => 80,
                'orden' => 1,
                'popular' => 0,
                'destacado' => 0,
                'etiqueta' => null,
                'estado' => 'ACTIVO',
                'estado_plan' => 'ACTIVO',
            ],
            [
                'codigo' => 'VIRTUAL-PRO',
                'nombre' => 'FigueFit Virtual Pro',
                'slug' => 'plan-virtual-pro',
                'descripcion' => 'Entrenamiento y nutrición en un solo plan. Ideal si ya entrenas y quieres resultados con seguimiento completo.',
                'caracteristicas_json' => json_encode([
                    'Plan de entrenamiento personalizado',
                    'Menú nutricional semanal',
                    'Videos con desbloqueo progresivo',
                    'Metas, fotos y evolución',
                    'Comunidad FigueFit',
                ]),
                'precio' => 159000,
                'moneda' => 'COP',
                'duracion_dias' => 30,
                'modalidad' => 'VIRTUAL',
                'tipo_cliente' => 'INDIVIDUAL',
                'cupo_maximo' => 60,
                'cupos_totales' => 60,
                'orden' => 2,
                'popular' => 1,
                'destacado' => 1,
                'etiqueta' => 'MÁS ELEGIDO',
                'estado' => 'ACTIVO',
                'estado_plan' => 'ACTIVO',
            ],
            [
                'codigo' => 'VIRTUAL-EMPRESAS',
                'nombre' => 'FigueFit Virtual Empresas',
                'slug' => 'plan-virtual-empresas',
                'descripcion' => 'Bienestar virtual para equipos de empresas o universidades: un solo plan, varios colaboradores y reporte de avance.',
                'caracteristicas_json' => json_encode([
                    'Hasta 25 colaboradores',
                    'Rutinas y retos grupales',
                    'Coach asignado al grupo',
                    'Tablero de avance institucional',
                    'Reporte mensual para RR. HH.',
                ]),
                'precio' => 2490000,
                'moneda' => 'COP',
                'duracion_dias' => 90,
                'modalidad' => 'VIRTUAL',
                'tipo_cliente' => 'INSTITUCIONAL',
                'cupo_maximo' => 25,
                'cupos_totales' => 25,
                'orden' => 3,
                'popular' => 0,
                'destacado' => 1,
                'etiqueta' => 'INSTITUCIONAL',
                'estado' => 'ACTIVO',
                'estado_plan' => 'ACTIVO',
            ],
        ]);

        $modulosPorPlan = [
            'plan-virtual-start' => ['PERFIL', 'ENTRENAMIENTO', 'CONTENIDO_VIRTUAL', 'PROGRESO', 'COMUNICACION'],
            'plan-virtual-pro' => ['PERFIL', 'ENTRENAMIENTO', 'NUTRICION', 'CONTENIDO_VIRTUAL', 'PROGRESO', 'COMUNICACION', 'PAGOS'],
            'plan-virtual-empresas' => ['PERFIL', 'ENTRENAMIENTO', 'CONTENIDO_VIRTUAL', 'PROGRESO', 'COMUNICACION'],
        ];

        foreach ($modulosPorPlan as $slug => $codigos) {
            $idPlan = DB::table('planes')->where('slug', $slug)->value('id_plan');
            foreach ($codigos as $i => $codigo) {
                $idModulo = DB::table('modulos_servicio')->where('codigo', $codigo)->value('id_modulo');
                DB::table('plan_modulos')->insert([
                    'id_plan' => $idPlan,
                    'id_modulo' => $idModulo,
                    'incluido' => 1,
                    'orden' => $i + 1,
                ]);
            }
        }

        DB::table('configuracion_sistema')->insert([
            ['clave' => 'app_nombre', 'valor' => 'StayFit', 'descripcion' => 'Nombre de la plataforma'],
            ['clave' => 'organizacion', 'valor' => 'FigueFit', 'descripcion' => 'Organización operadora'],
            ['clave' => 'moneda', 'valor' => 'COP', 'descripcion' => 'Moneda por defecto'],
            ['clave' => 'tienda_solo_presencial', 'valor' => '1', 'descripcion' => 'RF-051'],
        ]);
    }

    public function down(): void
    {
        DB::table('plan_modulos')->delete();
        DB::table('planes')->whereIn('slug', [
            'plan-virtual-esencial',
            'plan-presencial-plus',
            'plan-mixto-premium',
        ])->delete();
        DB::table('configuracion_pasarelas')->delete();
        DB::table('categorias_producto')->delete();
        DB::table('modulos_servicio')->delete();
        DB::table('roles')->delete();
        DB::table('configuracion_sistema')->delete();
    }
};
