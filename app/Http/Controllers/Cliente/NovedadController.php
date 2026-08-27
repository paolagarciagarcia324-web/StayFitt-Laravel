<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\NovedadWelcome;
use App\Support\ResolvesCliente;
use Illuminate\View\View;

class NovedadController extends Controller
{
    use ResolvesCliente;

    public function index(): View
    {
        $cliente = $this->resolveClienteOrAbort();
        $novedades = NovedadWelcome::query()->visibles()->paginate(12);

        return view('cliente.novedades.index', compact('cliente', 'novedades'));
    }
}
