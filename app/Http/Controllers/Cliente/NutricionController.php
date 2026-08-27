<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\PlanNutricional;
use App\Support\ResolvesCliente;
use Illuminate\View\View;

class NutricionController extends Controller
{
    use ResolvesCliente;

    public function index(): View
    {
        $cliente = $this->resolveClienteOrAbort();
        $planes = PlanNutricional::query()
            ->with(['comidas', 'coach.usuario'])
            ->where('id_cliente', $cliente->id_cliente)
            ->orderByDesc('id_plan_nutricional')
            ->get();

        $activo = $planes->first(fn ($p) => in_array(strtoupper((string) $p->estado), ['ACTIVO', 'PUBLICADO', 'APROBADO'], true))
            ?? $planes->first();

        return view('cliente.nutricion.index', compact('cliente', 'planes', 'activo'));
    }

    public function show(int $id): View
    {
        $cliente = $this->resolveClienteOrAbort();
        $plan = PlanNutricional::query()
            ->with(['comidas', 'coach.usuario'])
            ->where('id_cliente', $cliente->id_cliente)
            ->findOrFail($id);

        return view('cliente.nutricion.show', compact('cliente', 'plan'));
    }
}
