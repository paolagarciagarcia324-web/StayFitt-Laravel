<?php

namespace Database\Seeders;

use App\Models\SolicitudCompra;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Bundle demo profesional StayFit / FigueFit.
 * ID: demodata01
 *
 * Ejecutar: php artisan demo:data
 * Idempotente: no duplica si ya está marcado demodata01 (usar --force).
 */
class DemoDataSeeder extends Seeder
{
    public const BUNDLE_ID = 'demodata01';

    public const PASSWORD_DEMO = 'demo12345';

    public const PASSWORD_ADMIN = 'Admin123!';

    private string $passwordHash;

    private string $adminPasswordHash;

    private int $adminId = 0;

    /** @var array<string,int> */
    private array $roles = [];

    /** @var array<string,int> */
    private array $modulos = [];

    /** @var list<int> */
    private array $coachIds = [];

    /** @var array<string,int> coach correo => id_user */
    private array $coachUsers = [];

    /** @var list<array{id_cliente:int,id_user:int,correo:string,modalidad:string}> */
    private array $clientes = [];

    /** @var list<int> */
    private array $planIds = [];

    /** @var list<int> */
    private array $productoIds = [];

    public function run(bool $force = false): void
    {
        $this->passwordHash = Hash::make(self::PASSWORD_DEMO);
        $this->adminPasswordHash = Hash::make(self::PASSWORD_ADMIN);

        if (! $force && $this->bundleYaCargado()) {
            $this->command?->warn('Bundle demodata01 ya está cargado. Usa: php artisan demo:data --force');

            return;
        }

        if ($force) {
            $this->command?->info('Modo force: regenerando datos demo (upsert).');
        }

        DB::transaction(function () {
            $this->cargarCatalogosBase();
            $this->seedAdmin();
            $this->seedCoaches();
            $this->seedPlanesExtra();
            $this->seedInstitucionesYHorarios();
            $this->seedResponsablesInstitucionales();
            $this->seedClientes();
            $this->seedCupones();
            $this->seedProductos();
            $this->seedVideosYPrograma();
            $this->seedPropuestas();
            $this->seedSolicitudesYPagos();
            $this->seedOrdenesTienda();
            $this->seedComunidadYMensajes();
            $this->seedWhatsapp();
            $this->seedEventos();
            $this->seedContenidoEntrenamientoNutricion();
            $this->seedNotificaciones();
            $this->seedNovedades();
            $this->marcarBundle();
        });

        $this->imprimirResumen();
    }

    private function bundleYaCargado(): bool
    {
        if (! Schema::hasTable('configuracion_sistema')) {
            return false;
        }

        return DB::table('configuracion_sistema')->where('clave', 'demo_data_id')->where('valor', self::BUNDLE_ID)->exists();
    }

    private function marcarBundle(): void
    {
        DB::table('configuracion_sistema')->updateOrInsert(
            ['clave' => 'demo_data_id'],
            [
                'valor' => self::BUNDLE_ID,
                'descripcion' => 'Bundle demo profesional StayFit',
                'actualizado_en' => now(),
            ]
        );
    }

    private function cargarCatalogosBase(): void
    {
        $this->roles = DB::table('roles')->pluck('id_rol', 'codigo')->all();
        $this->modulos = DB::table('modulos_servicio')->pluck('id_modulo', 'codigo')->all();
        $this->planIds = DB::table('planes')->orderBy('id_plan')->pluck('id_plan')->all();

        if (empty($this->roles['ADMIN'])) {
            throw new \RuntimeException('Falta rol ADMIN. Ejecuta migraciones/catálogo base primero.');
        }
    }

    private function seedAdmin(): void
    {
        // Admin principal demodata01
        $this->adminId = $this->upsertUser([
            'nombres' => 'Admin',
            'apellidos' => 'FigueFit',
            'correo' => 'admin@figuefit.com',
            'telefono' => '3000000001',
            'documento' => '1000000001',
            'rol' => 'ADMIN',
            'password' => $this->adminPasswordHash,
            'foto' => 'https://i.pravatar.cc/300?u=admin@figuefit.com',
        ]);

        // Asegurar Paola también ADMIN si existe
        $paola = DB::table('usuarios')->where('correo', 'paola@correo.com')->value('id_user');
        if ($paola) {
            $this->attachRole((int) $paola, 'ADMIN');
        }

        // Asistente técnico demo
        $this->upsertUser([
            'nombres' => 'Andrea',
            'apellidos' => 'Soporte',
            'correo' => 'asistente@demo.figuefit.test',
            'telefono' => '3000000002',
            'documento' => '1000000002',
            'rol' => 'ASISTENTE_TECNICO',
            'password' => $this->passwordHash,
        ]);
    }

    private function seedCoaches(): void
    {
        $defs = [
            ['Laura', 'Sánchez', 'laura.coach@demo.figuefit.test', 'Fuerza y funcional', 'ACTIVO', '4.80'],
            ['Diego', 'Morales', 'diego.coach@demo.figuefit.test', 'HIIT y pérdida de grasa', 'ACTIVO', '4.60'],
            ['Camila', 'Vargas', 'camila.coach@demo.figuefit.test', 'Movilidad y yoga', 'ACTIVO', '4.90'],
            ['Mateo', 'Ruiz', 'mateo.coach@demo.figuefit.test', 'Nutrición deportiva', 'ACTIVO', '4.70'],
        ];

        foreach ($defs as $i => [$n, $a, $correo, $esp, $estado, $rating]) {
            $uid = $this->upsertUser([
                'nombres' => $n,
                'apellidos' => $a,
                'correo' => $correo,
                'telefono' => '30110000'.(10 + $i),
                'documento' => (string) (1100000000 + $i),
                'rol' => 'COACH',
                'password' => $this->passwordHash,
            ]);
            $this->coachUsers[$correo] = $uid;

            $existing = DB::table('coaches')->where('id_user', $uid)->value('id_coach');
            if ($existing) {
                DB::table('coaches')->where('id_coach', $existing)->update([
                    'especialidad' => $esp,
                    'estado_coach' => $estado,
                    'calificacion_promedio' => $rating,
                    'actualizado_en' => now(),
                ]);
                $this->coachIds[] = (int) $existing;
            } else {
                $this->coachIds[] = (int) DB::table('coaches')->insertGetId([
                    'id_user' => $uid,
                    'codigo_coach' => 'COACH-DEMO-'.($i + 1),
                    'especialidad' => $esp,
                    'biografia' => "Coach FigueFit especializado en {$esp}.",
                    'certificaciones' => 'NSCA-CPT, First Aid',
                    'calificacion_promedio' => $rating,
                    'total_valoraciones' => 15 + $i * 4,
                    'estado_coach' => $estado,
                    'creado_en' => now()->subMonths(6 - $i),
                ]);
            }
        }
    }

    private function seedPlanesExtra(): void
    {
        $extras = [
            ['VIRTUAL-BASIC', 'Plan Básico Virtual', 'plan-basico-virtual', 'VIRTUAL', 69000, 30, 'Ideal para comenzar en casa con rutinas guiadas.', false, false],
            ['VIRTUAL-PREMIUM', 'Plan Premium Virtual', 'plan-premium-virtual', 'VIRTUAL', 149000, 30, 'Entrenamiento + nutrición + seguimiento semanal.', true, true],
            ['VIRTUAL-VIP', 'Plan VIP Virtual', 'plan-vip-virtual', 'VIRTUAL', 249000, 60, 'Acompañamiento 1:1 intensivo y revisión de técnica.', true, false],
            ['PRESENCIAL-VIP', 'Plan VIP Presencial', 'plan-vip-presencial', 'PRESENCIAL', 320000, 30, 'Acceso sala + coach personal + tienda con descuento.', true, true],
            ['EMPRESAS-PLUS', 'Plan Empresarial Plus', 'plan-empresarial-plus', 'VIRTUAL', 99000, 30, 'Bienestar corporativo con reportes de adherencia.', false, false],
            ['ESTUDIANTE', 'Plan Estudiante', 'plan-estudiante', 'VIRTUAL', 59000, 30, 'Tarifa especial con carné estudiantil.', false, false],
        ];

        foreach ($extras as $i => [$codigo, $nombre, $slug, $mod, $precio, $dias, $desc, $popular, $destacado]) {
            $payload = [
                'codigo' => $codigo,
                'nombre' => $nombre,
                'slug' => $slug,
                'descripcion' => $desc,
                'beneficios' => $desc,
                'inclusiones' => 'Acceso a módulos según plan · Soporte FigueFit',
                'caracteristicas_json' => json_encode([
                    'Acceso app FigueFit',
                    'Seguimiento de progreso',
                    'Comunidad / chat',
                    $mod === 'PRESENCIAL' ? 'Acceso a sala' : 'Videos on-demand',
                ]),
                'precio' => $precio,
                'moneda' => 'COP',
                'duracion_dias' => $dias,
                'modalidad' => $mod,
                'tipo_cliente' => str_contains($codigo, 'EMPRESAS') ? 'INSTITUCIONAL' : 'INDIVIDUAL',
                'cupos_totales' => 100,
                'cupos_ocupados' => 5 + $i,
                'cupo_maximo' => 100,
                'popular' => $popular ? 1 : 0,
                'destacado' => $destacado ? 1 : 0,
                'etiqueta' => $popular ? 'Popular' : null,
                'orden' => 10 + $i,
                'estado_plan' => 'ACTIVO',
                'actualizado_en' => now(),
            ];
            if (Schema::hasColumn('planes', 'estado')) {
                $payload['estado'] = 'ACTIVO';
            }

            $id = DB::table('planes')->where('slug', $slug)->orWhere('codigo', $codigo)->value('id_plan');
            if ($id) {
                DB::table('planes')->where('id_plan', $id)->update($payload);
            } else {
                $payload['creado_en'] = now();
                $id = DB::table('planes')->insertGetId($payload);
            }
            $this->planIds[] = (int) $id;

            // Vincular módulos base
            $codes = $mod === 'PRESENCIAL'
                ? ['PERFIL', 'ENTRENAMIENTO', 'NUTRICION', 'PROGRESO', 'AGENDA', 'COMUNICACION', 'TIENDA', 'PAGOS']
                : ['PERFIL', 'ENTRENAMIENTO', 'NUTRICION', 'CONTENIDO_VIRTUAL', 'PROGRESO', 'AGENDA', 'COMUNICACION', 'PAGOS'];
            foreach ($codes as $code) {
                if (! isset($this->modulos[$code])) {
                    continue;
                }
                $exists = DB::table('plan_modulos')
                    ->where('id_plan', $id)
                    ->where('id_modulo', $this->modulos[$code])
                    ->exists();
                if (! $exists) {
                    DB::table('plan_modulos')->insert([
                        'id_plan' => $id,
                        'id_modulo' => $this->modulos[$code],
                    ]);
                }
            }
        }

        $this->planIds = array_values(array_unique(array_map('intval', $this->planIds)));
    }

