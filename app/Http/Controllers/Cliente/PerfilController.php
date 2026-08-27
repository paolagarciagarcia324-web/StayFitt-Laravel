<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\PreferenciaCliente;
use App\Support\ResolvesCliente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PerfilController extends Controller
{
    use ResolvesCliente;

    public function edit(): View|RedirectResponse
    {
        $user = Auth::user();
        $cliente = $this->resolveClienteOrAbort();

        if (strtoupper((string) $cliente->modalidad) === 'PRESENCIAL') {
            return redirect()->route('cliente.presencial.perfil');
        }

        $preferencias = PreferenciaCliente::query()->firstOrCreate(
            ['id_cliente' => $cliente->id_cliente],
            [
                'notif_email' => true,
                'notif_push' => true,
                'notif_whatsapp' => false,
                'idioma' => 'es',
            ]
        );

        return view('cliente.perfil.edit', compact('user', 'cliente', 'preferencias'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $cliente = $this->resolveClienteOrAbort();

        $data = $request->validate([
            'nombres' => ['required', 'string', 'max:80'],
            'apellidos' => ['required', 'string', 'max:80'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'ciudad' => ['nullable', 'string', 'max:80'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'genero' => ['nullable', 'string', 'max:20'],
            'objetivo_principal' => ['nullable', 'string', 'max:80'],
            'alergias' => ['nullable', 'string', 'max:500'],
            'condiciones_medicas' => ['nullable', 'string', 'max:500'],
            'lesiones' => ['nullable', 'string', 'max:500'],
            'medicamentos' => ['nullable', 'string', 'max:500'],
            'contacto_emergencia_nombre' => ['nullable', 'string', 'max:120'],
            'contacto_emergencia_telefono' => ['nullable', 'string', 'max:30'],
            'peso_inicial_kg' => ['nullable', 'numeric', 'min:20', 'max:400'],
            'estatura_cm' => ['nullable', 'numeric', 'min:100', 'max:250'],
            'notif_email' => ['nullable', 'boolean'],
            'notif_push' => ['nullable', 'boolean'],
            'notif_whatsapp' => ['nullable', 'boolean'],
            'horario_preferido' => ['nullable', 'string', 'max:40'],
            'idioma' => ['nullable', 'string', 'max:10'],
        ]);

        $user->update([
            'nombres' => $data['nombres'],
            'apellidos' => $data['apellidos'],
            'telefono' => $data['telefono'] ?? $user->telefono,
        ]);

        $cliente->update([
            'ciudad' => $data['ciudad'] ?? null,
            'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
            'genero' => $data['genero'] ?? null,
            'objetivo_principal' => $data['objetivo_principal'] ?? null,
            'alergias' => $data['alergias'] ?? null,
            'condiciones_medicas' => $data['condiciones_medicas'] ?? null,
            'lesiones' => $data['lesiones'] ?? null,
            'medicamentos' => $data['medicamentos'] ?? null,
            'contacto_emergencia_nombre' => $data['contacto_emergencia_nombre'] ?? null,
            'contacto_emergencia_telefono' => $data['contacto_emergencia_telefono'] ?? null,
            'peso_inicial_kg' => $data['peso_inicial_kg'] ?? null,
            'estatura_cm' => $data['estatura_cm'] ?? null,
        ]);

        PreferenciaCliente::query()->updateOrCreate(
            ['id_cliente' => $cliente->id_cliente],
            [
                'notif_email' => $request->boolean('notif_email'),
                'notif_push' => $request->boolean('notif_push'),
                'notif_whatsapp' => $request->boolean('notif_whatsapp'),
                'horario_preferido' => $data['horario_preferido'] ?? null,
                'idioma' => $data['idioma'] ?? 'es',
            ]
        );

        return back()->with('success', 'Perfil actualizado.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $data = $request->validate([
            'password_actual' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if (! $user->validatePassword($data['password_actual'])) {
            return back()->withErrors(['password_actual' => 'La contraseña actual no es correcta.']);
        }

        $user->assignPassword($data['password']);
        $user->save();

        return back()->with('success', 'Contraseña actualizada.');
    }
}
