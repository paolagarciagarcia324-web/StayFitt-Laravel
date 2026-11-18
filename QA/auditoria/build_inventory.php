<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$out = __DIR__;

$routes = collect(app('router')->getRoutes())->map(function ($r) {
    return [
        'methods' => implode('|', $r->methods()),
        'uri' => $r->uri(),
        'name' => $r->getName(),
        'action' => $r->getActionName(),
        'middleware' => implode(',', $r->gatherMiddleware()),
    ];
})->values();

file_put_contents($out.'/routes_inventory.json', json_encode($routes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo 'ROUTES='.$routes->count().PHP_EOL;

$byPrefix = [];
foreach ($routes as $route) {
    $uri = $route['uri'];
    $prefix = explode('/', $uri)[0] ?: 'root';
    if (! isset($byPrefix[$prefix])) {
        $byPrefix[$prefix] = 0;
    }
    $byPrefix[$prefix]++;
}
file_put_contents($out.'/routes_by_prefix.json', json_encode($byPrefix, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

$roles = DB::table('roles')->select('id_rol', 'codigo', 'nombre', 'estado')->get();
file_put_contents($out.'/roles_inventory.json', json_encode($roles, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo 'ROLES='.$roles->count().PHP_EOL;

$tables = collect(DB::select('SHOW TABLES'))->map(fn ($r) => array_values((array) $r)[0])->values();
file_put_contents($out.'/tables_inventory.json', json_encode($tables, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo 'TABLES='.$tables->count().PHP_EOL;

$counts = [];
foreach ($tables as $t) {
    try {
        $counts[$t] = DB::table($t)->count();
    } catch (Throwable $e) {
        $counts[$t] = 'ERR: '.$e->getMessage();
    }
}
file_put_contents($out.'/table_counts.json', json_encode($counts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

$usersByRole = DB::table('usuarios as u')
    ->join('user_roles as ur', 'ur.id_user', '=', 'u.id_user')
    ->join('roles as r', 'r.id_rol', '=', 'ur.id_rol')
    ->selectRaw('r.codigo, COUNT(*) as total')
    ->groupBy('r.codigo')
    ->pluck('total', 'codigo');
file_put_contents($out.'/users_by_role.json', json_encode($usersByRole, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo 'INVENTORY_DONE'.PHP_EOL;
