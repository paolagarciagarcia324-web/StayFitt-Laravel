<?php

namespace App\Http\Controllers;

use App\Services\Institucional\InstitucionalRegistroService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegistroInstitucionalController extends Controller
{
    public function __construct(private InstitucionalRegistroService $registro)
    {
    }

    public function showEnlace(string $token): View|RedirectResponse
    {
        $enlace = $this->registro->resolverEnlace($token);
        if (! $enlace) {
            abort(404);
        }

        $error = $this->registro->enlaceValido($enlace);

        return view('registro-institucional.form', [
            'tipo' => 'enlace',
            'token' => $token,
            'institucion' => $enlace->institucion,
            'plan' => $enlace->plan,
            'error' => $error,
        ]);
    }

    public function showInvitacion(string $token): View|RedirectResponse
    {
        $inv = $this->registro->resolverInvitacion($token);
        if (! $inv) {
            abort(404);
        }

        $error = $this->registro->invitacionValida($inv);

        return view('registro-institucional.form', [
            'tipo' => 'invitacion',
            'token' => $token,
            'institucion' => $inv->institucion,
            'plan' => $inv->plan,
            'correo' => $inv->correo,
            'nombre_sugerido' => $inv->nombre_sugerido,
            'error' => $error,
        ]);
    }

    public function storeEnlace(Request $request, string $token): RedirectResponse
    {
        $enlace = $this->registro->resolverEnlace($token);
        if (! $enlace) {
            abort(404);
        }

        $data = $request->validate([
            'nombres' => ['required', 'string', 'max:80'],
            'apellidos' => ['required', 'string', 'max:80'],
            'correo' => ['required', 'email', 'max:150'],
            'documento' => ['nullable', 'string', 'max:30'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'ciudad' => ['nullable', 'string', 'max:80'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'acepto_terminos' => ['accepted'],
        ]);

        $this->registro->registrarDesdeEnlace($enlace, $data);

        return redirect()->route('login')->with('status', 'Cuenta creada. Inicia sesión con tu correo y contraseña.');
    }

    public function storeInvitacion(Request $request, string $token): RedirectResponse
    {
        $inv = $this->registro->resolverInvitacion($token);
        if (! $inv) {
            abort(404);
        }

        $data = $request->validate([
            'nombres' => ['required', 'string', 'max:80'],
            'apellidos' => ['required', 'string', 'max:80'],
            'correo' => ['required', 'email', 'max:150'],
            'documento' => ['nullable', 'string', 'max:30'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'ciudad' => ['nullable', 'string', 'max:80'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'acepto_terminos' => ['accepted'],
        ]);

        if (strtolower($data['correo']) !== strtolower($inv->correo)) {
            return back()->withErrors(['correo' => 'Debes registrarte con el correo de la invitación.']);
        }

        $this->registro->registrarDesdeInvitacion($inv, $data);

        return redirect()->route('login')->with('status', 'Invitación aceptada. Ya puedes iniciar sesión.');
    }
}
