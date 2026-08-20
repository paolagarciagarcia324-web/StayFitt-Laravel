<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DescuentoCupon;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CuponController extends Controller
{
    public function index(): View
    {
        $cupones = DescuentoCupon::query()->with('plan')->orderByDesc('creado_en')->paginate(20);
        $planes = Plan::query()->orderBy('nombre')->get(['id_plan', 'nombre']);

        return view('admin.cupones.index', compact('cupones', 'planes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'codigo' => ['required', 'string', 'max:40', 'unique:descuentos_cupones,codigo'],
            'tipo' => ['required', 'in:PORCENTAJE,MONTO'],
            'valor' => ['required', 'numeric', 'min:0'],
            'id_plan' => ['nullable', 'integer', 'exists:planes,id_plan'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after:fecha_inicio'],
            'usos_maximos' => ['nullable', 'integer', 'min:1'],
            'estado' => ['required', 'in:ACTIVO,INACTIVO'],
        ]);

        DescuentoCupon::create([
            'codigo' => strtoupper($data['codigo']),
            'tipo' => $data['tipo'],
            'tipo_descuento' => $data['tipo'],
            'valor' => $data['valor'],
            'id_plan' => $data['id_plan'] ?? null,
            'fecha_inicio' => $data['fecha_inicio'],
            'fecha_fin' => $data['fecha_fin'],
            'usos_maximos' => $data['usos_maximos'] ?? null,
            'uso_maximo' => $data['usos_maximos'] ?? null,
            'usos_actuales' => 0,
            'estado' => $data['estado'],
            'estado_cupon' => $data['estado'],
            'creado_por' => Auth::id(),
        ]);

        return back()->with('success', 'Cupón creado.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $cupon = DescuentoCupon::query()->findOrFail($id);

        $data = $request->validate([
            'valor' => ['required', 'numeric', 'min:0'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after:fecha_inicio'],
            'usos_maximos' => ['nullable', 'integer', 'min:1'],
            'estado' => ['required', 'in:ACTIVO,INACTIVO'],
        ]);

        $cupon->update([
            'valor' => $data['valor'],
            'fecha_inicio' => $data['fecha_inicio'],
            'fecha_fin' => $data['fecha_fin'],
            'usos_maximos' => $data['usos_maximos'] ?? null,
            'uso_maximo' => $data['usos_maximos'] ?? null,
            'estado' => $data['estado'],
            'estado_cupon' => $data['estado'],
        ]);

        return back()->with('success', 'Cupón actualizado.');
    }
}
