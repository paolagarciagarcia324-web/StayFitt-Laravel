<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institucion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstitucionController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));

        $instituciones = Institucion::query()
            ->with([
                'clientes.usuario',
                'clientes.planesCliente.plan',
                'clientes.rachas',
            ])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('nombre', 'like', "%{$q}%")
                        ->orWhereHas('clientes.usuario', function ($u) use ($q) {
                            $u->where('nombres', 'like', "%{$q}%")
                                ->orWhere('apellidos', 'like', "%{$q}%");
                        });
                });
            })
            ->orderBy('nombre')
            ->get();

        return view('admin.instituciones.index', compact('instituciones', 'q'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'nit' => ['nullable', 'string', 'max:30', 'unique:instituciones,nit'],
            'contacto_nombre' => ['nullable', 'string', 'max:120'],
            'correo' => ['nullable', 'email', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'ciudad' => ['nullable', 'string', 'max:80'],
            'cupos_totales' => ['nullable', 'integer', 'min:0'],
            'convenio' => ['nullable', 'string', 'max:200'],
        ]);

        Institucion::create([
            ...$data,
            'estado' => 'ACTIVA',
            'cupos_totales' => $data['cupos_totales'] ?? 0,
            'cupos_usados' => 0,
        ]);

        return back()->with('success', 'Institución creada.');
    }
}
