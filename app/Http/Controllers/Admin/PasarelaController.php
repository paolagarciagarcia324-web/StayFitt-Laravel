<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracionPasarela;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PasarelaController extends Controller
{
    public function index(): View
    {
        $orden = ['BOLD' => 1, 'EPAYCO' => 2, 'MERCADOPAGO' => 3];
        $pasarelas = ConfiguracionPasarela::query()
            ->get()
            ->sortBy(fn ($pasarela) => $orden[$pasarela->codigo_pasarela] ?? 99)
            ->values();

        return view('admin.pasarelas.index', compact('pasarelas'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $pasarela = ConfiguracionPasarela::query()->findOrFail($id);

        $data = $request->validate([
            'modo' => ['required', 'in:SANDBOX,LIVE'],
            'activa' => ['nullable', 'boolean'],
        ]);

        $pasarela->modo = $data['modo'];
        $pasarela->activa = $request->boolean('activa');
        $pasarela->actualizado_en = now();
        $pasarela->save();

        return back()->with('success', 'Pasarela actualizada.');
    }
}
