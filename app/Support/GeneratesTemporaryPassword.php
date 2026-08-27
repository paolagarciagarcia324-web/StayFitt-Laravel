<?php

namespace App\Support;

use Illuminate\Support\Str;

final class GeneratesTemporaryPassword
{
    /**
     * Contraseña temporal legible (12 chars: letras + dígitos).
     */
    public static function make(int $length = 12): string
    {
        $base = Str::upper(Str::random(max(8, $length - 4)));
        $digits = (string) random_int(1000, 9999);

        return substr($base.$digits, 0, $length);
    }
}
