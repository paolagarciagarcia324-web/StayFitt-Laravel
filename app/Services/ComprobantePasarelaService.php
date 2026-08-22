<?php

namespace App\Services;

use App\Models\Pago;
use App\Models\SolicitudCompra;
use Illuminate\Support\Facades\File;

class ComprobantePasarelaService
{
    /** Imagen demo realista de comprobante (public/). */
    public const DEMO_RECIBO = 'images/comprobantes/recibo-ejemplo.png';

    /**
     * Asigna comprobante visual a todo pago.
     * En demo usa foto realista de recibo; si ya hay archivo local válido, lo respeta
     * salvo SVG rotos o URLs externas.
     */
    public function asegurar(Pago $pago, ?SolicitudCompra $solicitud = null, ?string $pasarela = null): string
    {
        $pago->refresh();
        $demo = self::DEMO_RECIBO;
        File::ensureDirectoryExists(public_path('images/comprobantes'));

        if (! is_file(public_path($demo))) {
            // Fallback mínimo si falta el PNG
            $this->escribirSvgDemo(public_path($demo.'.svg'));
            $demo = self::DEMO_RECIBO.'.svg';
        }

        $path = trim((string) ($pago->comprobante_url ?? ''));
        $okLocal = $path !== ''
            && str_starts_with($path, 'images/')
            && is_file(public_path($path))
            && ! str_ends_with(strtolower($path), '.svg');

        if (! $okLocal) {
            $pago->update(['comprobante_url' => $demo]);
        }

        return $pago->fresh()->comprobanteSrc();
    }

    private function escribirSvgDemo(string $full): void
    {
        if (is_file($full)) {
            return;
        }
        File::put($full, '<svg xmlns="http://www.w3.org/2000/svg" width="600" height="800"><rect fill="#121a2b" width="100%" height="100%"/><text x="40" y="80" fill="#2ef5e6" font-size="28">FigueFit Comprobante</text></svg>');
    }
}
