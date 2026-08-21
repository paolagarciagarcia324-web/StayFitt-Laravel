<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoAdminSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('usuarios')->where('correo', 'like', '%@demo.figuefit.test')->exists()) {
            $this->command?->info('Datos demo ya existen (@demo.figuefit.test). Nada que hacer.');

            return;
        }

        $adminId = (int) (DB::table('usuarios')->where('correo', 'paola@correo.com')->value('id_user')
            ?? DB::table('usuarios')->orderBy('id_user')->value('id_user'));

        $roles = DB::table('roles')->pluck('id_rol', 'codigo');
        $planes = DB::table('planes')->get()->keyBy('id_plan');
        $planVirtual = (int) ($planes->firstWhere('modalidad', 'VIRTUAL')?->id_plan ?? 1);
        $planVirtualPro = (int) ($planes->firstWhere('nombre', 'FigueFit Virtual Pro')?->id_plan ?? $planVirtual);
        $planPresencial = (int) ($planes->firstWhere('modalidad', 'PRESENCIAL')?->id_plan ?? 4);
        $planEmpresas = (int) ($planes->firstWhere('nombre', 'FigueFit Virtual Empresas')?->id_plan ?? $planVirtual);

        $horarios = DB::table('horarios_presenciales')->orderBy('id_horario')->pluck('id_horario');
        $modulos = DB::table('modulos_servicio')->pluck('id_modulo', 'codigo');
        $cats = DB::table('categorias_producto')->pluck('id_categoria_producto', 'nombre');
        $password = Hash::make('demo12345');
        $now = now();

        DB::transaction(function () use (
            $adminId, $roles, $planVirtual, $planVirtualPro, $planPresencial, $planEmpresas,
            $horarios, $modulos, $cats, $password, $now, $planes
        ) {
            // Instituciones
            $instCampo = $this->upsertInstitucion([
                'nombre' => 'Colegio Campoalegre',
                'nit' => '900111222-1',
                'contacto_nombre' => 'Laura Méndez',
                'correo' => 'convenio@campoalegre.edu.co',
                'telefono' => '3185550101',
                'ciudad' => 'Neiva',
                'convenio' => 'Bienestar estudiantil 2026',
                'cupos_totales' => 40,
                'cupos_usados' => 0,
                'estado' => 'ACTIVA',
            ]);

            $instUni = $this->upsertInstitucion([
                'nombre' => 'Universidad Surcolombiana',
                'nit' => '891180084-2',
                'contacto_nombre' => 'Carlos Rincón',
                'correo' => 'deportes@usco.edu.co',
                'telefono' => '3185550202',
                'ciudad' => 'Neiva',
                'convenio' => 'Plan empleados administrativos',
                'cupos_totales' => 25,
                'cupos_usados' => 0,
                'estado' => 'ACTIVA',
            ]);

            // Actualizar institución "ara" si existe
            DB::table('instituciones')->where('id_institucion', 1)->update([
                'nombre' => 'Ara Wellness Hub',
                'contacto_nombre' => 'Andrea Ruiz',
                'ciudad' => 'Neiva',
                'cupos_totales' => 20,
                'estado' => 'ACTIVA',
                'actualizado_en' => $now,
            ]);

            // Coaches
            $coachIds = [];
            foreach ([
                ['Laura', 'Sánchez', 'laura.coach@demo.figuefit.test', 'Fuerza y funcional', 'ACTIVO', '4.80'],
                ['Diego', 'Morales', 'diego.coach@demo.figuefit.test', 'HIIT y pérdida de grasa', 'ACTIVO', '4.60'],
                ['Camila', 'Vargas', 'camila.coach@demo.figuefit.test', 'Movilidad y yoga', 'VACACIONES', '4.90'],
            ] as $i => [$nombres, $apellidos, $correo, $esp, $estado, $rating]) {
                $uid = $this->createUser($nombres, $apellidos, $correo, $password, 'COACH', $roles);
                $coachIds[] = DB::table('coaches')->insertGetId([
                    'id_user' => $uid,
                    'codigo_coach' => 'COACH-DEMO-'.($i + 1),
                    'especialidad' => $esp,
                    'biografia' => "Coach FigueFit especializado en {$esp}.",
                    'certificaciones' => 'NSCA-CPT, First Aid',
                    'calificacion_promedio' => $rating,
                    'total_valoraciones' => 12 + $i * 3,
                    'estado_coach' => $estado,
                    'creado_en' => $now,
                ]);
            }

            // Clientes virtuales / presenciales / institucionales
            $clientesData = [
                ['Valentina', 'Gómez', 'valen.gomez@demo.figuefit.test', 'VIRTUAL', 'INDIVIDUAL', 'ACTIVO', 'Bajar grasa', $planVirtualPro, $coachIds[0], null, null],
                ['Andrés', 'Pérez', 'andres.perez@demo.figuefit.test', 'VIRTUAL', 'INDIVIDUAL', 'ACTIVO', 'Ganar músculo', $planVirtual, $coachIds[1], null, null],
                ['Sofía', 'Castro', 'sofia.castro@demo.figuefit.test', 'VIRTUAL', 'INDIVIDUAL', 'PENDIENTE_PAGO', 'Mejorar hábitos', $planVirtual, $coachIds[0], null, null],
                ['Julián', 'Rojas', 'julian.rojas@demo.figuefit.test', 'PRESENCIAL', 'INDIVIDUAL', 'ACTIVO', 'Fuerza', $planPresencial, $coachIds[1], null, $horarios[2] ?? null],
                ['Mariana', 'López', 'mariana.lopez@demo.figuefit.test', 'PRESENCIAL', 'INDIVIDUAL', 'ACTIVO', 'Tonificar', $planPresencial, $coachIds[0], null, $horarios[0] ?? null],
                ['Felipe', 'Nieto', 'felipe.nieto@demo.figuefit.test', 'PRESENCIAL', 'INDIVIDUAL', 'ACTIVA', 'Condición física', $planPresencial, $coachIds[1], null, $horarios[3] ?? null],
                ['Diana', 'Suárez', 'diana.suarez@demo.figuefit.test', 'VIRTUAL', 'INSTITUCIONAL', 'ACTIVO', 'Salud laboral', $planEmpresas, $coachIds[2], $instCampo, null],
                ['Esteban', 'Quiroga', 'esteban.quiroga@demo.figuefit.test', 'VIRTUAL', 'INSTITUCIONAL', 'ACTIVO', 'Energía diaria', $planEmpresas, $coachIds[0], $instCampo, null],
                ['Natalia', 'Ortiz', 'natalia.ortiz@demo.figuefit.test', 'VIRTUAL', 'INSTITUCIONAL', 'ACTIVO', 'Reducir estrés', $planEmpresas, $coachIds[1], $instUni, null],
                ['Kevin', 'Mejía', 'kevin.mejia@demo.figuefit.test', 'VIRTUAL', 'INDIVIDUAL', 'SUSPENDIDO', 'Recomposición', $planVirtual, $coachIds[2], null, null],
            ];

            $clienteIds = [];
            $planClienteIds = [];

            foreach ($clientesData as $idx => [$nombres, $apellidos, $correo, $modalidad, $tipo, $estado, $objetivo, $idPlan, $idCoach, $idInst, $idHorario]) {
                $rolCode = $tipo === 'INSTITUCIONAL' ? 'CLIENTE_INSTITUCIONAL' : 'CLIENTE';
                $uid = $this->createUser($nombres, $apellidos, $correo, $password, $rolCode, $roles);
                $cid = DB::table('clientes')->insertGetId([
                    'id_user' => $uid,
                    'tipo_cliente' => $tipo,
                    'modalidad' => $modalidad,
                    'tipo_documento' => 'CC',
                    'documento' => (string) (1000000000 + $idx),
                    'fecha_nacimiento' => now()->subYears(22 + $idx)->subMonths($idx)->toDateString(),
                    'genero' => $idx % 2 === 0 ? 'FEMENINO' : 'MASCULINO',
                    'ciudad' => 'Neiva',
                    'peso_inicial_kg' => 58 + $idx * 2.5,
                    'estatura_cm' => 160 + ($idx % 5) * 3,
                    'objetivo_principal' => $objetivo,
                    'id_institucion' => $idInst,
                    'id_coach' => $idCoach,
                    'id_coach_asignado' => $idCoach,
                    'id_horario' => $idHorario,
                    'estado_cliente' => $estado,
                    'fecha_alta' => now()->subDays(40 - $idx)->toDateString(),
                    'fecha_activacion' => in_array($estado, ['ACTIVO', 'ACTIVA'], true) ? now()->subDays(35 - $idx) : null,
                    'creado_en' => now()->subDays(40 - $idx),
                ]);
                $clienteIds[] = $cid;

                if ($idInst) {
                    DB::table('instituciones')->where('id_institucion', $idInst)->increment('cupos_usados');
                }

                if ($idHorario) {
                    DB::table('horarios_presenciales')->where('id_horario', $idHorario)->increment('cupo_ocupado');
                }

                DB::table('historial_asignacion_coach')->insert([
                    'id_cliente' => $cid,
                    'id_coach_anterior' => null,
                    'id_coach_nuevo' => $idCoach,
                    'id_admin' => $adminId ?: null,
                    'motivo' => 'Asignación inicial demo',
                    'asignado_en' => now()->subDays(30 - $idx),
                ]);

                $solCodigo = 'SOL-DEMO-'.str_pad((string) ($idx + 1), 3, '0', STR_PAD_LEFT);
                $precio = (float) ($planes[$idPlan]->precio ?? 89000);
                $estadoSol = in_array($estado, ['ACTIVO', 'ACTIVA'], true) ? 'VALIDADA' : ($estado === 'PENDIENTE_PAGO' ? 'PENDIENTE' : 'RECHAZADA');

                $sid = DB::table('solicitudes_compra')->insertGetId([
                    'codigo_solicitud' => $solCodigo,
                    'id_plan' => $idPlan,
                    'id_user' => $uid,
                    'id_cliente' => $cid,
                    'id_institucion' => $idInst,
                    'origen' => $idx % 3 === 0 ? 'PASARELA' : 'COMPROBANTE',
                    'modalidad' => $modalidad,
                    'tipo_cliente' => $tipo,
                    'nombres' => $nombres,
                    'apellidos' => $apellidos,
                    'tipo_documento' => 'CC',
                    'documento' => (string) (1000000000 + $idx),
                    'documento_identidad' => (string) (1000000000 + $idx),
                    'correo' => $correo,
                    'telefono' => '300555'.str_pad((string) $idx, 4, '0', STR_PAD_LEFT),
                    'ciudad' => 'Neiva',
                    'precio_lista' => $precio,
                    'descuento' => $idx === 0 ? 10000 : 0,
                    'total' => $precio - ($idx === 0 ? 10000 : 0),
                    'acepto_terminos' => 1,
                    'fecha_aceptacion_terminos' => now()->subDays(38 - $idx),
                    'estado_solicitud' => $estadoSol,
                    'reserva_cupo' => 1,
                    'observacion_admin' => $estadoSol === 'VALIDADA' ? 'Aprobado demo' : null,
                    'revisado_por' => $estadoSol !== 'PENDIENTE' ? $adminId : null,
                    'revisada_en' => $estadoSol !== 'PENDIENTE' ? now()->subDays(34 - $idx) : null,
                    'creado_en' => now()->subDays(38 - $idx),
                ]);

                $pagoEstado = $estadoSol === 'VALIDADA' ? 'VALIDADO' : ($estadoSol === 'PENDIENTE' ? 'PENDIENTE' : 'RECHAZADO');
                $pid = DB::table('pagos')->insertGetId([
                    'id_solicitud' => $sid,
                    'id_cliente' => $cid,
                    'id_plan' => $idPlan,
                    'tipo_pago' => 'PLAN',
                    'origen_pago' => 'SOLICITUD_PLAN',
                    'monto' => $precio - ($idx === 0 ? 10000 : 0),
                    'moneda' => 'COP',
                    'metodo' => $idx % 3 === 0 ? 'PASARELA' : 'TRANSFERENCIA',
                    'metodo_pago' => $idx % 3 === 0 ? 'PASARELA' : 'TRANSFERENCIA',
                    'pasarela' => $idx % 3 === 0 ? 'BOLD' : 'NINGUNA',
                    'referencia' => 'REF-DEMO-'.($idx + 1),
                    'referencia_pago' => 'PAY-DEMO-'.($idx + 1),
                    'estado_pago' => $pagoEstado,
                    'fecha_pago' => now()->subDays(36 - $idx),
                    'fecha_validacion' => $pagoEstado === 'VALIDADO' ? now()->subDays(34 - $idx) : null,
                    'validado_por' => $pagoEstado === 'VALIDADO' ? $adminId : null,
                    'creado_en' => now()->subDays(min(25, 36 - $idx)),
                ]);

                if ($pagoEstado === 'VALIDADO') {
                    $pcid = DB::table('planes_cliente')->insertGetId([
                        'id_cliente' => $cid,
                        'id_plan' => $idPlan,
                        'id_solicitud' => $sid,
                        'id_pago' => $pid,
                        'fecha_inicio' => now()->subDays(30)->toDateString(),
                        'fecha_fin' => now()->addDays(30)->toDateString(),
                        'estado' => 'ACTIVO',
                        'estado_plan_cliente' => 'ACTIVO',
                        'creado_en' => now()->subDays(30),
                    ]);
                    $planClienteIds[$cid] = $pcid;
                    DB::table('pagos')->where('id_pago', $pid)->update(['id_plan_cliente' => $pcid]);
                    DB::table('planes')->where('id_plan', $idPlan)->increment('cupos_ocupados');

                    foreach (['PERFIL', 'ENTRENAMIENTO', 'NUTRICION', 'PROGRESO', 'AGENDA', 'COMUNICACION'] as $modCode) {
                        if (! isset($modulos[$modCode])) {
                            continue;
                        }
                        DB::table('acceso_cliente_modulo')->insert([
                            'id_cliente' => $cid,
                            'id_modulo' => $modulos[$modCode],
                            'id_plan_cliente' => $pcid,
                            'estado' => 'ACTIVO',
                            'estado_acceso' => 'ACTIVO',
                            'fecha_inicio' => now()->subDays(30)->toDateString(),
                            'fecha_fin' => now()->addDays(30)->toDateString(),
                            'creado_en' => now()->subDays(30),
                        ]);
                    }
                    if ($modalidad === 'VIRTUAL' && isset($modulos['CONTENIDO_VIRTUAL'])) {
                        DB::table('acceso_cliente_modulo')->insert([
                            'id_cliente' => $cid,
                            'id_modulo' => $modulos['CONTENIDO_VIRTUAL'],
                            'id_plan_cliente' => $pcid,
                            'estado' => 'ACTIVO',
                            'estado_acceso' => 'ACTIVO',
                            'fecha_inicio' => now()->subDays(30)->toDateString(),
                            'fecha_fin' => now()->addDays(30)->toDateString(),
                            'creado_en' => now()->subDays(30),
                        ]);
                    }
                    if ($modalidad === 'PRESENCIAL' && isset($modulos['TIENDA'])) {
                        DB::table('acceso_cliente_modulo')->insert([
                            'id_cliente' => $cid,
                            'id_modulo' => $modulos['TIENDA'],
                            'id_plan_cliente' => $pcid,
                            'estado' => 'ACTIVO',
                            'estado_acceso' => 'ACTIVO',
                            'fecha_inicio' => now()->subDays(30)->toDateString(),
                            'fecha_fin' => now()->addDays(30)->toDateString(),
                            'creado_en' => now()->subDays(30),
                        ]);
                    }
                }
            }

            // Solicitudes pendientes extra (sin cliente aún)
            foreach ([
                ['Luisa', 'Hernández', 'luisa.h@demo.figuefit.test', $planVirtual, 'VIRTUAL'],
                ['Mateo', 'Silva', 'mateo.s@demo.figuefit.test', $planPresencial, 'PRESENCIAL'],
            ] as $i => [$n, $a, $c, $p, $m]) {
                $precio = (float) ($planes[$p]->precio ?? 89000);
                DB::table('solicitudes_compra')->insert([
                    'codigo_solicitud' => 'SOL-DEMO-P'.($i + 1),
                    'id_plan' => $p,
                    'origen' => 'COMPROBANTE',
                    'modalidad' => $m,
                    'tipo_cliente' => 'INDIVIDUAL',
                    'nombres' => $n,
                    'apellidos' => $a,
                    'tipo_documento' => 'CC',
                    'documento' => (string) (2000000000 + $i),
                    'documento_identidad' => (string) (2000000000 + $i),
                    'correo' => $c,
                    'telefono' => '3015550'.(100 + $i),
                    'ciudad' => 'Neiva',
                    'precio_lista' => $precio,
                    'descuento' => 0,
                    'total' => $precio,
                    'acepto_terminos' => 1,
                    'fecha_aceptacion_terminos' => now()->subHours(6 + $i),
                    'estado_solicitud' => 'PENDIENTE',
                    'reserva_cupo' => 1,
                    'creado_en' => now()->subHours(6 + $i),
                ]);
            }

            // Cupones
            foreach ([
                ['FIGUE10', 'PORCENTAJE', 10, $planVirtual],
                ['NEIVA25', 'MONTO', 25000, null],
                ['SALA15', 'PORCENTAJE', 15, $planPresencial],
            ] as [$codigo, $tipo, $valor, $idPlan]) {
                DB::table('descuentos_cupones')->insert([
                    'codigo' => $codigo,
                    'tipo' => $tipo,
                    'tipo_descuento' => $tipo,
                    'valor' => $valor,
                    'id_plan' => $idPlan,
                    'fecha_inicio' => now()->subDays(5),
                    'fecha_fin' => now()->addMonths(2),
                    'usos_maximos' => 50,
                    'uso_maximo' => 50,
                    'usos_actuales' => rand(2, 12),
                    'estado' => 'ACTIVO',
                    'estado_cupon' => 'ACTIVO',
                    'creado_por' => $adminId ?: null,
                    'creado_en' => $now,
                ]);
            }

            // Enlaces institucionales
            foreach ([$instCampo, $instUni] as $i => $instId) {
                DB::table('enlaces_registro_institucional')->insert([
                    'id_institucion' => $instId,
                    'id_plan' => $planEmpresas,
                    'token' => Str::lower(Str::random(40)),
                    'cupos_maximos' => 20,
                    'usos_actuales' => 2 + $i,
                    'fecha_expiracion' => now()->addMonths(3),
                    'estado' => 'ACTIVO',
                    'creado_por' => $adminId ?: null,
                    'creado_en' => $now,
                ]);
            }

            // Productos tienda
            $productos = [
                ['FF-TEE-01', 'Camiseta FigueFit Rosa', 'Camisetas', 65000, 40, 4.8, 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=600&q=80'],
                ['FF-BOT-01', 'Botella térmica 750ml', 'Accesorios', 45000, 25, 4.5, 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=600&q=80'],
                ['FF-PRO-01', 'Proteína whey 1kg', 'Suplementos', 129000, 15, 4.7, 'https://images.unsplash.com/photo-1593095948071-474c5cc2989d?w=600&q=80'],
                ['FF-CAP-01', 'Gorra StayFit', 'Identificación de marca', 39000, 30, 4.2, 'https://images.unsplash.com/photo-1588850561407-ed78c282e89b?w=600&q=80'],
                ['FF-BAND-01', 'Bandas elásticas set x3', 'Accesorios', 55000, 18, 4.6, 'https://images.unsplash.com/photo-1598289431512-b97b0917affc?w=600&q=80'],
            ];
            $productoIds = [];
            foreach ($productos as [$sku, $nombre, $catName, $precio, $stock, $rating, $img]) {
                $catId = $cats[$catName] ?? $cats->first();
                $productoIds[] = DB::table('productos_tienda')->insertGetId([
                    'id_categoria_producto' => $catId,
                    'sku' => $sku,
                    'nombre' => $nombre,
                    'descripcion' => "Producto oficial FigueFit · {$nombre}",
                    'precio' => $precio,
                    'calificacion' => $rating,
                    'imagen_url' => $img,
                    'stock_total' => $stock,
                    'stock_minimo' => 5,
                    'exclusivo_presencial' => 1,
                    'estado' => 'ACTIVO',
                    'creado_en' => $now,
                ]);
            }

            // Órdenes tienda (clientes presenciales)
            $presenciales = array_values(array_filter($clienteIds, fn ($cid, $i) => in_array($i, [3, 4, 5], true), ARRAY_FILTER_USE_BOTH));
            foreach ($presenciales as $i => $cid) {
                $prodId = $productoIds[$i % count($productoIds)];
                $precio = (float) DB::table('productos_tienda')->where('id_producto', $prodId)->value('precio');
                $qty = 1 + ($i % 2);
                $total = $precio * $qty;
                $oid = DB::table('ordenes_tienda')->insertGetId([
                    'numero_orden' => 'ORD-DEMO-'.($i + 1),
                    'id_cliente' => $cid,
                    'subtotal' => $total,
                    'costo_envio' => 0,
                    'total' => $total,
                    'tipo_entrega' => 'RECOGE_SALA',
                    'ciudad_entrega' => 'Neiva',
                    'estado_orden' => $i === 0 ? 'PENDIENTE_PAGO' : ($i === 1 ? 'PREPARADA' : 'ENTREGADA'),
                    'creado_en' => now()->subDays(5 - $i),
                ]);
                DB::table('orden_detalle')->insert([
                    'id_orden' => $oid,
                    'id_producto' => $prodId,
                    'cantidad' => $qty,
                    'precio_unitario' => $precio,
                    'subtotal' => $total,
                ]);
            }

            // Eventos
            $eventos = [
                ['Reto 21 días FigueFit', 'RETOS', 'VIRTUAL', 40, 'PUBLICADO', 5, 'https://images.unsplash.com/photo-1518611012118-696072aa579a?w=800&q=80'],
                ['Taller de movilidad', 'TALLER', 'PRESENCIAL', 20, 'PUBLICADO', 8, 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=800&q=80'],
                ['Open day sala Campoalegre', 'ESPECIAL', 'PRESENCIAL', 50, 'PUBLICADO', 12, 'https://images.unsplash.com/photo-1571902943202-507ec2618e8f?w=800&q=80'],
                ['Live Q&A nutrición', 'TALLER', 'VIRTUAL', 80, 'BORRADOR', 20, 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?w=800&q=80'],
            ];
            foreach ($eventos as $i => [$nombre, $tipo, $mod, $cupo, $estado, $dias, $img]) {
                DB::table('eventos')->insert([
                    'id_coach' => $coachIds[$i % count($coachIds)],
                    'id_admin' => $adminId ?: null,
                    'nombre' => $nombre,
                    'descripcion' => "Actividad FigueFit: {$nombre}",
                    'imagen_url' => $img,
                    'tipo' => $tipo,
                    'fecha_inicio' => now()->addDays($dias)->setTime(9 + $i, 0),
                    'fecha_fin' => now()->addDays($dias)->setTime(11 + $i, 0),
                    'modalidad' => $mod,
                    'cupo_maximo' => $cupo,
                    'cupo_inscritos' => (int) floor($cupo * 0.3),
                    'estado' => $estado,
                    'creado_en' => $now,
                ]);
            }

            // Sesiones
            for ($d = 0; $d < 10; $d++) {
                DB::table('sesiones')->insert([
                    'id_coach' => $coachIds[$d % count($coachIds)],
                    'tipo' => $d % 2 ? 'GRUPAL' : 'INDIVIDUAL',
                    'titulo' => $d % 2 ? 'Sesión grupal funcional' : 'Seguimiento 1:1',
                    'fecha' => now()->addDays($d)->toDateString(),
                    'hora_inicio' => '07:00:00',
                    'hora_fin' => '08:00:00',
                    'modalidad' => $d % 3 ? 'PRESENCIAL' : 'VIRTUAL',
                    'cupo_maximo' => $d % 2 ? 12 : 1,
                    'cupo_reservado' => $d % 2 ? 6 : 1,
                    'estado' => 'PROGRAMADA',
                    'creado_en' => $now,
                ]);
            }

            // Comunidad / chats
            $chatId = DB::table('chats')->insertGetId([
                'tipo' => 'COACH_CLIENTE',
                'id_plan' => $planVirtualPro,
                'id_coach' => $coachIds[0],
                'nombre' => 'Laura · Valentina',
                'estado' => 'ACTIVO',
                'creado_en' => $now,
            ]);
            $chatGrupo = DB::table('chats')->insertGetId([
                'tipo' => 'GRUPO_PLAN',
                'id_plan' => $planPresencial,
                'id_coach' => $coachIds[1],
                'nombre' => 'Comunidad sala presencial',
                'estado' => 'ACTIVO',
                'creado_en' => $now,
            ]);

            $valenUser = DB::table('usuarios')->where('correo', 'valen.gomez@demo.figuefit.test')->value('id_user');
            $lauraUser = DB::table('usuarios')->where('correo', 'laura.coach@demo.figuefit.test')->value('id_user');
            foreach ([$chatId => [[$lauraUser, 'COACH'], [$valenUser, 'CLIENTE']], $chatGrupo => [[$lauraUser, 'COACH'], [$valenUser, 'CLIENTE']]] as $idChat => $parts) {
                foreach ($parts as [$uid, $rol]) {
                    if ($uid) {
                        DB::table('chat_participantes')->insert([
                            'id_chat' => $idChat,
                            'id_user' => $uid,
                            'rol_en_chat' => $rol,
                            'unido_en' => $now,
                        ]);
                    }
                }
            }

            if ($lauraUser && $valenUser) {
                DB::table('mensajes')->insert([
                    ['id_chat' => $chatId, 'id_user_emisor' => $lauraUser, 'contenido' => 'Hola Valentina, ¿cómo te fue con la rutina de hoy?', 'tipo_mensaje' => 'TEXTO', 'leido' => 1, 'enviado_en' => now()->subHours(5), 'creado_en' => now()->subHours(5)],
                    ['id_chat' => $chatId, 'id_user_emisor' => $valenUser, 'contenido' => 'Muy bien coach, terminé los 4 bloques 💪', 'tipo_mensaje' => 'TEXTO', 'leido' => 1, 'enviado_en' => now()->subHours(4), 'creado_en' => now()->subHours(4)],
                    ['id_chat' => $chatGrupo, 'id_user_emisor' => $lauraUser, 'contenido' => 'Recuerden hidratarse antes de la clase de las 7am.', 'tipo_mensaje' => 'TEXTO', 'leido' => 0, 'enviado_en' => now()->subHour(), 'creado_en' => now()->subHour()],
                ]);
            }

            // Reportes historial
            if ($adminId) {
                foreach (['pagos', 'clientes', 'cupos'] as $tipo) {
                    DB::table('reportes_generados')->insert([
                        'id_user' => $adminId,
                        'tipo' => $tipo,
                        'parametros_json' => json_encode(['demo' => true]),
                        'formato' => 'CSV',
                        'url_archivo' => null,
                        'generado_en' => now()->subDays(rand(1, 7)),
                    ]);
                }
            }

            // Pagos del mes adicionales para KPI
            foreach ([89000, 159000, 249000, 65000] as $i => $monto) {
                DB::table('pagos')->insert([
                    'id_cliente' => $clienteIds[$i] ?? $clienteIds[0],
                    'id_plan' => $planVirtual,
                    'tipo_pago' => 'PLAN',
                    'origen_pago' => 'SOLICITUD_PLAN',
                    'monto' => $monto,
                    'moneda' => 'COP',
                    'metodo_pago' => 'PASARELA',
                    'pasarela' => 'EPAYCO',
                    'referencia_pago' => 'PAY-MES-'.($i + 1),
                    'estado_pago' => 'VALIDADO',
                    'fecha_pago' => now()->subDays($i + 1),
                    'fecha_validacion' => now()->subDays($i),
                    'validado_por' => $adminId ?: null,
                    'creado_en' => now()->subDays($i + 1),
                ]);
            }
        });

        $this->command?->info('Demo Admin cargado. Password usuarios demo: demo12345');
    }

    private function createUser(string $nombres, string $apellidos, string $correo, string $password, string $rolCode, $roles): int
    {
        $id = DB::table('usuarios')->insertGetId([
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'Tipo_documento' => 'CC',
            'documento_identidad' => (string) random_int(10000000, 99999999),
            'correo' => $correo,
            'email' => $correo,
            'codigo_pais' => '+57',
            'telefono' => '3'.random_int(100000000, 199999999),
            'password_hash' => $password,
            'foto_perfil_url' => 'https://i.pravatar.cc/300?u='.urlencode($correo),
            'estado' => 'ACTIVO',
            'estado_cuenta' => 'ACTIVO',
            'acepto_terminos' => 1,
            'fecha_aceptacion_terminos' => now(),
            'correo_verificado_en' => now(),
            'creado_en' => now(),
        ]);

        if (isset($roles[$rolCode])) {
            DB::table('user_roles')->insert([
                'id_user' => $id,
                'id_rol' => $roles[$rolCode],
                'asignado_en' => now(),
                'creado_en' => now(),
            ]);
        }

        return $id;
    }

    private function upsertInstitucion(array $data): int
    {
        $existing = DB::table('instituciones')->where('nit', $data['nit'])->value('id_institucion');
        if ($existing) {
            DB::table('instituciones')->where('id_institucion', $existing)->update(array_merge($data, [
                'actualizado_en' => now(),
            ]));

            return (int) $existing;
        }

        $data['creado_en'] = now();

        return (int) DB::table('instituciones')->insertGetId($data);
    }
}
