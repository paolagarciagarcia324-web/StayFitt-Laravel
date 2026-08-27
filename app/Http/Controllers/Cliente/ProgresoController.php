<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\MetaCliente;
use App\Models\RegistroProgreso;
use App\Support\ResolvesCliente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProgresoController extends Controller
{
    use ResolvesCliente;

    public function index(): View
    {
        $cliente = $this->resolveClienteOrAbort();
        $registros = RegistroProgreso::query()
            ->where('id_cliente', $cliente->id_cliente)
            ->orderByDesc('fecha_registro')
            ->paginate(12);

        $ultimo = $registros->first();
        $metas = MetaCliente::query()
            ->where('id_cliente', $cliente->id_cliente)
            ->orderByDesc('id_meta')
            ->limit(6)
            ->get();

        $seriePeso = RegistroProgreso::query()
            ->where('id_cliente', $cliente->id_cliente)
            ->whereNotNull('peso_kg')
            ->orderBy('fecha_registro')
            ->limit(20)
            ->get(['fecha_registro', 'peso_kg']);

        return view('cliente.progreso.index', compact('cliente', 'registros', 'ultimo', 'metas', 'seriePeso'));
    }

    public function create(): View
    {
        $cliente = $this->resolveClienteOrAbort();

        return view('cliente.progreso.create', compact('cliente'));
    }

    public function store(Request $request): RedirectResponse
    {
        $cliente = $this->resolveClienteOrAbort();

        $data = $request->validate([
            'fecha_registro' => ['required', 'date'],
            'peso_kg' => ['nullable', 'numeric', 'min:20', 'max:400'],
            'estatura_cm' => ['nullable', 'numeric', 'min:100', 'max:250'],
            'porcentaje_grasa' => ['nullable', 'numeric', 'min:1', 'max:70'],
            'masa_muscular_kg' => ['nullable', 'numeric', 'min:1', 'max:200'],
            'cintura_cm' => ['nullable', 'numeric', 'min:30', 'max:200'],
            'cadera_cm' => ['nullable', 'numeric', 'min:30', 'max:200'],
            'brazo_cm' => ['nullable', 'numeric', 'min:10', 'max:80'],
            'nivel_energia' => ['nullable', 'integer', 'min:1', 'max:10'],
            'calidad_sueno' => ['nullable', 'integer', 'min:1', 'max:10'],
            'nivel_estres' => ['nullable', 'integer', 'min:1', 'max:10'],
            'agua_litros' => ['nullable', 'numeric', 'min:0', 'max:15'],
        ]);

        $peso = isset($data['peso_kg']) ? (float) $data['peso_kg'] : null;
        $estatura = isset($data['estatura_cm']) ? (float) $data['estatura_cm'] : (float) ($cliente->estatura_cm ?? 0);
        $imc = null;
        if ($peso && $estatura > 0) {
            $metros = $estatura / 100;
            $imc = round($peso / ($metros * $metros), 2);
        }

        RegistroProgreso::query()->create([
            ...$data,
            'id_cliente' => $cliente->id_cliente,
            'id_coach' => $cliente->id_coach,
            'id_plan_cliente' => $cliente->planActivo()?->id_plan_cliente,
            'estatura_cm' => $data['estatura_cm'] ?? $cliente->estatura_cm,
            'imc' => $imc,
            'es_linea_base' => false,
        ]);

        return redirect()->route('cliente.progreso.index')->with('success', 'Registro de progreso guardado.');
    }
}
