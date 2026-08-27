<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AsistenteController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));

        $base = $this->asistentesQuery();

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

        $candidatosCliente = User::query()
            ->with('cliente')
            ->whereHas('cliente')
            ->whereDoesntHave('roles', function ($query) {
                $query->where('codigo', 'ASISTENTE_TECNICO')
                    ->orWhere('codigo_rol', 'ASISTENTE_TECNICO');
            })
            ->orderBy('nombres')
            ->orderBy('apellidos')
            ->limit(150)
            ->get();

        return view('admin.asistentes.index', compact('asistentes', 'q', 'total', 'candidatosCliente'));
    }

    public function store(Request $request): RedirectResponse
    {
        $modo = $request->input('modo');
        $rol = $this->rolAsistente();

        if (! $rol) {
            return back()->withErrors(['modo' => 'No está configurado el rol Asistente Técnico.'])->withInput();
        }

        if ($modo === 'cliente') {
            $data = $request->validate([
                'modo' => ['required', Rule::in(['cliente'])],
                'id_user' => ['required', 'integer', 'exists:usuarios,id_user'],
            ]);

            $usuario = User::query()
                ->with('cliente')
                ->where('id_user', $data['id_user'])
                ->whereHas('cliente')
                ->firstOrFail();

            if ($this->asistentesQuery()->where('id_user', $usuario->id_user)->exists()) {
                return back()->withErrors(['id_user' => 'Ese cliente ya es asistente técnico.'])->withInput();
            }

            $usuario->roles()->syncWithoutDetaching([$rol->id_rol]);

            return redirect()
                ->route('admin.asistentes.index')
                ->with('asistente_ok', ['accion' => 'vinculado']);
        }

        if ($modo === 'externo') {
            $data = $request->validate([
                'modo' => ['required', Rule::in(['externo'])],
                'nombres' => ['required', 'string', 'max:120'],
                'apellidos' => ['required', 'string', 'max:120'],
                'correo' => ['required', 'email', 'max:190', 'unique:usuarios,correo'],
                'telefono' => ['nullable', 'string', 'max:40'],
                'documento_identidad' => ['required', 'string', 'max:40', 'unique:usuarios,documento_identidad'],
            ]);

            $correo = strtolower(trim($data['correo']));

            $usuario = User::create([
                'nombres' => $data['nombres'],
                'apellidos' => $data['apellidos'],
                'correo' => $correo,
                'email' => $correo,
                'telefono' => $data['telefono'] ?? null,
                'documento_identidad' => $data['documento_identidad'],
                'password_hash' => $data['documento_identidad'],
                'estado' => 'ACTIVO',
                'estado_cuenta' => 'ACTIVO',
                'acepto_terminos' => true,
                'fecha_aceptacion_terminos' => now(),
                'correo_verificado_en' => now(),
            ]);

            $usuario->roles()->syncWithoutDetaching([$rol->id_rol]);

            return redirect()
                ->route('admin.asistentes.index')
                ->with('asistente_ok', ['accion' => 'creado']);
        }

        return back()->withErrors(['modo' => 'Selecciona un modo de registro válido.'])->withInput();
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $usuario = $this->findAsistenteOrFail($id);

        $data = $request->validate([
            'nombres' => ['required', 'string', 'max:120'],
            'apellidos' => ['required', 'string', 'max:120'],
            'correo' => ['required', 'email', 'max:190'],
            'telefono' => ['nullable', 'string', 'max:40'],
            'documento_identidad' => ['nullable', 'string', 'max:40'],
            'estado' => ['required', 'string', 'in:ACTIVO,INACTIVO,SUSPENDIDO'],
        ]);

        $correo = strtolower(trim($data['correo']));
        $existe = User::query()
            ->where('id_user', '!=', $usuario->id_user)
            ->where(function ($q) use ($correo) {
                $q->where('correo', $correo)->orWhere('email', $correo);
            })
            ->exists();

        if ($existe) {
            return back()->withErrors(['correo' => 'Ya existe otro usuario con ese correo.'])->withInput();
        }

        $usuario->fill([
            'nombres' => $data['nombres'],
            'apellidos' => $data['apellidos'],
            'correo' => $correo,
            'email' => $correo,
            'telefono' => $data['telefono'] ?? null,
            'documento_identidad' => $data['documento_identidad'] ?? null,
            'estado' => $data['estado'],
            'estado_cuenta' => $data['estado'],
        ]);
        $usuario->save();

        return redirect()
            ->route('admin.asistentes.index', $request->only('q'))
            ->with('asistente_ok', ['accion' => 'actualizado']);
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $usuario = $this->findAsistenteOrFail($id);
        $rol = $this->rolAsistente();

        if ($rol) {
            $usuario->roles()->detach($rol->id_rol);
        }

        return redirect()
            ->route('admin.asistentes.index', $request->only('q'))
            ->with('asistente_ok', ['accion' => 'eliminado']);
    }

    private function asistentesQuery()
    {
        return User::query()
            ->with('roles')
            ->whereHas('roles', function ($query) {
                $query->where('codigo', 'ASISTENTE_TECNICO')
                    ->orWhere('codigo_rol', 'ASISTENTE_TECNICO');
            });
    }

    private function findAsistenteOrFail(int $id): User
    {
        return $this->asistentesQuery()->where('id_user', $id)->firstOrFail();
    }

    private function rolAsistente(): ?Rol
    {
        return Rol::query()
            ->where(function ($q) {
                $q->where('codigo', 'ASISTENTE_TECNICO')
                    ->orWhere('codigo_rol', 'ASISTENTE_TECNICO');
            })
            ->first();
    }
}