    private function seedInstitucionesYHorarios(): void
    {
        $planInst = DB::table('planes')
            ->where(function ($q) {
                $q->where('tipo_cliente', 'INSTITUCIONAL')->orWhere('tipo_cliente', 'AMBOS');
            })
            ->orderBy('id_plan')
            ->value('id_plan');

        foreach ([
            ['Colegio Campoalegre', '900111222-1', 'Laura Méndez', 'convenio@campoalegre.edu.co', 40, 'EDUCATIVA', 'Convenio bienestar escolar 2026'],
            ['Universidad Surcolombiana', '891180084-2', 'Carlos Rincón', 'deportes@usco.edu.co', 25, 'EDUCATIVA', 'Programa deportes universitarios'],
            ['Ara Wellness Hub', '900333444-5', 'Andrea Ruiz', 'hola@arawellness.test', 20, 'GIMNASIO', 'Wellness corporativo Ara'],
        ] as [$nombre, $nit, $contacto, $correo, $cupos, $tipo, $convenio]) {
            $id = DB::table('instituciones')->where('nit', $nit)->value('id_institucion');
            $data = [
                'nombre' => $nombre,
                'nit' => $nit,
                'contacto_nombre' => $contacto,
                'correo' => $correo,
                'telefono' => '3185550'.substr($nit, -3),
                'ciudad' => 'Neiva',
                'direccion' => 'Carrera 10 # 20-30',
                'convenio' => $convenio,
                'cupos_totales' => $cupos,
                'cupos_usados' => 0,
                'fecha_inicio' => now()->subMonths(2)->toDateString(),
                'fecha_fin' => now()->addMonths(10)->toDateString(),
                'estado' => 'ACTIVA',
                'actualizado_en' => now(),
            ];

            if (Schema::hasColumn('instituciones', 'tipo_institucion')) {
                $data['tipo_institucion'] = $tipo;
            }
            if (Schema::hasColumn('instituciones', 'contacto_cargo')) {
                $data['contacto_cargo'] = 'Coordinación bienestar';
            }
            if (Schema::hasColumn('instituciones', 'id_plan_contratado') && $planInst) {
                $data['id_plan_contratado'] = $planInst;
            }

            if ($id) {
                DB::table('instituciones')->where('id_institucion', $id)->update($data);
            } else {
                $data['creado_en'] = now();
                DB::table('instituciones')->insert($data);
            }
        }
    }

    private function seedResponsablesInstitucionales(): void
    {
        if (! Schema::hasColumn('instituciones', 'id_user_responsable')) {
            return;
        }

        if (! isset($this->roles['RESPONSABLE_INSTITUCIONAL'])) {
            $idRol = DB::table('roles')->where('codigo', 'RESPONSABLE_INSTITUCIONAL')->value('id_rol');
            if (! $idRol) {
                $idRol = DB::table('roles')->insertGetId([
                    'codigo' => 'RESPONSABLE_INSTITUCIONAL',
                    'codigo_rol' => 'RESPONSABLE_INSTITUCIONAL',
                    'nombre' => 'Responsable Institucional',
                    'descripcion' => 'Portal B2B convenios',
                    'estado' => 'ACTIVO',
                ]);
            }
            $this->roles['RESPONSABLE_INSTITUCIONAL'] = (int) $idRol;
        }

        $instId = DB::table('instituciones')->where('nit', '900111222-1')->value('id_institucion');
        if (! $instId) {
            return;
        }

        $userId = $this->upsertUser([
            'nombres' => 'Laura',
            'apellidos' => 'Méndez',
            'correo' => 'rrhh@campoalegre.demo.figuefit.test',
            'telefono' => '3185550222',
            'documento' => '52001001',
            'rol' => 'RESPONSABLE_INSTITUCIONAL',
            'password' => $this->passwordHash,
            'foto' => 'https://i.pravatar.cc/300?u=rrhh@campoalegre.demo.figuefit.test',
        ]);

        DB::table('instituciones')->where('id_institucion', $instId)->update([
            'id_user_responsable' => $userId,
            'contacto_nombre' => 'Laura Méndez',
            'contacto_cargo' => 'Coordinadora RR.HH.',
            'correo' => 'rrhh@campoalegre.demo.figuefit.test',
            'actualizado_en' => now(),
        ]);

        if (Schema::hasTable('invitaciones_institucionales')) {
            $planId = DB::table('instituciones')->where('id_institucion', $instId)->value('id_plan_contratado');
            DB::table('invitaciones_institucionales')->updateOrInsert(
                ['correo' => 'nuevo.docente@campoalegre.demo.figuefit.test', 'id_institucion' => $instId],
                [
                    'id_plan' => $planId,
                    'nombre_sugerido' => 'María Docente',
                    'token' => 'demo-invitacion-campoalegre-2026',
                    'estado' => 'PENDIENTE',
                    'fecha_expiracion' => now()->addDays(14),
                    'id_user_creador' => $userId,
                    'creado_en' => now(),
                ]
            );
        }

        if (Schema::hasTable('comunicados_institucionales')) {
            DB::table('comunicados_institucionales')->updateOrInsert(
                ['titulo' => 'Reto wellness marzo', 'id_institucion' => $instId],
                [
                    'id_user_emisor' => $userId,
                    'mensaje' => 'Iniciamos el reto de bienestar institucional. Registra tu progreso semanal.',
                    'canal' => 'IN_APP',
                    'destinatarios' => 3,
                    'creado_en' => now()->subDays(3),
                ]
            );
        }
    }

    private function seedClientes(): void
    {
        $defs = [
            ['Valentina', 'Gómez', 'valen.gomez@demo.figuefit.test', 'VIRTUAL', 'INDIVIDUAL', 'ACTIVO', 'Bajar grasa'],
            ['Andrés', 'Pérez', 'andres.perez@demo.figuefit.test', 'VIRTUAL', 'INDIVIDUAL', 'ACTIVO', 'Ganar músculo'],
            ['Sofía', 'Castro', 'sofia.castro@demo.figuefit.test', 'VIRTUAL', 'INDIVIDUAL', 'ACTIVO', 'Hábitos'],
            ['Julián', 'Rojas', 'julian.rojas@demo.figuefit.test', 'PRESENCIAL', 'INDIVIDUAL', 'ACTIVO', 'Fuerza'],
            ['Mariana', 'López', 'mariana.lopez@demo.figuefit.test', 'PRESENCIAL', 'INDIVIDUAL', 'ACTIVO', 'Tonificar'],
            ['Felipe', 'Nieto', 'felipe.nieto@demo.figuefit.test', 'PRESENCIAL', 'INDIVIDUAL', 'ACTIVO', 'Condición'],
            ['Diana', 'Suárez', 'diana.suarez@demo.figuefit.test', 'VIRTUAL', 'INSTITUCIONAL', 'ACTIVO', 'Salud laboral'],
            ['Esteban', 'Quiroga', 'esteban.quiroga@demo.figuefit.test', 'VIRTUAL', 'INSTITUCIONAL', 'ACTIVO', 'Energía'],
            ['Natalia', 'Ortiz', 'natalia.ortiz@demo.figuefit.test', 'VIRTUAL', 'INSTITUCIONAL', 'ACTIVO', 'Estrés'],
            ['Kevin', 'Mejía', 'kevin.mejia@demo.figuefit.test', 'VIRTUAL', 'INDIVIDUAL', 'SUSPENDIDO', 'Recomp'],
            ['Laura', 'Méndez', 'laura.mendez@demo.figuefit.test', 'VIRTUAL', 'INDIVIDUAL', 'ACTIVO', 'Movilidad'],
            ['Carlos', 'Ruiz', 'carlos.ruiz@demo.figuefit.test', 'VIRTUAL', 'INDIVIDUAL', 'ACTIVO', 'Resistencia'],
            ['Paula', 'Vargas', 'paula.vargas@demo.figuefit.test', 'PRESENCIAL', 'INDIVIDUAL', 'ACTIVO', 'Pérdida peso'],
            ['Santiago', 'Gil', 'santiago.gil@demo.figuefit.test', 'VIRTUAL', 'INDIVIDUAL', 'ACTIVO', 'Hipertrofia'],
            ['Isabella', 'Torres', 'isabella.torres@demo.figuefit.test', 'VIRTUAL', 'INDIVIDUAL', 'ACTIVO', 'Yoga'],
            ['Daniel', 'Castaño', 'daniel.castano@demo.figuefit.test', 'PRESENCIAL', 'INDIVIDUAL', 'ACTIVO', 'Powerlifting'],
            ['Camila', 'Hoyos', 'camila.hoyos@demo.figuefit.test', 'VIRTUAL', 'INDIVIDUAL', 'ACTIVO', 'Bienestar'],
            ['Sebastián', 'Pardo', 'sebastian.pardo@demo.figuefit.test', 'VIRTUAL', 'INDIVIDUAL', 'ACTIVO', 'Definición'],
            ['Mariana', 'Cruz', 'mariana.cruz@demo.figuefit.test', 'VIRTUAL', 'INDIVIDUAL', 'ACTIVO', 'Postparto'],
            ['Tomás', 'Álvarez', 'tomas.alvarez@demo.figuefit.test', 'PRESENCIAL', 'INDIVIDUAL', 'ACTIVO', 'Atletismo'],
            ['Elena', 'Salazar', 'elena.salazar@demo.figuefit.test', 'VIRTUAL', 'INDIVIDUAL', 'ACTIVO', 'Principiante'],
            ['Ricardo', 'Peña', 'ricardo.pena@demo.figuefit.test', 'VIRTUAL', 'INDIVIDUAL', 'ACTIVO', 'Senior fitness'],
        ];

        $horarios = DB::table('horarios_presenciales')->orderBy('id_horario')->pluck('id_horario')->all();
        $instIds = DB::table('instituciones')->orderBy('id_institucion')->pluck('id_institucion')->all();
        $planVirtual = (int) (DB::table('planes')->where('modalidad', 'VIRTUAL')->orderBy('id_plan')->value('id_plan') ?: ($this->planIds[0] ?? 1));
        $planPresencial = (int) (DB::table('planes')->where('modalidad', 'PRESENCIAL')->orderBy('id_plan')->value('id_plan') ?: $planVirtual);

        foreach ($defs as $idx => [$n, $a, $correo, $mod, $tipo, $estado, $obj]) {
            $rol = $tipo === 'INSTITUCIONAL' ? 'CLIENTE_INSTITUCIONAL' : 'CLIENTE';
            if ($rol === 'CLIENTE_INSTITUCIONAL' && empty($this->roles[$rol])) {
                $rol = 'CLIENTE';
            }

            $uid = $this->upsertUser([
                'nombres' => $n,
                'apellidos' => $a,
                'correo' => $correo,
                'telefono' => '3002000'.str_pad((string) $idx, 3, '0', STR_PAD_LEFT),
                'documento' => (string) (1200000000 + $idx),
                'rol' => $rol,
                'password' => $this->passwordHash,
            ]);

            $coachId = $this->coachIds[$idx % max(1, count($this->coachIds))] ?? null;
            $idPlan = $mod === 'PRESENCIAL' ? $planPresencial : $planVirtual;
            $idInst = $tipo === 'INSTITUCIONAL' ? ($instIds[$idx % max(1, count($instIds))] ?? null) : null;
            $idHorario = $mod === 'PRESENCIAL' ? ($horarios[$idx % max(1, count($horarios))] ?? null) : null;

            $cid = DB::table('clientes')->where('id_user', $uid)->value('id_cliente');
            $clienteData = [
                'id_user' => $uid,
                'tipo_cliente' => $tipo,
                'modalidad' => $mod,
                'tipo_documento' => 'CC',
                'documento' => (string) (1200000000 + $idx),
                'fecha_nacimiento' => now()->subYears(22 + ($idx % 20))->toDateString(),
                'genero' => $idx % 2 === 0 ? 'FEMENINO' : 'MASCULINO',
                'ciudad' => 'Neiva',
                'peso_inicial_kg' => 55 + ($idx % 20),
                'estatura_cm' => 158 + ($idx % 20),
                'objetivo_principal' => $obj,
                'id_institucion' => $idInst,
                'id_coach' => $coachId,
                'id_coach_asignado' => $coachId,
                'id_horario' => $idHorario,
                'estado_cliente' => $estado,
                'fecha_alta' => now()->subDays(90 - $idx)->toDateString(),
                'fecha_activacion' => in_array($estado, ['ACTIVO', 'ACTIVA'], true) ? now()->subDays(80 - $idx) : null,
                'actualizado_en' => now(),
            ];

            if ($cid) {
                DB::table('clientes')->where('id_cliente', $cid)->update($clienteData);
            } else {
                $clienteData['creado_en'] = now()->subDays(90 - $idx);
                $cid = DB::table('clientes')->insertGetId($clienteData);
            }

            DB::table('preferencias_cliente')->updateOrInsert(
                ['id_cliente' => $cid],
                [
                    'notif_email' => 1,
                    'notif_push' => 1,
                    'notif_whatsapp' => $mod === 'PRESENCIAL' ? 1 : 0,
                    'idioma' => 'es',
                    'actualizado_en' => now(),
                ]
            );

            if (in_array($estado, ['ACTIVO', 'ACTIVA'], true)) {
                $this->activarPlanCliente((int) $cid, $idPlan, $mod, $idx);
            }

            $this->clientes[] = [
                'id_cliente' => (int) $cid,
                'id_user' => $uid,
                'correo' => $correo,
                'modalidad' => $mod,
            ];
        }

        // Mantener usuario manual previo si existe
        $manual = DB::table('usuarios')->where('correo', 'cliente.virtual@figuefit.test')->first();
        if ($manual) {
            $this->attachRole((int) $manual->id_user, 'CLIENTE');
        }
    }

