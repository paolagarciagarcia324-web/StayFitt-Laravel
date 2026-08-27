<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$email = 'cliente.presencial@figuefit.test';
$password = 'demo12345';

foreach (['stayfit', 'stayfit_test'] as $db) {
    config(['database.connections.mysql.database' => $db]);
    DB::purge('mysql');
    DB::reconnect('mysql');

    echo "=== $db ===\n";
    try {
        $row = DB::table('usuarios')->where('correo', $email)->first();
        if (! $row) {
            echo "USER: NOT FOUND\n\n";
            continue;
        }
        echo "USER: #{$row->id_user} {$row->nombres}\n";
        echo "ESTADO: {$row->estado}\n";
        echo "HASH: ".substr($row->password_hash ?? '', 0, 10)."...\n";
        $ok = Hash::check($password, $row->password_hash ?? '');
        echo "PASS demo12345: ".($ok ? 'OK' : 'FAIL')."\n";
        $roles = DB::table('user_roles')
            ->join('roles', 'roles.id_rol', '=', 'user_roles.id_rol')
            ->where('user_roles.id_user', $row->id_user)
            ->pluck('roles.codigo');
        echo 'ROLES: '.$roles->implode(',')."\n\n";
    } catch (Throwable $e) {
        echo 'ERROR: '.$e->getMessage()."\n\n";
    }
}

echo 'APP_DB='.config('database.connections.mysql.database')."\n";

$service = app(App\Services\Auth\LoginUsuarioService::class);
$user = $service->findByLogin($email);
echo 'SERVICE_FIND: '.($user ? "#{$user->id_user}" : 'NULL')."\n";
echo 'SERVICE_PASS: '.($user && $service->credentialsMatch($user, $password) ? 'OK' : 'FAIL')."\n";
