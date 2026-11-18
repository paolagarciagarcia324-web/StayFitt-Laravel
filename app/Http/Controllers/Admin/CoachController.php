<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminCoachRequest;
use App\Models\Cliente;
use App\Models\Coach;
use App\Models\Rol;
use App\Models\User;
use App\Support\GeneratesTemporaryPassword;
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
            ->paginate(10)
            ->withQueryString();

        return view('admin.coaches.index', compact('coaches', 'q'));
    }

    public function store(AdminCoachRequest $request): RedirectResponse
    {
        $tempPassword = GeneratesTemporaryPassword::make();

        $usuario = User::create([
            'nombres' => $request->nombres,
            'apellidos' => $request->apellidos,
            'correo' => $request->correo,
            'telefono' => $request->telefono,
            'documento_identidad' => $request->documento_identidad,
            'estado' => 'ACTIVO',
            'acepto_terminos' => true,
            'fecha_aceptacion_terminos' => now(),
            'correo_verificado_en' => now(),
        ]);
        $usuario->assignPassword($tempPassword);
        $usuario->save();

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
            ->with('success', 'Coach creado correctamente.')
            ->with('coach_temp_password', $tempPassword);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $coach = Coach::query()->with('usuario')->findOrFail($id);

        $data = $request->validate([
            'especialidad' => ['required', 'string', 'max:120'],
            'biografia' => ['nullable', 'string', 'max:2000'],
            'certificaciones' => ['nullable', 'string', 'max:500'],
            'estado_coach' => ['required', 'in:ACTIVO,INACTIVO,VACACIONES,SUSPENDIDO'],
            'telefono' => ['nullable', 'string', 'max:30'],
        ]);

        $coach->update([
            'especialidad' => $data['especialidad'],
            'biografia' => $data['biografia'] ?? null,
            'certificaciones' => $data['certificaciones'] ?? null,
            'estado_coach' => $data['estado_coach'],
        ]);

        if ($coach->usuario) {
            $coach->usuario->update(['telefono' => $data['telefono'] ?? $coach->usuario->telefono]);
        }

        return back()->with('success', 'Coach actualizado.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $coach = Coach::query()->with('usuario')->findOrFail($id);

        $coach->update(['estado_coach' => 'INACTIVO']);

        Cliente::query()
            ->where(function ($query) use ($coach) {
                $query->where('id_coach', $coach->id_coach)
                    ->orWhere('id_coach_asignado', $coach->id_coach);
            })
            ->update([
                'id_coach' => null,
                'id_coach_asignado' => null,
            ]);

        if ($coach->usuario) {
            $coach->usuario->update(['estado' => 'INACTIVO', 'estado_cuenta' => 'INACTIVO']);
        }

        return back()->with('success', 'Coach desactivado correctamente.');
    }
}