    private function activarPlanCliente(int $idCliente, int $idPlan, string $modalidad, int $idx): void
    {
        $duracion = (int) (DB::table('planes')->where('id_plan', $idPlan)->value('duracion_dias') ?: 30);
        $inicio = now()->subDays(25 - ($idx % 10));
        $fin = $inicio->copy()->addDays($duracion);

        $pcid = DB::table('planes_cliente')
            ->where('id_cliente', $idCliente)
            ->where('estado_plan_cliente', 'ACTIVO')
            ->value('id_plan_cliente');

        $pcData = [
            'id_cliente' => $idCliente,
            'id_plan' => $idPlan,
            'fecha_inicio' => $inicio->toDateString(),
            'fecha_fin' => $fin->toDateString(),
            'estado_plan_cliente' => 'ACTIVO',
            'actualizado_en' => now(),
        ];
        if (Schema::hasColumn('planes_cliente', 'estado')) {
            $pcData['estado'] = 'ACTIVO';
        }

        if ($pcid) {
            DB::table('planes_cliente')->where('id_plan_cliente', $pcid)->update($pcData);
        } else {
            $pcData['creado_en'] = $inicio;
            $pcid = DB::table('planes_cliente')->insertGetId($pcData);
        }

        $codes = $modalidad === 'PRESENCIAL'
            ? ['PERFIL', 'ENTRENAMIENTO', 'NUTRICION', 'PROGRESO', 'AGENDA', 'COMUNICACION', 'TIENDA', 'PAGOS']
            : ['PERFIL', 'ENTRENAMIENTO', 'NUTRICION', 'CONTENIDO_VIRTUAL', 'PROGRESO', 'AGENDA', 'COMUNICACION', 'PAGOS'];

        foreach ($codes as $code) {
            if (! isset($this->modulos[$code])) {
                continue;
            }
            $exists = DB::table('acceso_cliente_modulo')
                ->where('id_cliente', $idCliente)
                ->where('id_modulo', $this->modulos[$code])
                ->where('estado_acceso', 'ACTIVO')
                ->exists();
            if ($exists) {
                continue;
            }
            $row = [
                'id_cliente' => $idCliente,
                'id_modulo' => $this->modulos[$code],
                'id_plan_cliente' => $pcid,
                'estado_acceso' => 'ACTIVO',
                'fecha_inicio' => $inicio->toDateString(),
                'fecha_fin' => $fin->toDateString(),
                'creado_en' => $inicio,
            ];
            if (Schema::hasColumn('acceso_cliente_modulo', 'estado')) {
                $row['estado'] = 'ACTIVO';
            }
            DB::table('acceso_cliente_modulo')->insert($row);
        }
    }

    private function seedCupones(): void
    {
        $planVirtual = DB::table('planes')->where('modalidad', 'VIRTUAL')->value('id_plan');
        $planPresencial = DB::table('planes')->where('modalidad', 'PRESENCIAL')->value('id_plan');

        $cupones = [
            ['FIGUEFIT10', 'PORCENTAJE', 10, $planVirtual],
            ['PREMIUM20', 'PORCENTAJE', 20, null],
            ['BIENVENIDA15', 'PORCENTAJE', 15, null],
            ['NEIVA25', 'MONTO', 25000, null],
            ['SALA15', 'PORCENTAJE', 15, $planPresencial],
            ['VERANO30', 'PORCENTAJE', 30, null],
            ['AMIGO10', 'PORCENTAJE', 10, null],
            ['EMPRESA12', 'PORCENTAJE', 12, null],
            ['FLASH50K', 'MONTO', 50000, $planVirtual],
            ['RETO21', 'PORCENTAJE', 21, null],
        ];

        foreach ($cupones as $i => [$codigo, $tipo, $valor, $idPlan]) {
            $payload = [
                'codigo' => $codigo,
                'tipo' => $tipo,
                'tipo_descuento' => $tipo,
                'valor' => $valor,
                'id_plan' => $idPlan,
                'fecha_inicio' => now()->subDays(10),
                'fecha_fin' => now()->addMonths(2),
                'usos_maximos' => 100,
                'uso_maximo' => 100,
                'usos_actuales' => 3 + $i,
                'estado' => 'ACTIVO',
                'estado_cupon' => 'ACTIVO',
                'creado_por' => $this->adminId ?: null,
                'actualizado_en' => now(),
            ];
            $id = DB::table('descuentos_cupones')->where('codigo', $codigo)->value('id_descuento');
            if ($id) {
                DB::table('descuentos_cupones')->where('id_descuento', $id)->update($payload);
            } else {
                $payload['creado_en'] = now()->subDays(10 - $i);
                DB::table('descuentos_cupones')->insert($payload);
            }
        }
    }

