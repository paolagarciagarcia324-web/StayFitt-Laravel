<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\RecuperacionContrasenaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function __construct(private RecuperacionContrasenaService $recuperacion) {}

    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'max:150'],
        ], [
            'email.required' => 'Ingresa el correo de tu cuenta.',
            'email.email' => 'El correo no tiene un formato válido.',
        ]);

        $this->recuperacion->solicitar(
            $request->string('email')->toString(),
            $request->ip()
        );

        return redirect()
            ->route('login')
            ->with('status', 'Si el correo está registrado, te enviamos un enlace para recuperar tu contraseña. Revisa tu bandeja e ingresa de nuevo cuando la hayas actualizado.');
    }
}
