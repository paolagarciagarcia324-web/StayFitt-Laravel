<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClienteController extends Controller
{
    public function index(): View
    {
        return redirect()->route('admin.clientes.virtuales');
    }

    public function virtuales(Request $request): View
    {
        return $this->listar($request, 'VIRTUAL', 'virtuales', 'Clientes virtuales', 'Individual e institucional');
    }

    public function presenciales(Request $request): View
    {
        return $this->listar($request, 'PRESENCIAL', 'presenciales', 'Clientes presenciales', 'Inscripción en sala Campoalegre');
    }

    private function listar(Request $request, string $modalidad, string $view, string $title, string $subtitle): View
    {
        $q = trim((string) $request->get('q', ''));

        $clientes = Cliente::query()
            ->with(['usuario', 'planesCliente.plan', 'rachas', 'institucion', 'horario'])
            ->where('modalidad', $modalidad)
            ->when($q !== '', function ($query) use ($q) {
                $query->whereHas('usuario', function ($inner) use ($q) {
                    $inner->where('nombres', 'like', "%{$q}%")
                        ->orWhere('apellidos', 'like', "%{$q}%")
                        ->orWhereRaw("CONCAT(nombres, ' ', apellidos) LIKE ?", ["%{$q}%"]);
                });
            })
            ->orderByDesc('creado_en')
            ->paginate(20)
            ->withQueryString();

        return view("admin.clientes.{$view}", compact('clientes', 'title', 'subtitle', 'q'));
    }
}
