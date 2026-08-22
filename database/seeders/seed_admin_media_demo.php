<?php

/**
 * One-shot demo media for Admin (planes, usuarios, eventos, pasarelas, pagos, ordenes).
 * Run: php database/seeders/seed_admin_media_demo.php
 */

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$planes = [
    1 => 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?w=800&q=80',
    2 => 'https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?w=800&q=80',
    3 => 'https://images.unsplash.com/photo-1554284126-aa88f22d8b74?w=800&q=80',
    4 => 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?w=800&q=80',
];
foreach ($planes as $id => $url) {
    DB::table('planes')->where('id_plan', $id)->update(['imagen_url' => $url, 'actualizado_en' => now()]);
}

$avatars = [
    'valen.gomez@demo.figuefit.test' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=300&q=80',
    'andres.perez@demo.figuefit.test' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&q=80',
    'sofia.castro@demo.figuefit.test' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=300&q=80',
    'julian.rojas@demo.figuefit.test' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=300&q=80',
    'mariana.lopez@demo.figuefit.test' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=300&q=80',
    'felipe.nieto@demo.figuefit.test' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=300&q=80',
    'diana.suarez@demo.figuefit.test' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=300&q=80',
    'esteban.quiroga@demo.figuefit.test' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=300&q=80',
    'natalia.ortiz@demo.figuefit.test' => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=300&q=80',
    'kevin.mejia@demo.figuefit.test' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=300&q=80',
    'laura.coach@demo.figuefit.test' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=300&q=80',
    'diego.coach@demo.figuefit.test' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=300&q=80',
    'camila.coach@demo.figuefit.test' => 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=300&q=80',
];
foreach ($avatars as $correo => $url) {
    DB::table('usuarios')->where('correo', $correo)->update(['foto_perfil_url' => $url, 'actualizado_en' => now()]);
}

$eventoImgs = [
    'https://images.unsplash.com/photo-1518611012118-696072aa579a?w=800&q=80',
    'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=800&q=80',
    'https://images.unsplash.com/photo-1571902943202-507ec2618e8f?w=800&q=80',
    'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?w=800&q=80',
];
$eventos = DB::table('eventos')->orderBy('id_evento')->get();
foreach ($eventos as $i => $ev) {
    DB::table('eventos')->where('id_evento', $ev->id_evento)->update([
        'imagen_url' => $eventoImgs[$i % count($eventoImgs)],
        'actualizado_en' => now(),
    ]);
}

$pasarelaLogos = [
    'WOMPI' => 'https://cdn.simpleicons.org/bancolombia/FF2D78',
    'PAYU' => 'https://cdn.simpleicons.org/paypal/2EF5E6',
    'MERCADOPAGO' => 'https://cdn.simpleicons.org/mercadopago/2EF5E6',
    'NEQUI' => 'https://cdn.simpleicons.org/nubank/FF2D78',
    'DAVIPLATA' => 'https://cdn.simpleicons.org/davivienda/FF2D78',
    'BOLD' => 'https://cdn.simpleicons.org/stripe/2EF5E6',
    'EPAYCO' => 'https://cdn.simpleicons.org/visa/FF2D78',
];
foreach ($pasarelaLogos as $codigo => $url) {
    DB::table('configuracion_pasarelas')->where('codigo_pasarela', $codigo)->update([
        'logo_url' => $url,
        'actualizado_en' => now(),
    ]);
}

// Comprobante demo realista (misma imagen en pagos/órdenes para preview de despliegue)
$recibo = 'images/comprobantes/recibo-ejemplo.png';
if (is_file(public_path($recibo))) {
    DB::table('pagos')->update(['comprobante_url' => $recibo]);
    DB::table('ordenes_tienda')->update(['comprobante_url' => $recibo]);
}

echo "planes=".DB::table('planes')->whereNotNull('imagen_url')->count().PHP_EOL;
echo "avatars=".DB::table('usuarios')->whereNotNull('foto_perfil_url')->count().PHP_EOL;
echo "eventos=".DB::table('eventos')->whereNotNull('imagen_url')->count().PHP_EOL;
echo "pasarelas=".DB::table('configuracion_pasarelas')->whereNotNull('logo_url')->count().PHP_EOL;
echo "pagos_comp=".DB::table('pagos')->whereNotNull('comprobante_url')->count().PHP_EOL;
echo "ordenes_comp=".DB::table('ordenes_tienda')->whereNotNull('comprobante_url')->count().PHP_EOL;
echo "OK\n";
