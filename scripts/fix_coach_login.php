<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\Auth\LoginUsuarioService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

$email = 'laura.coach@demo.figuefit.test';
$password = 'demo12345';

echo "DB: ".config('database.connections.mysql.database')."\n\n";

$row = DB::table('usuarios')
    ->whereRaw('LOWER(correo) = ?', [strtolower($email)])
    ->orWhereRaw('LOWER(email) = ?', [strtolower($email)])
    ->first();

if (! $row) {
    echo "USER: NOT FOUND\n";
    exit(1);
}

echo "USER: #{$row->id_user} {$row->nombres} estado={$row->estado}\n";

$hash = $row->password_hash ?? '';
echo 'HASH_LEN: '.strlen($hash)."\n";
echo 'HASH_CHECK: '.(Hash::check($password, $hash) ? 'OK' : 'FAIL')."\n";

$roles = DB::table('user_roles')
    ->join('roles', 'roles.id_rol', '=', 'user_roles.id_rol')
    ->where('user_roles.id_user', $row->id_user)
    ->pluck('roles.codigo');
echo 'ROLES: '.$roles->implode(',')."\n";

$coach = DB::table('coaches')->where('id_user', $row->id_user)->first();
echo 'COACH: '.($coach ? $coach->id_coach : 'NONE')."\n";

$service = app(LoginUsuarioService::class);
$user = $service->findByLogin($email);
echo 'SERVICE_FIND: '.($user ? "#{$user->id_user}" : 'NULL')."\n";
echo 'SERVICE_PASS: '.($user && $service->credentialsMatch($user, $password) ? 'OK' : 'FAIL')."\n";

if ($user) {
    echo 'TIENE_ROL_COACH: '.($user->tieneRol('COACH') ? 'YES' : 'NO')."\n";
}
