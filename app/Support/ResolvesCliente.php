<?php

namespace App\Support;

use App\Models\Cliente;
use Illuminate\Support\Facades\Auth;

trait ResolvesCliente
{
    protected function resolveClienteOrAbort(): Cliente
    {
        $user = Auth::user();
        $cliente = $user?->cliente;

        if (! $cliente) {
            abort(403, 'No tienes perfil de cliente activo.');
        }

        return $cliente;
    }
}
