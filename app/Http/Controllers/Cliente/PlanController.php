<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Support\ResolvesCliente;
use Illuminate\View\View;

class PlanController extends Controller
{
    use ResolvesCliente;

    public function index(): View
    {
        $cliente = $this->resolveClienteOrAbort()->load(['accesos.modulo', 'planesCliente.plan', 'coach.usuario']);
        $planActivo = $cliente->planActivo();
        $historial = $cliente->planesCliente()->with('plan')->orderByDesc('fecha_fin')->get();
        $accesos = $cliente->accesos()->with('modulo')->orderByDesc('fecha_fin')->get();

        return view('cliente.plan.index', compact('cliente', 'planActivo', 'historial', 'accesos'));
    }
}
