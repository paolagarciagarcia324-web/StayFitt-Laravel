<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institucion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstitucionController extends Controller
{
    public function index(): View
    {
        $instituciones = Institucion::orderBy('nombre')->get();

        return view('admin.instituciones.index', compact('instituciones'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'codigo_institucion' => ['nullable', 'string', 'max:20'],
            'nit' => ['nullable', 'string', 'max:50'],
            'correo_contacto' => ['nullable', 'email', 'max:150'],
            'telefono_contacto' => ['nullable', 'string', 'max:30'],
            'ciudad' => ['nullable', 'string', 'max:100'],
        ]);

        $data['estado'] = 'ACTIVA';
        Institucion::create($data);

        return back()->with('success', 'Institución creada.');
    }
}
