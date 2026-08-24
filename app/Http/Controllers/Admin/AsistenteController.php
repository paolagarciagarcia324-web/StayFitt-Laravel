<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AsistenteController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));

        $base = User::query()
            ->with('roles')
            ->whereHas('roles', function ($query) {
                $query->where('codigo', 'ASISTENTE_TECNICO')
                    ->orWhere('codigo_rol', 'ASISTENTE_TECNICO');
            });

        $total = (clone $base)->count();

        $asistentes = (clone $base)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('nombres', 'like', "%{$q}%")
                        ->orWhere('apellidos', 'like', "%{$q}%")
                        ->orWhere('correo', 'like', "%{$q}%")
                        ->orWhere('documento_identidad', 'like', "%{$q}%")
                        ->orWhereRaw("CONCAT(nombres, ' ', apellidos) LIKE ?", ["%{$q}%"]);
                });
            })
            ->orderBy('nombres')
            ->orderBy('apellidos')
            ->paginate(12)
            ->withQueryString();

        return view('admin.asistentes.index', compact('asistentes', 'q', 'total'));
    }
}
