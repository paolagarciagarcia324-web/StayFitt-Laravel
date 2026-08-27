<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Support\ResolvesCliente;
use Illuminate\View\View;

class PagoController extends Controller
{
    use ResolvesCliente;

    public function index(): View
    {
        $cliente = $this->resolveClienteOrAbort();
        $pagos = Pago::query()
            ->with(['plan'])
            ->where('id_cliente', $cliente->id_cliente)
            ->orderByDesc('creado_en')
            ->paginate(15);

        $resumen = [
            'pendientes' => Pago::query()->where('id_cliente', $cliente->id_cliente)->where('estado_pago', 'PENDIENTE')->count(),
            'validados' => Pago::query()->where('id_cliente', $cliente->id_cliente)->whereIn('estado_pago', ['VALIDADO', 'APROBADO'])->count(),
            'total' => (float) Pago::query()->where('id_cliente', $cliente->id_cliente)->whereIn('estado_pago', ['VALIDADO', 'APROBADO'])->sum('monto'),
        ];

        return view('cliente.pagos.index', compact('cliente', 'pagos', 'resumen'));
    }
}