    private function seedProductos(): void
    {
        $cats = [
            ['Suplementos', 'suplementos'],
            ['Accesorios', 'accesorios'],
            ['Ropa deportiva', 'ropa'],
            ['Equipamiento', 'equipamiento'],
            ['Hidratación', 'hidratacion'],
        ];
        $catIds = [];
        foreach ($cats as $i => [$nombre, $slug]) {
            $id = DB::table('categorias_producto')->where('slug', $slug)->orWhere('nombre', $nombre)->value('id_categoria_producto');
            if (! $id) {
                $id = DB::table('categorias_producto')->insertGetId([
                    'nombre' => $nombre,
                    'slug' => $slug,
                    'descripcion' => "Categoría {$nombre}",
                    'orden' => $i + 1,
                    'estado' => 'ACTIVA',
                    'creado_en' => now(),
                ]);
            }
            $catIds[$slug] = (int) $id;
        }

        $productos = [
            ['FF-PRO-01', 'Proteína whey 1kg', 'suplementos', 129000, 40, 'https://images.unsplash.com/photo-1593095948071-474c5cc2989d?w=600&q=80'],
            ['FF-PRO-02', 'Creatina monohidrato 300g', 'suplementos', 79000, 55, 'https://images.unsplash.com/photo-1579722820308-d74e571900a9?w=600&q=80'],
            ['FF-PRO-03', 'BCAA 2:1:1', 'suplementos', 69000, 35, 'https://images.unsplash.com/photo-1579722821273-0f6c7d44377f?w=600&q=80'],
            ['FF-PRO-04', 'Pre-workout FigueFit', 'suplementos', 89000, 28, 'https://images.unsplash.com/photo-1594381898411-846e7d193883?w=600&q=80'],
            ['FF-PRO-05', 'Colágeno hidrolizado', 'suplementos', 99000, 22, 'https://images.unsplash.com/photo-1550572017-edd951aa8f72?w=600&q=80'],
            ['FF-PRO-06', 'Omega 3 90 caps', 'suplementos', 59000, 48, 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=600&q=80'],
            ['FF-ACC-01', 'Botella térmica 750ml', 'hidratacion', 45000, 60, 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=600&q=80'],
            ['FF-ACC-02', 'Shaker Pro 600ml', 'hidratacion', 35000, 70, 'https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?w=600&q=80'],
            ['FF-ACC-03', 'Bandas elásticas set x5', 'accesorios', 55000, 40, 'https://images.unsplash.com/photo-1598289431512-b97b0917affc?w=600&q=80'],
            ['FF-ACC-04', 'Cuerda de salto pro', 'accesorios', 42000, 33, 'https://images.unsplash.com/photo-1518611012118-696072aa579a?w=600&q=80'],
            ['FF-ACC-05', 'Guantes de gym', 'accesorios', 48000, 29, 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?w=600&q=80'],
            ['FF-ACC-06', 'Rodilleras neopreno', 'accesorios', 52000, 24, 'https://images.unsplash.com/photo-1571902943202-507ec2618e8f?w=600&q=80'],
            ['FF-ACC-07', 'Foam roller', 'accesorios', 65000, 18, 'https://images.unsplash.com/photo-1599058945522-28d584b6f14f?w=600&q=80'],
            ['FF-ACC-08', 'Mat yoga premium', 'equipamiento', 89000, 20, 'https://images.unsplash.com/photo-1601925260368-ae2f83cf8b7f?w=600&q=80'],
            ['FF-EQ-01', 'Mancuernas 5kg par', 'equipamiento', 110000, 15, 'https://images.unsplash.com/photo-1517963879433-6ad2b056d944?w=600&q=80'],
            ['FF-EQ-02', 'Kettlebell 12kg', 'equipamiento', 135000, 12, 'https://images.unsplash.com/photo-1599058917212-d750089bc07e?w=600&q=80'],
            ['FF-EQ-03', 'TRX suspension', 'equipamiento', 189000, 10, 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?w=600&q=80'],
            ['FF-EQ-04', 'Ab wheel', 'equipamiento', 39000, 26, 'https://images.unsplash.com/photo-1576678927484-e369b9c1c9a2?w=600&q=80'],
            ['FF-ROP-01', 'Camiseta FigueFit Rosa', 'ropa', 65000, 45, 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=600&q=80'],
            ['FF-ROP-02', 'Leggins performance', 'ropa', 98000, 32, 'https://images.unsplash.com/photo-1506629082955-511b1aa16263?w=600&q=80'],
            ['FF-ROP-03', 'Hoodie StayFit', 'ropa', 120000, 21, 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=600&q=80'],
            ['FF-ROP-04', 'Gorra StayFit', 'ropa', 39000, 50, 'https://images.unsplash.com/photo-1588850561407-ed78c282e89b?w=600&q=80'],
            ['FF-ROP-05', 'Shorts entrenamiento', 'ropa', 72000, 38, 'https://images.unsplash.com/photo-1591195853828-11db59a44f6b?w=600&q=80'],
            ['FF-ROP-06', 'Top deportivo mujer', 'ropa', 69000, 34, 'https://images.unsplash.com/photo-1518310383802-640c2de311b2?w=600&q=80'],
            ['FF-SVC-01', 'Sesión valoración corporal', 'accesorios', 45000, 99, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=600&q=80'],
            ['FF-SVC-02', 'Kit bienvenida presencial', 'accesorios', 35000, 40, 'https://images.unsplash.com/photo-1518611012118-696072aa579a?w=600&q=80'],
            ['FF-HID-01', 'Electrolitos pack x12', 'hidratacion', 56000, 44, 'https://images.unsplash.com/photo-1622597467836-f3285f2131b8?w=600&q=80'],
            ['FF-HID-02', 'Térmica steel 1L', 'hidratacion', 78000, 27, 'https://images.unsplash.com/photo-1523362628745-0c100150b504?w=600&q=80'],
            ['FF-EQ-05', 'Banda de resistencia XXL', 'equipamiento', 47000, 31, 'https://images.unsplash.com/photo-1434682881908-b43d0467b798?w=600&q=80'],
            ['FF-PRO-07', 'Multivitamínico FigueFit', 'suplementos', 64000, 36, 'https://images.unsplash.com/photo-1550572017-edd951aa8f72?w=600&q=80'],
        ];

        foreach ($productos as $i => [$sku, $nombre, $catSlug, $precio, $stock, $img]) {
            $payload = [
                'id_categoria_producto' => $catIds[$catSlug],
                'sku' => $sku,
                'nombre' => $nombre,
                'descripcion' => "Producto oficial FigueFit · {$nombre}. Ideal para acompañar tu plan.",
                'precio' => $precio,
                'imagen_url' => $img,
                'stock_total' => $stock,
                'stock_minimo' => 5,
                'exclusivo_presencial' => 1,
                'estado' => 'ACTIVO',
                'actualizado_en' => now(),
            ];
            if (Schema::hasColumn('productos_tienda', 'calificacion')) {
                $payload['calificacion'] = round(4.1 + (($i % 9) * 0.1), 1);
            }

            $id = DB::table('productos_tienda')->where('sku', $sku)->value('id_producto');
            if ($id) {
                DB::table('productos_tienda')->where('id_producto', $id)->update($payload);
            } else {
                $payload['creado_en'] = now()->subDays(40 - $i);
                $id = DB::table('productos_tienda')->insertGetId($payload);
            }
            $this->productoIds[] = (int) $id;
        }
    }

    private function seedVideosYPrograma(): void
    {
        $programaId = DB::table('programas_virtuales')->where('nombre', 'Programa Virtual FigueFit Demo')->value('id_programa');
        if (! $programaId) {
            $programaId = DB::table('programas_virtuales')->insertGetId([
                'nombre' => 'Programa Virtual FigueFit Demo',
                'descripcion' => 'Biblioteca de videos para clientes virtuales.',
                'estado' => 'ACTIVO',
                'creado_en' => now(),
            ]);
        }

        $cats = [
            ['Fundamentos', 1],
            ['Fuerza', 2],
            ['Cardio', 3],
            ['Movilidad', 4],
            ['Educativos', 5],
        ];
        $catMap = [];
        foreach ($cats as [$nombre, $orden]) {
            $id = DB::table('categorias_video')->where('id_programa', $programaId)->where('nombre', $nombre)->value('id_categoria_video');
            if (! $id) {
                $id = DB::table('categorias_video')->insertGetId([
                    'id_programa' => $programaId,
                    'nombre' => $nombre,
                    'orden' => $orden,
                    'creado_en' => now(),
                ]);
            }
            $catMap[$nombre] = (int) $id;
        }

        $videos = [
            ['Fundamentos', 1, 'Activación y movilidad', 'Calentamiento guiado 10 min', 'https://www.youtube.com/watch?v=ml6cT4AZdqI', 600],
            ['Fundamentos', 2, 'Respiración y core', 'Control postural básico', 'https://www.youtube.com/watch?v=DHD1-2P94DI', 540],
            ['Fundamentos', 3, 'Técnica de sentadilla', 'Tutorial de patrón de empujón', 'https://www.youtube.com/watch?v=aclHkVaku9U', 720],
            ['Fuerza', 1, 'Full body A', 'Circuito fuerza técnica', 'https://www.youtube.com/watch?v=UoC_O3HzsH0', 1800],
            ['Fuerza', 2, 'Tren superior', 'Press, remo y core', 'https://www.youtube.com/watch?v=IODxDxX7oi4', 1500],
            ['Fuerza', 3, 'Tren inferior', 'Sentadilla, peso muerto rumano', 'https://www.youtube.com/watch?v=yaNfFf3G1nY', 1600],
            ['Fuerza', 4, 'Glúteos y posteriores', 'Enfoque hipertrofia', 'https://www.youtube.com/watch?v=2C-uNgKwPLE', 1400],
            ['Cardio', 1, 'HIIT 20 minutos', 'Intervalos casa sin equipo', 'https://www.youtube.com/watch?v=mlY6OepyIns', 1200],
            ['Cardio', 2, 'Cardio low impact', 'Opción articular amigable', 'https://www.youtube.com/watch?v=gC_L9qAXLEg', 1100],
            ['Cardio', 3, 'Tabata express', 'Sesión corta intensa', 'https://www.youtube.com/watch?v=VHyGqsPOUHs', 900],
            ['Movilidad', 1, 'Movilidad de cadera', 'Flujo diario 12 min', 'https://www.youtube.com/watch?v=4BOTtv2xhOY', 720],
            ['Movilidad', 2, 'Yoga recovery', 'Recuperación post entreno', 'https://www.youtube.com/watch?v=v7AYKMP6rOE', 1500],
            ['Educativos', 1, 'Cómo leer tu plan', 'Onboarding FigueFit', 'https://www.youtube.com/watch?v=2Vv-BfVoq4g', 480],
            ['Educativos', 2, 'Hábitos de hidratación', 'Educación nutricional', 'https://www.youtube.com/watch?v=9iMGFqMm2H0', 600],
            ['Educativos', 3, 'Testimonio Valentina', 'Caso de éxito demo', 'https://www.youtube.com/watch?v=jNQXAC9IVRw', 360],
        ];

        foreach ($videos as [$cat, $dia, $titulo, $desc, $url, $dur]) {
            $catId = $catMap[$cat];
            $exists = DB::table('videos')->where('id_categoria_video', $catId)->where('dia_orden', $dia)->exists();
            if ($exists) {
                DB::table('videos')->where('id_categoria_video', $catId)->where('dia_orden', $dia)->update([
                    'titulo' => $titulo,
                    'descripcion' => $desc,
                    'url_video' => $url,
                    'duracion_segundos' => $dur,
                    'estado' => 'ACTIVO',
                ]);
            } else {
                DB::table('videos')->insert([
                    'id_categoria_video' => $catId,
                    'dia_orden' => $dia,
                    'titulo' => $titulo,
                    'descripcion' => $desc,
                    'url_video' => $url,
                    'duracion_segundos' => $dur,
                    'requiere_ia' => false,
                    'estado' => 'ACTIVO',
                    'creado_en' => now(),
                ]);
            }
        }
    }


    private function seedPropuestas(): void
    {
        $estados = [
            'BORRADOR_ENTRENAMIENTO',
            'BORRADOR_ENTRENAMIENTO',
            'BORRADOR_ENTRENAMIENTO',
            'BORRADOR_NUTRICION',
            'BORRADOR_NUTRICION',
            'PENDIENTE_REVISION',
            'PENDIENTE_REVISION',
            'PENDIENTE_REVISION',
            'APROBADO',
            'APROBADO',
            'RECHAZADO',
            'RECHAZADO',
            'PUBLICADO',
            'PUBLICADO',
            'PUBLICADO',
        ];

        $titulos = [
            'Recomposición 4 semanas',
            'HIIT principiantes',
            'Fuerza tren superior',
            'Movilidad diaria',
            'Definición verano',
            'Postparto activo',
            'Senior balance',
            'Atletas resistencia',
            'Yoga flow energía',
            'Powerlifting base',
            'Cardio bajo impacto',
            'Glúteos y core',
            'Plan institucional wellness',
            'Reto 21 días',
            'VIP acompañamiento 1:1',
        ];

        $planVirtual = (int) (DB::table('planes')->where('modalidad', 'VIRTUAL')->orderBy('id_plan')->value('id_plan') ?: ($this->planIds[0] ?? 1));

        foreach ($titulos as $i => $titulo) {
            $coachId = $this->coachIds[$i % max(1, count($this->coachIds))];
            $estado = $estados[$i];

            if (DB::table('propuestas_plan')->where('id_coach', $coachId)->where('titulo_interno', $titulo)->exists()) {
                continue;
            }

            $coachUserId = (int) DB::table('coaches')->where('id_coach', $coachId)->value('id_user');
            $now = now()->subDays(20 - $i);

            $programaId = DB::table('programas_virtuales')->insertGetId([
                'nombre' => "Programa demo · {$titulo}",
                'descripcion' => "Contenido virtual propuesto por coach para {$titulo}.",
                'id_plan' => $planVirtual,
                'estado' => 'ACTIVO',
                'creado_en' => $now,
            ]);

            $catId = DB::table('categorias_video')->insertGetId([
                'id_programa' => $programaId,
                'nombre' => 'Semana 1',
                'orden' => 1,
                'creado_en' => $now,
            ]);

            DB::table('videos')->insert([
                'id_categoria_video' => $catId,
                'dia_orden' => 1,
                'titulo' => "Sesión intro · {$titulo}",
                'descripcion' => 'Video principal de la propuesta demo.',
                'url_video' => 'https://www.youtube.com/watch?v=ml6cT4AZdqI',
                'duracion_segundos' => 600 + ($i * 30),
                'requiere_ia' => $i % 3 === 0,
                'estado' => 'ACTIVO',
                'creado_en' => $now,
            ]);

            $planNutId = null;
            $incluyeNutricion = $estado !== 'BORRADOR_ENTRENAMIENTO';

            if ($incluyeNutricion) {
                $planNutId = DB::table('planes_nutricionales')->insertGetId([
                    'id_coach' => $coachId,
                    'nombre' => "Nutrición · {$titulo}",
                    'objetivo' => 'Balance energético y adherencia',
                    'kcal_objetivo' => 1700 + ($i * 25),
                    'proteinas_g' => 110 + ($i % 5) * 5,
                    'carbohidratos_g' => 160 + ($i % 4) * 10,
                    'grasas_g' => 50 + ($i % 3) * 3,
                    'recomendaciones' => 'Hidratación 2.5 L · proteína en cada comida.',
                    'estado' => in_array($estado, ['PUBLICADO', 'APROBADO'], true) ? 'ACTIVO' : 'BORRADOR',
                    'creado_en' => $now,
                ]);

                foreach ([
                    ['DESAYUNO', 'Bowl energético', 'Avena + fruta + proteína', 1],
                    ['ALMUERZO', 'Plato balanceado', 'Proteína magra + carbohidrato + vegetales', 2],
                    ['CENA', 'Cena ligera', 'Opción alta en fibra y proteína', 3],
                ] as [$tipo, $nombre, $receta, $orden]) {
                    DB::table('comidas')->insert([
                        'id_plan_nutricional' => $planNutId,
                        'tipo_comida' => $tipo,
                        'nombre' => $nombre,
                        'receta' => $receta,
                        'porcion' => '1 ración',
                        'calorias' => 420 + ($orden * 30),
                        'proteinas_g' => 28,
                        'carbohidratos_g' => 35,
                        'grasas_g' => 12,
                        'orden' => $orden,
                        'creado_en' => $now,
                    ]);
                }
            }

            $propuestaData = [
                'id_coach' => $coachId,
                'creado_por' => $coachUserId ?: null,
                'id_programa' => $programaId,
                'id_plan_nutricional' => $planNutId,
                'id_plan_publicado' => $estado === 'PUBLICADO' ? $planVirtual : null,
                'titulo_interno' => $titulo,
                'descripcion_interna' => "Propuesta demo FigueFit para {$titulo}.",
                'estado' => $estado,
                'notas_coach' => 'Generado por bundle demodata01.',
                'notas_admin' => in_array($estado, ['APROBADO', 'PUBLICADO'], true) ? 'Aprobado demo' : null,
                'motivo_rechazo' => $estado === 'RECHAZADO' ? 'Ajustar volumen de entrenamiento semana 2.' : null,
                'enviado_en' => in_array($estado, [
                    'PENDIENTE_REVISION',
                    'APROBADO',
                    'RECHAZADO',
                    'PUBLICADO',
                ], true) ? $now->copy()->subDays(5) : null,
                'revisado_en' => in_array($estado, [
                    'APROBADO',
                    'RECHAZADO',
                    'PUBLICADO',
                ], true) ? $now->copy()->subDays(2) : null,
                'publicado_en' => $estado === 'PUBLICADO' ? $now : null,
                'creado_en' => $now,
                'actualizado_en' => now(),
            ];

            $propuestaId = DB::table('propuestas_plan')->insertGetId($propuestaData);

            DB::table('programas_virtuales')->where('id_programa', $programaId)->update(['id_propuesta' => $propuestaId]);
            if ($planNutId) {
                DB::table('planes_nutricionales')->where('id_plan_nutricional', $planNutId)->update(['id_propuesta' => $propuestaId]);
            }
        }
    }

    private function seedSolicitudesYPagos(): void
    {
        $planes = DB::table('planes')->get()->keyBy('id_plan');
        $pasarelas = ['BOLD', 'EPAYCO', 'MERCADOPAGO'];
        $metodos = ['TARJETA', 'PSE', 'TARJETA'];
        $fitSeq = max(200, (int) DB::table('solicitudes_compra')
            ->where('codigo_solicitud', 'like', 'Fit%')
            ->pluck('codigo_solicitud')
            ->map(function (string $codigo) {
                return preg_match('/^Fit(\d+)$/i', $codigo, $m) ? (int) $m[1] : 0;
            })
            ->max() + 1);

        foreach ($this->clientes as $idx => $cliente) {
            $cid = $cliente['id_cliente'];
            $uid = $cliente['id_user'];

            if (DB::table('solicitudes_compra')->where('id_cliente', $cid)->exists()) {
                continue;
            }

            $usuario = DB::table('usuarios')->where('id_user', $uid)->first();
            $clienteRow = DB::table('clientes')->where('id_cliente', $cid)->first();
            if (! $usuario || ! $clienteRow) {
                continue;
            }

            $idPlan = (int) (DB::table('planes_cliente')
                ->where('id_cliente', $cid)
                ->where('estado_plan_cliente', 'ACTIVO')
                ->value('id_plan')
                ?: DB::table('planes')->where('modalidad', $cliente['modalidad'])->value('id_plan')
                ?: ($this->planIds[0] ?? 1));

            $precio = (float) ($planes[$idPlan]->precio ?? 89000);
            $descuento = $idx % 5 === 0 ? 10000 : 0;
            $total = $precio - $descuento;
            $estadoCliente = strtoupper((string) ($clienteRow->estado_cliente ?? 'ACTIVO'));
            $estadoSol = $estadoCliente === 'SUSPENDIDO' ? 'RECHAZADA' : ($idx % 11 === 0 ? 'PENDIENTE' : 'VALIDADA');
            $fechaSol = now()->subDays(60 - ($idx % 30));
            $fechaRev = $estadoSol !== 'PENDIENTE' ? $fechaSol->copy()->addDays(2) : null;

            do {
                $codigo = 'Fit'.str_pad((string) $fitSeq, 3, '0', STR_PAD_LEFT);
                $fitSeq++;
            } while (DB::table('solicitudes_compra')->where('codigo_solicitud', $codigo)->exists());

            $sid = DB::table('solicitudes_compra')->insertGetId([
                'codigo_solicitud' => $codigo,
                'id_plan' => $idPlan,
                'id_user' => $uid,
                'id_cliente' => $cid,
                'id_institucion' => $clienteRow->id_institucion,
                'origen' => 'PASARELA',
                'modalidad' => $cliente['modalidad'],
                'tipo_cliente' => $clienteRow->tipo_cliente ?? 'INDIVIDUAL',
                'nombres' => $usuario->nombres,
                'apellidos' => $usuario->apellidos,
                'tipo_documento' => 'CC',
                'documento' => $clienteRow->documento ?? $usuario->documento_identidad,
                'documento_identidad' => $clienteRow->documento ?? $usuario->documento_identidad,
                'correo' => $usuario->correo,
                'telefono' => $usuario->telefono,
                'ciudad' => $clienteRow->ciudad ?? 'Neiva',
                'precio_lista' => $precio,
                'descuento' => $descuento,
                'total' => $total,
                'acepto_terminos' => 1,
                'fecha_aceptacion_terminos' => $fechaSol->copy()->subDay(),
                'estado_solicitud' => $estadoSol,
                'reserva_cupo' => 1,
                'observacion_admin' => $estadoSol === 'VALIDADA' ? 'Aprobado demo demodata01' : null,
                'revisado_por' => $estadoSol !== 'PENDIENTE' ? ($this->adminId ?: null) : null,
                'revisada_en' => $fechaRev,
                'fecha_revision' => $fechaRev,
                'creado_en' => $fechaSol,
            ]);

            $pagoEstado = $estadoSol === 'RECHAZADA' ? 'RECHAZADO' : ($estadoSol === 'PENDIENTE' ? 'PENDIENTE' : 'VALIDADO');
            $pasarela = $pasarelas[$idx % 3];
            $metodo = $metodos[$idx % 3];
            $fechaPago = $fechaSol->copy()->addDay();

            $pid = DB::table('pagos')->insertGetId([
                'id_solicitud' => $sid,
                'id_cliente' => $cid,
                'id_plan' => $idPlan,
                'tipo_pago' => 'PLAN',
                'origen_pago' => 'SOLICITUD_PLAN',
                'monto' => $total,
                'moneda' => 'COP',
                'metodo' => $metodo,
                'metodo_pago' => $metodo,
                'pasarela' => $pasarela,
                'referencia' => 'REF-'.$codigo,
                'referencia_pago' => 'PAY-'.$codigo,
                'estado_pago' => $pagoEstado,
                'fecha_pago' => $fechaPago,
                'fecha_validacion' => $pagoEstado === 'VALIDADO' ? $fechaPago->copy()->addDay() : null,
                'validado_por' => $pagoEstado === 'VALIDADO' ? ($this->adminId ?: null) : null,
                'creado_en' => $fechaPago,
            ]);

            $pcid = DB::table('planes_cliente')
                ->where('id_cliente', $cid)
                ->where('estado_plan_cliente', 'ACTIVO')
                ->value('id_plan_cliente');

            if ($pcid) {
                DB::table('pagos')->where('id_pago', $pid)->update(['id_plan_cliente' => $pcid]);
                DB::table('planes_cliente')->where('id_plan_cliente', $pcid)->update([
                    'id_solicitud' => $sid,
                    'id_pago' => $pid,
                ]);
            }
        }

        $planVirtual = (int) (DB::table('planes')->where('modalidad', 'VIRTUAL')->value('id_plan') ?: ($this->planIds[0] ?? 1));
        $montos = [59000, 69000, 89000, 99000, 129000, 149000, 159000, 189000, 199000, 249000];
        $historicosCreados = 0;

        for ($h = 0; $h < 25; $h++) {
            $ref = 'REF-HIST-'.str_pad((string) (200 + $h), 3, '0', STR_PAD_LEFT);
            if (DB::table('pagos')->where('referencia', $ref)->exists()) {
                continue;
            }

            $cliente = $this->clientes[$h % max(1, count($this->clientes))];
            $fechaPago = now()->subDays(90 - ($h * 3))->subHours($h);
            $monto = $montos[$h % count($montos)];

            DB::table('pagos')->insert([
                'id_cliente' => $cliente['id_cliente'],
                'id_plan' => $planVirtual,
                'tipo_pago' => 'PLAN',
                'origen_pago' => 'SOLICITUD_PLAN',
                'monto' => $monto,
                'moneda' => 'COP',
                'metodo' => $h % 2 === 0 ? 'TARJETA' : 'PSE',
                'metodo_pago' => $h % 2 === 0 ? 'TARJETA' : 'PSE',
                'pasarela' => $pasarelas[$h % 3],
                'referencia' => $ref,
                'referencia_pago' => 'PAY-HIST-'.str_pad((string) (200 + $h), 3, '0', STR_PAD_LEFT),
                'estado_pago' => $h % 9 === 0 ? 'PENDIENTE' : 'VALIDADO',
                'fecha_pago' => $fechaPago,
                'fecha_validacion' => $h % 9 === 0 ? null : $fechaPago->copy()->addHours(6),
                'validado_por' => $h % 9 === 0 ? null : ($this->adminId ?: null),
                'creado_en' => $fechaPago,
            ]);
            $historicosCreados++;
        }
    }

    private function seedOrdenesTienda(): void
    {
        $presenciales = array_values(array_filter($this->clientes, fn (array $c) => $c['modalidad'] === 'PRESENCIAL'));
        if ($presenciales === [] || $this->productoIds === []) {
            return;
        }

        $estados = ['ENTREGADA', 'DESPACHADA', 'PREPARANDO', 'PAGADA', 'PENDIENTE_PAGO'];

        for ($o = 0; $o < 20; $o++) {
            $numero = 'ORD-DEMO-'.str_pad((string) (100 + $o), 3, '0', STR_PAD_LEFT);
            if (DB::table('ordenes_tienda')->where('numero_orden', $numero)->exists()) {
                continue;
            }

            $cliente = $presenciales[$o % count($presenciales)];
            $prodCount = 1 + ($o % 3);
            $subtotal = 0.0;
            $detalles = [];

            for ($d = 0; $d < $prodCount; $d++) {
                $prodId = $this->productoIds[($o + $d) % count($this->productoIds)];
                $prod = DB::table('productos_tienda')->where('id_producto', $prodId)->first();
                if (! $prod) {
                    continue;
                }
                $cantidad = 1 + ($d % 2);
                $precio = (float) $prod->precio;
                $lineSub = $precio * $cantidad;
                $subtotal += $lineSub;
                $detalles[] = [$prodId, $cantidad, $precio, $lineSub];
            }

            if ($detalles === []) {
                continue;
            }

            $envio = $o % 4 === 0 ? 8000 : 0;
            $total = $subtotal + $envio;
            $estado = $estados[$o % count($estados)];
            $creado = now()->subDays(25 - $o);

            $ordenId = DB::table('ordenes_tienda')->insertGetId([
                'numero_orden' => $numero,
                'id_cliente' => $cliente['id_cliente'],
                'subtotal' => $subtotal,
                'costo_envio' => $envio,
                'total' => $total,
                'tipo_entrega' => $o % 2 === 0 ? 'RETIRO_SALA' : 'DOMICILIO',
                'direccion_entrega' => $o % 2 === 0 ? null : 'Cra 8 # 15-20, Neiva',
                'ciudad_entrega' => 'Neiva',
                'estado_orden' => $estado,
                'observacion_admin' => $estado === 'ENTREGADA' ? 'Entrega demo completada' : null,
                'creado_en' => $creado,
            ]);

            foreach ($detalles as [$prodId, $cantidad, $precio, $lineSub]) {
                $exists = DB::table('orden_detalle')
                    ->where('id_orden', $ordenId)
                    ->where('id_producto', $prodId)
                    ->exists();
                if ($exists) {
                    continue;
                }
                DB::table('orden_detalle')->insert([
                    'id_orden' => $ordenId,
                    'id_producto' => $prodId,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precio,
                    'subtotal' => $lineSub,
                ]);
            }
        }
    }

    private function seedComunidadYMensajes(): void
    {
        $dialogos = [
            ['Hola, ¿cómo te fue con la rutina de hoy?', 'Muy bien coach, terminé todos los bloques 💪'],
            ['Recuerda registrar tu peso esta semana.', 'Listo, ya lo subí en la app.'],
            ['¿Tuviste alguna molestia en la rodilla?', 'Un poco al final, pero nada grave.'],
            ['Te dejé ajustada la carga del día 3.', 'Perfecto, lo reviso antes de entrenar.'],
            ['¡Excelente adherencia esta semana!', 'Gracias, me siento con más energía.'],
        ];

        $clientesChat = array_slice($this->clientes, 0, min(12, count($this->clientes)));
        $planVirtual = (int) (DB::table('planes')->where('modalidad', 'VIRTUAL')->value('id_plan') ?: ($this->planIds[0] ?? 1));

        foreach ($clientesChat as $idx => $cliente) {
            $clienteRow = DB::table('clientes')->where('id_cliente', $cliente['id_cliente'])->first();
            $coachId = $clienteRow->id_coach ?? $clienteRow->id_coach_asignado ?? ($this->coachIds[$idx % max(1, count($this->coachIds))] ?? null);
            if (! $coachId) {
                continue;
            }

            $coachUserId = (int) DB::table('coaches')->where('id_coach', $coachId)->value('id_user');
            $clienteUserId = $cliente['id_user'];
            $coachNombre = DB::table('usuarios')->where('id_user', $coachUserId)->value('nombres') ?: 'Coach';
            $clienteNombre = DB::table('usuarios')->where('id_user', $clienteUserId)->value('nombres') ?: 'Cliente';
            $nombreChat = "{$coachNombre} · {$clienteNombre}";

            $chatId = DB::table('chats')
                ->where('tipo', 'COACH_CLIENTE')
                ->where('id_coach', $coachId)
                ->where('nombre', $nombreChat)
                ->value('id_chat');

            if (! $chatId) {
                $chatId = DB::table('chats')->insertGetId([
                    'tipo' => 'COACH_CLIENTE',
                    'id_plan' => $planVirtual,
                    'id_coach' => $coachId,
                    'nombre' => $nombreChat,
                    'estado' => 'ACTIVO',
                    'creado_en' => now()->subDays(10 - ($idx % 5)),
                ]);

                foreach ([[$coachUserId, 'COACH'], [$clienteUserId, 'CLIENTE']] as [$uid, $rol]) {
                    if (! $uid) {
                        continue;
                    }
                    DB::table('chat_participantes')->updateOrInsert(
                        ['id_chat' => $chatId, 'id_user' => $uid],
                        ['rol_en_chat' => $rol, 'unido_en' => now()->subDays(10 - ($idx % 5))]
                    );
                }
            }

            if (DB::table('mensajes')->where('id_chat', $chatId)->count() >= 10) {
                continue;
            }

            $base = now()->subHours(12 + $idx);
            $msgIdx = 0;
            foreach ($dialogos as $par) {
                foreach ([[$coachUserId, $par[0]], [$clienteUserId, $par[1]]] as [$emisor, $texto]) {
                    if (! $emisor) {
                        continue;
                    }
                    $enviado = $base->copy()->addMinutes($msgIdx * 8);
                    DB::table('mensajes')->insert([
                        'id_chat' => $chatId,
                        'id_user_emisor' => $emisor,
                        'contenido' => $texto,
                        'tipo_mensaje' => 'TEXTO',
                        'leido' => $msgIdx < 8,
                        'enviado_en' => $enviado,
                        'creado_en' => $enviado,
                    ]);
                    $msgIdx++;
                }
            }
        }

        $grupoNombre = 'Comunidad FigueFit Virtual';
        $grupoId = DB::table('chats')->where('tipo', 'GRUPO_PLAN')->where('nombre', $grupoNombre)->value('id_chat');
        $coachGrupo = $this->coachIds[0] ?? null;

        if (! $grupoId && $coachGrupo) {
            $grupoId = DB::table('chats')->insertGetId([
                'tipo' => 'GRUPO_PLAN',
                'id_plan' => $planVirtual,
                'id_coach' => $coachGrupo,
                'nombre' => $grupoNombre,
                'estado' => 'ACTIVO',
                'creado_en' => now()->subDays(15),
            ]);

            $coachUserGrupo = (int) DB::table('coaches')->where('id_coach', $coachGrupo)->value('id_user');
            if ($coachUserGrupo) {
                DB::table('chat_participantes')->updateOrInsert(
                    ['id_chat' => $grupoId, 'id_user' => $coachUserGrupo],
                    ['rol_en_chat' => 'COACH', 'unido_en' => now()->subDays(15)]
                );
            }

            foreach (array_slice($this->clientes, 0, 8) as $c) {
                DB::table('chat_participantes')->updateOrInsert(
                    ['id_chat' => $grupoId, 'id_user' => $c['id_user']],
                    ['rol_en_chat' => 'CLIENTE', 'unido_en' => now()->subDays(14)]
                );
            }
        }

        if ($grupoId) {
            $anuncios = [
                '¡Bienvenidos al grupo virtual FigueFit! Compartimos tips y retos semanales.',
                'Este jueves live de movilidad a las 7pm. ¿Quién se apunta?',
                'Recuerden subir foto de progreso antes del domingo.',
                'Nuevo video de HIIT disponible en su módulo de contenido.',
                'Felicitaciones a quienes completaron la semana 2 del reto 🔥',
            ];
            $emisorAnuncio = (int) (DB::table('coaches')->where('id_coach', $coachGrupo)->value('id_user') ?: $this->adminId);

            foreach ($anuncios as $aIdx => $texto) {
                $exists = DB::table('mensajes')
                    ->where('id_chat', $grupoId)
                    ->where('contenido', $texto)
                    ->exists();
                if ($exists || ! $emisorAnuncio) {
                    continue;
                }
                $enviado = now()->subDays(5 - $aIdx);
                DB::table('mensajes')->insert([
                    'id_chat' => $grupoId,
                    'id_user_emisor' => $emisorAnuncio,
                    'contenido' => $texto,
                    'tipo_mensaje' => 'TEXTO',
                    'leido' => false,
                    'enviado_en' => $enviado,
                    'creado_en' => $enviado,
                ]);
            }
        }
    }

    private function seedWhatsapp(): void
    {
        $configData = [
            'numero_business' => '+573001112233',
            'link_grupo_principal' => 'https://chat.whatsapp.com/demofiguefitvirtual',
            'mensaje_bienvenida' => '¡Bienvenido a FigueFit! Aquí acompañamos tu proceso fitness.',
            'horario_atencion' => 'Lun-Vie 7:00-19:00 · Sáb 8:00-13:00',
            'respuestas_automaticas' => json_encode([
                'horarios' => 'Atendemos Lun-Vie 7-19h.',
                'pagos' => 'Comparte tu comprobante por este medio.',
            ]),
            'activa' => true,
            'actualizado_en' => now(),
        ];

        $configId = DB::table('configuracion_whatsapp')->value('id_config_wa');
        if ($configId) {
            DB::table('configuracion_whatsapp')->where('id_config_wa', $configId)->update($configData);
        } else {
            $configId = DB::table('configuracion_whatsapp')->insertGetId($configData);
        }

        $grupoWaId = DB::table('grupos_whatsapp')->where('nombre', 'Sala Presencial FigueFit')->value('id_grupo_wa');
        if (! $grupoWaId) {
            $grupoWaId = DB::table('grupos_whatsapp')->insertGetId([
                'id_config_wa' => $configId,
                'nombre' => 'Sala Presencial FigueFit',
                'tipo' => 'PRESENCIAL',
                'criterio' => 'modalidad=PRESENCIAL',
                'link_invitacion' => 'https://chat.whatsapp.com/demofiguefitpresencial',
                'estado' => 'ACTIVO',
                'creado_en' => now(),
            ]);
        }

        $invCols = Schema::hasTable('invitaciones_whatsapp')
            ? Schema::getColumnListing('invitaciones_whatsapp')
            : [];

        foreach ($this->clientes as $cliente) {
            if ($cliente['modalidad'] !== 'PRESENCIAL') {
                continue;
            }

            $exists = DB::table('invitaciones_whatsapp')->where('id_cliente', $cliente['id_cliente'])->exists();
            if ($exists) {
                continue;
            }

            $row = [
                'id_cliente' => $cliente['id_cliente'],
                'link_mostrado' => 'https://chat.whatsapp.com/demofiguefitpresencial',
            ];

            if (in_array('id_grupo_wa', $invCols, true)) {
                $row['id_grupo_wa'] = $grupoWaId;
            }
            if (in_array('mostrada_en', $invCols, true)) {
                $row['mostrada_en'] = now()->subDays(3);
            }
            if (in_array('aceptada_en', $invCols, true)) {
                $row['aceptada_en'] = now()->subDays(2);
            }
            if (in_array('cerrada_sin_aceptar', $invCols, true)) {
                $row['cerrada_sin_aceptar'] = false;
            }
            if (in_array('persistencia_local', $invCols, true)) {
                $row['persistencia_local'] = true;
            }

            DB::table('invitaciones_whatsapp')->insert($row);
        }
    }

    private function seedEventos(): void
    {
        $defs = [
            ['Live movilidad FigueFit', 'Sesión abierta virtual.', 'LIVE', 'VIRTUAL', 3, 50, 'PUBLICADO'],
            ['Taller nutrición práctica', 'Educación alimentaria grupal.', 'TALLER', 'VIRTUAL', 7, 40, 'PUBLICADO'],
            ['Clase funcional sala', 'Entrenamiento presencial grupal.', 'CLASE', 'PRESENCIAL', 2, 20, 'PUBLICADO'],
            ['Reto 5K Neiva', 'Preparación carrera local.', 'EVENTO', 'PRESENCIAL', 14, 30, 'PUBLICADO'],
            ['Webinar hábitos', 'Charla virtual institucional.', 'WEBINAR', 'VIRTUAL', 10, 60, 'BORRADOR'],
            ['Open day FigueFit', 'Jornada de puertas abiertas.', 'EVENTO', 'PRESENCIAL', 21, 80, 'PUBLICADO'],
        ];

        foreach ($defs as $i => [$nombre, $desc, $tipo, $mod, $dias, $cupo, $estado]) {
            $exists = DB::table('eventos')->where('nombre', $nombre)->exists();
            if ($exists) {
                $eventoId = (int) DB::table('eventos')->where('nombre', $nombre)->value('id_evento');
            } else {
                $coachId = $this->coachIds[$i % max(1, count($this->coachIds))] ?? null;
                $inicio = now()->addDays($dias)->setTime(9 + ($i % 3), 0);
                $eventoId = DB::table('eventos')->insertGetId([
                    'id_coach' => $coachId,
                    'id_admin' => $this->adminId ?: null,
                    'nombre' => $nombre,
                    'descripcion' => $desc,
                    'tipo' => $tipo,
                    'fecha_inicio' => $inicio,
                    'fecha_fin' => $inicio->copy()->addHours(1 + ($i % 2)),
                    'modalidad' => $mod,
                    'cupo_maximo' => $cupo,
                    'cupo_inscritos' => (int) floor($cupo * 0.25),
                    'estado' => $estado,
                    'creado_en' => now()->subDays(5),
                ]);
            }

            $participantes = array_slice($this->clientes, $i, 4);
            foreach ($participantes as $pIdx => $cliente) {
                DB::table('evento_participantes')->updateOrInsert(
                    ['id_evento' => $eventoId, 'id_cliente' => $cliente['id_cliente']],
                    [
                        'estado' => $pIdx === 0 ? 'CONFIRMADO' : 'INSCRITO',
                        'inscrito_en' => now()->subDays(3 - $pIdx),
                        'creado_en' => now()->subDays(3 - $pIdx),
                    ]
                );
            }
        }
    }

    private function seedContenidoEntrenamientoNutricion(): void
    {
        $ejercicioDefs = [
            ['Sentadilla goblet', 'Piernas'],
            ['Press de pecho mancuernas', 'Pecho'],
            ['Remo con banda', 'Espalda'],
            ['Plancha frontal', 'Core'],
            ['Peso muerto rumano', 'Piernas'],
            ['Dominadas asistidas', 'Espalda'],
            ['Zancadas alternas', 'Piernas'],
            ['Press militar', 'Hombros'],
        ];

        $ejercicioIds = [];
        foreach ($ejercicioDefs as [$nombre, $grupo]) {
            $id = DB::table('ejercicios')->where('nombre', $nombre)->value('id_ejercicio');
            if (! $id) {
                $id = DB::table('ejercicios')->insertGetId([
                    'nombre' => $nombre,
                    'grupo_muscular' => $grupo,
                    'descripcion' => "Ejercicio demo {$nombre}",
                    'instrucciones' => 'Control de tempo y respiración.',
                    'creado_en' => now(),
                ]);
            }
            $ejercicioIds[] = (int) $id;
        }

        $clientesObj = array_slice($this->clientes, 0, min(10, count($this->clientes)));
        $videoIds = DB::table('videos')->orderBy('id_video')->pluck('id_video')->all();

        foreach ($clientesObj as $idx => $cliente) {
            $cid = $cliente['id_cliente'];
            $clienteRow = DB::table('clientes')->where('id_cliente', $cid)->first();
            $coachId = $clienteRow->id_coach ?? $clienteRow->id_coach_asignado ?? ($this->coachIds[$idx % max(1, count($this->coachIds))] ?? null);
            $planClienteId = DB::table('planes_cliente')
                ->where('id_cliente', $cid)
                ->where('estado_plan_cliente', 'ACTIVO')
                ->value('id_plan_cliente');

            $planEntNombre = 'Plan demo '.explode('@', $cliente['correo'])[0];
            $planEntId = DB::table('planes_entrenamiento')
                ->where('id_cliente', $cid)
                ->where('nombre', $planEntNombre)
                ->value('id_plan_entrenamiento');

            if (! $planEntId && $coachId) {
                $planEntId = DB::table('planes_entrenamiento')->insertGetId([
                    'id_cliente' => $cid,
                    'id_coach' => $coachId,
                    'id_plan_cliente' => $planClienteId,
                    'nombre' => $planEntNombre,
                    'objetivo' => $clienteRow->objetivo_principal ?? 'Mejorar condición',
                    'modalidad' => $cliente['modalidad'],
                    'estado' => 'ACTIVO',
                    'creado_en' => now()->subDays(20 - $idx),
                ]);

                $rutinaId = DB::table('rutinas')->insertGetId([
                    'id_plan_entrenamiento' => $planEntId,
                    'nombre' => 'Full body '.chr(65 + ($idx % 3)),
                    'descripcion' => 'Rutina demo semanal FigueFit.',
                    'dia_semana' => 1 + ($idx % 5),
                    'orden' => 1,
                    'duracion_min' => 45,
                    'estado' => 'ACTIVA',
                    'creado_en' => now()->subDays(18 - $idx),
                ]);

                foreach (array_slice($ejercicioIds, 0, 4) as $eIdx => $ejId) {
                    DB::table('rutina_ejercicios')->insert([
                        'id_rutina' => $rutinaId,
                        'id_ejercicio' => $ejId,
                        'series' => 3,
                        'repeticiones' => '10-12',
                        'descanso_seg' => 60,
                        'peso_sugerido_kg' => 8 + $eIdx * 2,
                        'orden' => $eIdx + 1,
                        'notas' => 'Tempo controlado',
                    ]);
                }

                if (! DB::table('registro_rutinas')->where('id_cliente', $cid)->where('id_rutina', $rutinaId)->exists()) {
                    DB::table('registro_rutinas')->insert([
                        'id_cliente' => $cid,
                        'id_rutina' => $rutinaId,
                        'fecha_ejecucion' => now()->subDays(2 + ($idx % 4))->toDateString(),
                        'completada' => true,
                        'dificultad' => 5 + ($idx % 4),
                        'observacion_cliente' => 'Sesión completada demo',
                        'creado_en' => now(),
                    ]);
                }
            }

            $planNutId = DB::table('planes_nutricionales')
                ->where('id_cliente', $cid)
                ->where('nombre', 'Plan nutricional demo')
                ->value('id_plan_nutricional');

            if (! $planNutId && $coachId) {
                $planNutId = DB::table('planes_nutricionales')->insertGetId([
                    'id_cliente' => $cid,
                    'id_coach' => $coachId,
                    'id_plan_cliente' => $planClienteId,
                    'nombre' => 'Plan nutricional demo',
                    'objetivo' => 'Balance y adherencia',
                    'kcal_objetivo' => 1750 + ($idx * 20),
                    'proteinas_g' => 115,
                    'carbohidratos_g' => 170,
                    'grasas_g' => 55,
                    'recomendaciones' => 'Prioriza proteína e hidratación diaria.',
                    'estado' => 'ACTIVO',
                    'creado_en' => now()->subDays(15 - $idx),
                ]);

                foreach ([
                    ['DESAYUNO', 'Bowl proteico', 'Avena + yogurt + frutos rojos', 1],
                    ['ALMUERZO', 'Pollo y quinoa', '150g pechuga + quinoa + ensalada', 2],
                    ['CENA', 'Opción ligera', 'Proteína magra + vegetales', 3],
                ] as [$tipo, $nombre, $receta, $orden]) {
                    $existsComida = DB::table('comidas')
                        ->where('id_plan_nutricional', $planNutId)
                        ->where('nombre', $nombre)
                        ->exists();
                    if ($existsComida) {
                        continue;
                    }
                    DB::table('comidas')->insert([
                        'id_plan_nutricional' => $planNutId,
                        'tipo_comida' => $tipo,
                        'nombre' => $nombre,
                        'receta' => $receta,
                        'porcion' => '1 ración',
                        'calorias' => 430,
                        'proteinas_g' => 32,
                        'carbohidratos_g' => 38,
                        'grasas_g' => 12,
                        'orden' => $orden,
                        'creado_en' => now(),
                    ]);
                }
            }

            if (! DB::table('registros_progreso')->where('id_cliente', $cid)->exists()) {
                foreach ([0, 7, 14] as $pIdx => $dias) {
                    $peso = (float) ($clienteRow->peso_inicial_kg ?? 70) - ($pIdx * 0.5);
                    $estatura = (float) ($clienteRow->estatura_cm ?? 165);
                    DB::table('registros_progreso')->insert([
                        'id_cliente' => $cid,
                        'id_coach' => $coachId,
                        'id_plan_cliente' => $planClienteId,
                        'fecha_registro' => now()->subDays(14 - $dias)->toDateString(),
                        'peso_kg' => $peso,
                        'estatura_cm' => $estatura,
                        'imc' => $estatura > 0 ? round($peso / (($estatura / 100) ** 2), 2) : null,
                        'cintura_cm' => 75 - $pIdx,
                        'nivel_energia' => 7,
                        'calidad_sueno' => 8,
                        'agua_litros' => 2.2,
                        'es_linea_base' => $pIdx === 0,
                        'creado_en' => now(),
                    ]);
                }
            }

            // Asistencias + racha (solo presencial) — perfilpresencial01
            if ($cliente['modalidad'] === 'PRESENCIAL' && $coachId) {
                $asistCount = (int) DB::table('asistencias')->where('id_cliente', $cid)->count();
                if ($asistCount < 5) {
                    for ($a = 0; $a < 8; $a++) {
                        if ($a % 3 === 0) {
                            continue; // huecos para el calendario
                        }
                        $fechaA = now()->subDays($a)->toDateString();
                        $existsA = DB::table('asistencias')
                            ->where('id_cliente', $cid)
                            ->where('fecha', $fechaA)
                            ->exists();
                        if ($existsA) {
                            continue;
                        }
                        DB::table('asistencias')->insert([
                            'id_cliente' => $cid,
                            'id_coach' => $coachId,
                            'id_sesion' => null,
                            'fecha' => $fechaA,
                            'estado' => 'PRESENTE',
                            'llama_racha' => true,
                            'observacion' => $a === 1 ? 'Sesión demo sala' : null,
                            'registrada_en' => now()->subDays($a),
                        ]);
                    }
                }

                DB::table('rachas_cliente')->updateOrInsert(
                    ['id_cliente' => $cid, 'tipo' => 'ASISTENCIA'],
                    [
                        'dias_consecutivos' => 2 + ($idx % 4),
                        'mejor_racha' => 5 + ($idx % 6),
                        'ultima_fecha' => now()->subDay()->toDateString(),
                        'actualizado_en' => now(),
                    ]
                );

                if (! DB::table('metas_cliente')->where('id_cliente', $cid)->exists()) {
                    DB::table('metas_cliente')->insert([
                        'id_cliente' => $cid,
                        'nombre' => 'Peso objetivo',
                        'metrica' => 'PESO',
                        'valor_inicial' => (float) ($clienteRow->peso_inicial_kg ?? 72),
                        'valor_actual' => (float) ($clienteRow->peso_inicial_kg ?? 72) - 1.2,
                        'valor_objetivo' => (float) ($clienteRow->peso_inicial_kg ?? 72) - 4,
                        'unidad' => 'kg',
                        'fecha_inicio' => now()->subDays(20)->toDateString(),
                        'fecha_objetivo' => now()->addDays(40)->toDateString(),
                        'estado' => 'ACTIVA',
                        'creado_en' => now()->subDays(20),
                    ]);
                }
            }

            if ($cliente['modalidad'] === 'VIRTUAL' && $videoIds !== []) {
                foreach (array_slice($videoIds, 0, 3 + ($idx % 3)) as $vIdx => $videoId) {
                    DB::table('progreso_videos')->updateOrInsert(
                        ['id_cliente' => $cid, 'id_video' => $videoId],
                        [
                            'id_plan_cliente' => $planClienteId,
                            'estado' => $vIdx === 0 ? 'COMPLETADO' : ($vIdx === 1 ? 'EN_PROGRESO' : 'BLOQUEADO'),
                            'iniciado_en' => $vIdx < 2 ? now()->subDays(5 - $vIdx) : null,
                            'completado_en' => $vIdx === 0 ? now()->subDays(4) : null,
                            'porcentaje' => $vIdx === 0 ? 100 : ($vIdx === 1 ? 45 : 0),
                            'llama_racha' => $vIdx === 0,
                        ]
                    );
                }
            }
        }
    }

    private function seedNotificaciones(): void
    {
        $tipos = [
            ['PLAN', 'Plan activado', 'Tu plan FigueFit ya está activo. ¡A entrenar!'],
            ['PAGO', 'Pago confirmado', 'Recibimos tu pago. Gracias por confiar en FigueFit.'],
            ['MENSAJE', 'Nuevo mensaje', 'Tienes un mensaje de tu coach.'],
            ['EVENTO', 'Próximo evento', 'No olvides inscribirte al evento de esta semana.'],
            ['PROGRESO', 'Registra progreso', 'Es momento de actualizar tu peso y medidas.'],
            ['NUTRICION', 'Plan nutricional', 'Tu coach actualizó tu plan de alimentación.'],
            ['TIENDA', 'Pedido tienda', 'Tu pedido de tienda fue actualizado.'],
            ['SISTEMA', 'Bienvenido', 'Explora los módulos de tu panel FigueFit.'],
        ];

        $creadas = 0;
        $userIds = array_merge(
            array_column($this->clientes, 'id_user'),
            $this->adminId ? [$this->adminId] : []
        );

        foreach ($userIds as $uIdx => $userId) {
            foreach ($tipos as $tIdx => [$tipo, $titulo, $mensaje]) {
                $refKey = "demo-{$userId}-{$tipo}-{$tIdx}";
                $exists = DB::table('notificaciones')
                    ->where('id_user', $userId)
                    ->where('titulo', $titulo)
                    ->where('mensaje', $mensaje)
                    ->exists();
                if ($exists) {
                    continue;
                }

                DB::table('notificaciones')->insert([
                    'id_user' => $userId,
                    'tipo' => $tipo,
                    'titulo' => $titulo,
                    'mensaje' => $mensaje,
                    'leida' => ($uIdx + $tIdx) % 3 === 0,
                    'canal' => 'IN_APP',
                    'referencia_tipo' => 'DEMO',
                    'referencia_id' => crc32($refKey),
                    'creado_en' => now()->subDays(10 - ($tIdx % 5))->subHours($uIdx % 6),
                ]);
                $creadas++;
            }
        }

        while ($creadas < 50 && count($this->clientes) > 0) {
            $cliente = $this->clientes[$creadas % count($this->clientes)];
            $titulo = 'Recordatorio FigueFit #'.($creadas + 1);
            if (DB::table('notificaciones')->where('id_user', $cliente['id_user'])->where('titulo', $titulo)->exists()) {
                $creadas++;

                continue;
            }
            DB::table('notificaciones')->insert([
                'id_user' => $cliente['id_user'],
                'tipo' => 'RECORDATORIO',
                'titulo' => $titulo,
                'mensaje' => 'Mantén tu racha de entrenamiento esta semana.',
                'leida' => false,
                'canal' => 'IN_APP',
                'referencia_tipo' => 'DEMO',
                'referencia_id' => 1000 + $creadas,
                'creado_en' => now()->subHours($creadas),
            ]);
            $creadas++;
        }
    }

    private function seedNovedades(): void
    {
        $planVirtual = (int) (DB::table('planes')->where('modalidad', 'VIRTUAL')->value('id_plan') ?: ($this->planIds[0] ?? 1));
        $planPremium = (int) (DB::table('planes')->where('codigo', 'VIRTUAL-PREMIUM')->value('id_plan') ?: $planVirtual);

        $novedades = [
            ['Reto 21 días FigueFit', 'Transforma tus hábitos', 'Únete al reto con seguimiento diario.', 'https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?w=800&q=80', 'RETO', $planVirtual, 1],
            ['Plan Premium Virtual', 'Entrenamiento + nutrición', 'Acompañamiento semanal con coach.', 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?w=800&q=80', 'PLAN', $planPremium, 2],
            ['Comunidad activa', 'Grupo WhatsApp y chat', 'Conecta con otros miembros FigueFit.', 'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?w=800&q=80', 'GENERAL', null, 3],
            ['Tienda presencial', 'Suplementos y accesorios', 'Descuentos exclusivos para sala.', 'https://images.unsplash.com/photo-1593095948071-474c5cc2989d?w=800&q=80', 'TIENDA', null, 4],
            ['Live movilidad', 'Sesión gratuita virtual', 'Reserva tu cupo para el jueves.', 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=800&q=80', 'EVENTO', $planVirtual, 5],
        ];

        foreach ($novedades as [$titulo, $sub, $desc, $img, $tipo, $idPlan, $orden]) {
            DB::table('novedades_welcome')->updateOrInsert(
                ['titulo' => $titulo],
                [
                    'subtitulo' => $sub,
                    'descripcion' => $desc,
                    'imagen_url' => $img,
                    'tipo' => $tipo,
                    'boton_texto' => 'Ver más',
                    'url_destino' => '/cliente/dashboard',
                    'id_plan' => $idPlan,
                    'activo' => true,
                    'orden' => $orden,
                    'inicia_en' => now()->subDays(2),
                    'termina_en' => now()->addMonths(2),
                    'creado_por' => $this->adminId ?: null,
                    'actualizado_en' => now(),
                ]
            );
        }
    }

    private function upsertUser(array $data): int
    {
        $correo = $data['correo'];
        $existing = DB::table('usuarios')->where('correo', $correo)->value('id_user');

        $payload = [
            'nombres' => $data['nombres'],
            'apellidos' => $data['apellidos'],
            'correo' => $correo,
            'email' => $correo,
            'telefono' => $data['telefono'] ?? null,
            'Tipo_documento' => 'CC',
            'documento_identidad' => $data['documento'] ?? null,
            'password_hash' => $data['password'],
            'estado' => 'ACTIVO',
            'estado_cuenta' => 'ACTIVO',
            'acepto_terminos' => 1,
            'fecha_aceptacion_terminos' => now(),
            'correo_verificado_en' => now(),
            'actualizado_en' => now(),
        ];

        if (! empty($data['foto'])) {
            $payload['foto_perfil_url'] = $data['foto'];
        } else {
            $payload['foto_perfil_url'] = 'https://i.pravatar.cc/300?u='.urlencode($correo);
        }

        if ($existing) {
            DB::table('usuarios')->where('id_user', $existing)->update($payload);
            $userId = (int) $existing;
        } else {
            $payload['codigo_pais'] = '+57';
            $payload['creado_en'] = now();
            $userId = (int) DB::table('usuarios')->insertGetId($payload);
        }

        if (! empty($data['rol'])) {
            $this->attachRole($userId, $data['rol']);
        }

        return $userId;
    }

    private function attachRole(int $userId, string $rolCode): void
    {
        if (! isset($this->roles[$rolCode])) {
            return;
        }

        $exists = DB::table('user_roles')
            ->where('id_user', $userId)
            ->where('id_rol', $this->roles[$rolCode])
            ->exists();

        if ($exists) {
            return;
        }

        $row = [
            'id_user' => $userId,
            'id_rol' => $this->roles[$rolCode],
            'asignado_en' => now(),
        ];
        if (Schema::hasColumn('user_roles', 'creado_en')) {
            $row['creado_en'] = now();
        }

        DB::table('user_roles')->insert($row);
    }

    private function imprimirResumen(): void
    {
        $counts = [
            'Usuarios demo' => DB::table('usuarios')->where('correo', 'like', '%@demo.figuefit.test')->count(),
            'Clientes' => DB::table('clientes')->count(),
            'Coaches' => DB::table('coaches')->count(),
            'Propuestas' => Schema::hasTable('propuestas_plan') ? DB::table('propuestas_plan')->count() : 0,
            'Solicitudes' => DB::table('solicitudes_compra')->count(),
            'Pagos' => DB::table('pagos')->count(),
            'Órdenes tienda' => Schema::hasTable('ordenes_tienda') ? DB::table('ordenes_tienda')->count() : 0,
            'Chats' => DB::table('chats')->count(),
            'Mensajes' => DB::table('mensajes')->count(),
            'Notificaciones' => DB::table('notificaciones')->count(),
            'Eventos' => DB::table('eventos')->count(),
            'Novedades welcome' => Schema::hasTable('novedades_welcome') ? DB::table('novedades_welcome')->count() : 0,
        ];

        $this->command?->info('Bundle '.self::BUNDLE_ID.' cargado correctamente.');
        $this->command?->table(['Recurso', 'Total'], collect($counts)->map(fn ($v, $k) => [$k, $v])->values()->all());
        $this->command?->line('Admin: admin@figuefit.com / '.self::PASSWORD_ADMIN);
        $this->command?->line('Demo: *@demo.figuefit.test / '.self::PASSWORD_DEMO);
        $this->command?->line('Institucional B2B: rrhh@campoalegre.demo.figuefit.test / '.self::PASSWORD_DEMO.' → /institucional');
    }
}
