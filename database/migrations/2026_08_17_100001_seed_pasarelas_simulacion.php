<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('configuracion_pasarelas')
            ->whereIn('codigo_pasarela', ['WOMPI', 'PAYU', 'NEQUI', 'DAVIPLATA'])
            ->update(['activa' => 0, 'actualizado_en' => now()]);

        foreach ([
            ['BOLD', 'Bold'],
            ['EPAYCO', 'ePayco'],
            ['MERCADOPAGO', 'Mercado Pago'],
        ] as [$codigo, $nombre]) {
            $existe = DB::table('configuracion_pasarelas')->where('codigo_pasarela', $codigo)->exists();

            if ($existe) {
                DB::table('configuracion_pasarelas')->where('codigo_pasarela', $codigo)->update([
                    'nombre' => $nombre,
                    'modo' => 'SANDBOX',
                    'activa' => 1,
                    'actualizado_en' => now(),
                ]);
                continue;
            }

            DB::table('configuracion_pasarelas')->insert([
                'codigo_pasarela' => $codigo,
                'nombre' => $nombre,
                'modo' => 'SANDBOX',
                'url_webhook' => '/api/webhook/pasarela/'.strtolower($codigo),
                'activa' => 1,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('configuracion_pasarelas')->whereIn('codigo_pasarela', ['BOLD', 'EPAYCO'])->delete();
        DB::table('configuracion_pasarelas')
            ->whereIn('codigo_pasarela', ['WOMPI', 'PAYU', 'NEQUI', 'DAVIPLATA', 'MERCADOPAGO'])
            ->update(['activa' => 1]);
    }
};
