<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\HorarioPresencial;

$n = 0;
foreach (HorarioPresencial::query()->orderBy('orden')->get() as $h) {
    $nombre = $h->nombreGrupoWhatsapp();
    $actual = trim((string) $h->link_whatsapp);
    if ($actual === '' || preg_match('#^https?://#i', $actual)) {
        $h->update(['link_whatsapp' => $nombre]);
        $n++;
    }
    echo $h->codigo.' | '.$h->rangoHora12().' | '.$h->fresh()->nombreGrupoWhatsapp().PHP_EOL;
}

echo "updated={$n}\n";
echo "OK\n";
