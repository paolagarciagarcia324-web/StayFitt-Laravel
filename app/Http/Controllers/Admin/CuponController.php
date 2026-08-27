<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminCuponStoreRequest;
use App\Http\Requests\Admin\AdminCuponUpdateRequest;
use App\Models\DescuentoCupon;
use App\Models\Plan;
use App\Models\UsoCupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CuponController extends Controller
{
    public function index(): View
    {
        $cupones = DescuentoCupon::query()->with('plan')->orderByDesc('creado_en')->paginate(10)->withQueryString();
        $planes = Plan::query()->orderBy('nombre')->get(['id_plan', 'nombre']);

        return view('admin.cupones.index', compact('cupones', 'planes'));
    }

    public function store(AdminCuponStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();

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

    public function update(AdminCuponUpdateRequest $request, int $id): RedirectResponse
    {
        $cupon = DescuentoCupon::query()->findOrFail($id);
        $data = $request->validated();

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

    public function destroy(int $id): RedirectResponse
    {
        $cupon = DescuentoCupon::query()->findOrFail($id);

        $cupon->update([
            'estado' => 'INACTIVO',
            'estado_cupon' => 'INACTIVO',
        ]);

        return back()->with('success', 'Cupón desactivado.');
    }

    public function usos(int $id): View
    {
        $cupon = DescuentoCupon::query()->with('plan')->findOrFail($id);
        $usos = UsoCupon::query()
            ->with('solicitud')
            ->where('id_descuento', $cupon->id_descuento)
            ->orderByDesc('usado_en')
            ->paginate(15);

        return view('admin.cupones.usos', compact('cupon', 'usos'));
    }
}
