<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminCoachRequest;
use App\Models\Coach;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CoachController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));

        $coaches = Coach::query()
            ->with(['usuario', 'clientes'])
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

        return view('admin.coaches.index', compact('coaches', 'q'));
    }

    public function store(AdminCoachRequest $request): RedirectResponse
    {
        $usuario = User::create([
            'nombres' => $request->nombres,
            'apellidos' => $request->apellidos,
            'correo' => $request->correo,
            'telefono' => $request->telefono,
            'documento_identidad' => $request->documento_identidad,
            'password_hash' => $request->documento_identidad,
            'estado' => 'ACTIVO',
            'acepto_terminos' => true,
            'fecha_aceptacion_terminos' => now(),
            'correo_verificado_en' => now(),
        ]);

        $rolCoach = Rol::where('codigo', 'COACH')->orWhere('codigo_rol', 'COACH')->value('id_rol');
        if ($rolCoach) {
            $usuario->roles()->syncWithoutDetaching([$rolCoach]);
        }

        Coach::create([
            'id_user' => $usuario->id_user,
            'codigo_coach' => $request->codigo_coach ?: 'COACH-'.str_pad((string) $usuario->id_user, 4, '0', STR_PAD_LEFT),
            'especialidad' => $request->especialidad,
            'biografia' => $request->biografia,
            'certificaciones' => $request->certificaciones,
            'estado_coach' => $request->estado_coach,
        ]);

        return redirect()
            ->route('admin.coaches.index')
            ->with('success', 'Coach creado. Acceso: correo + documento.');
    }
}
