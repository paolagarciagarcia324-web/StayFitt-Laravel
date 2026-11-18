<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$out = [];
$checks = [];

function check(array &$checks, string $id, string $desc, bool $ok, $detail = ''): void
{
    $checks[] = ['id' => $id, 'desc' => $desc, 'status' => $ok ? 'APROBADO' : 'FALLIDO', 'detail' => $detail];
    echo ($ok ? 'PASS' : 'FAIL')." [$id] $desc — ".json_encode($detail).PHP_EOL;
}

// Connectivity
try {
    $db = DB::connection()->getDatabaseName();
    check($checks, 'DB-001', 'Conexión MySQL', true, $db);
} catch (Throwable $e) {
    check($checks, 'DB-001', 'Conexión MySQL', false, $e->getMessage());
    file_put_contents(__DIR__.'/db_verify.json', json_encode($checks, JSON_PRETTY_PRINT));
    exit(1);
}

check($checks, 'DB-002', 'Tabla instituciones existe', Schema::hasTable('instituciones'));
check($checks, 'DB-003', 'Columna id_user_responsable', Schema::hasColumn('instituciones', 'id_user_responsable'));
check($checks, 'DB-004', 'Tabla invitaciones_institucionales', Schema::hasTable('invitaciones_institucionales'));
check($checks, 'DB-005', 'Tabla comunicados_institucionales', Schema::hasTable('comunicados_institucionales'));

$roles = DB::table('roles')->pluck('codigo')->all();
check($checks, 'DB-006', 'Rol RESPONSABLE_INSTITUCIONAL', in_array('RESPONSABLE_INSTITUCIONAL', $roles, true), $roles);

$rrhh = DB::table('usuarios')->where('correo', 'rrhh@campoalegre.demo.figuefit.test')->first();
check($checks, 'DB-007', 'Usuario responsable demo', (bool) $rrhh, $rrhh->id_user ?? null);

if ($rrhh) {
    $hasRole = DB::table('user_roles as ur')
        ->join('roles as r', 'r.id_rol', '=', 'ur.id_rol')
        ->where('ur.id_user', $rrhh->id_user)
        ->where('r.codigo', 'RESPONSABLE_INSTITUCIONAL')
        ->exists();
    check($checks, 'DB-008', 'Rol asignado a RRHH', $hasRole);

    $inst = DB::table('instituciones')->where('id_user_responsable', $rrhh->id_user)->first();
    check($checks, 'DB-009', 'Institución vinculada a RRHH', (bool) $inst, $inst->nombre ?? null);
}

$admin = DB::table('usuarios')->where('correo', 'paola@correo.com')->exists();
$coach = DB::table('usuarios')->where('correo', 'laura.coach@demo.figuefit.test')->exists();
$staff = DB::table('usuarios')->where('correo', 'asistente@demo.figuefit.test')->exists();
$cv = DB::table('usuarios')->where('correo', 'valen.gomez@demo.figuefit.test')->exists();
$cp = DB::table('usuarios')->where('correo', 'julian.rojas@demo.figuefit.test')->exists();
$ci = DB::table('usuarios')->where('correo', 'diana.suarez@demo.figuefit.test')->exists();

check($checks, 'DB-010', 'Admin Paola', $admin);
check($checks, 'DB-011', 'Coach demo', $coach);
check($checks, 'DB-012', 'Staff demo', $staff);
check($checks, 'DB-013', 'Cliente virtual demo', $cv);
check($checks, 'DB-014', 'Cliente presencial demo', $cp);
check($checks, 'DB-015', 'Cliente institucional demo', $ci);

$planQa = DB::table('planes')->where('nombre', 'like', 'Plan QA Automático%')->orderByDesc('id_plan')->first();
check($checks, 'DB-016', 'Plan QA creado por E2E (si ya corrió)', (bool) $planQa, $planQa->nombre ?? 'aún no');

$prodQa = DB::table('productos_tienda')->where('sku', 'like', 'QA-SKU-%')->orderByDesc('id_producto')->first();
check($checks, 'DB-017', 'Producto QA creado por E2E (si ya corrió)', (bool) $prodQa, $prodQa->sku ?? 'aún no');

$instCount = DB::table('instituciones')->count();
check($checks, 'DB-018', '≥2 instituciones para aislamiento', $instCount >= 2, $instCount);

$inv = DB::table('invitaciones_institucionales')->count();
check($checks, 'DB-019', 'Invitaciones institucionales >0', $inv > 0, $inv);

// Isolation check: members of inst A vs B
$insts = DB::table('instituciones')->orderBy('id_institucion')->limit(2)->get();
if ($insts->count() >= 2) {
    $a = $insts[0]->id_institucion;
    $b = $insts[1]->id_institucion;
    $overlap = DB::table('clientes')
        ->where('id_institucion', $a)
        ->whereIn('id_cliente', function ($q) use ($b) {
            $q->select('id_cliente')->from('clientes')->where('id_institucion', $b);
        })->count();
    check($checks, 'DB-020', 'Sin clientes compartidos entre 2 instituciones', $overlap === 0, $overlap);
}

file_put_contents(__DIR__.'/db_verify.json', json_encode([
    'at' => date('c'),
    'database' => DB::connection()->getDatabaseName(),
    'checks' => $checks,
    'aprobados' => count(array_filter($checks, fn ($c) => $c['status'] === 'APROBADO')),
    'fallidos' => count(array_filter($checks, fn ($c) => $c['status'] === 'FALLIDO')),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo 'DB_VERIFY_DONE'.PHP_EOL;
