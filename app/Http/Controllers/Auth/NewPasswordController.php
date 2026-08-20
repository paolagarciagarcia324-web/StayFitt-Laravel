<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\RecuperacionContrasenaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    public function __construct(private RecuperacionContrasenaService $recuperacion) {}

    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email', 'max:150'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'email.required' => 'Ingresa el correo de tu cuenta.',
            'password.required' => 'Define una nueva contraseña.',
            'password.confirmed' => 'La confirmación no coincide con la contraseña.',
        ]);

        $ok = $this->recuperacion->restablecer(
            $request->string('token')->toString(),
            $request->string('email')->toString(),
            $request->string('password')->toString()
        );

        if (! $ok) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'El enlace no es válido o ya venció. Solicita uno nuevo.']);
        }

        return redirect()
            ->route('login')
            ->with('status', 'Tu contraseña ya está actualizada. Inicia sesión con tu nuevo acceso.');
    }
}
