<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracionSistema;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConfiguracionController extends Controller
{
    public function index(): View
    {
        $configs = ConfiguracionSistema::query()->orderBy('clave')->get();

        return view('admin.configuracion.index', compact('configs'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'valores' => ['required', 'array'],
            'valores.*' => ['nullable', 'string', 'max:5000'],
        ]);

        foreach ($data['valores'] as $id => $valor) {
            ConfiguracionSistema::query()
                ->where('id_config', (int) $id)
                ->update(['valor' => $valor]);
        }

        return back()->with('success', 'Configuración del sistema actualizada.');
    }
}
