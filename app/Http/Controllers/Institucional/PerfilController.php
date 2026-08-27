<?php

namespace App\Http\Controllers\Institucional;

use App\Http\Controllers\Controller;
use App\Support\ResolvesInstitucionContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PerfilController extends Controller
{
    use ResolvesInstitucionContext;

    public function show(): View
    {
        $user = auth()->user();
        $institucion = $this->resolveInstitucionForUser($user);
        $institucion->load(['planContratado', 'responsable']);

        return view('institucional.perfil.show', compact('user', 'institucion'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $institucion = $this->resolveInstitucionForUser($user);
        $this->assertInstitucionOwnership($institucion, $user);

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'tipo_institucion' => ['nullable', 'string', 'max:80', 'in:EDUCATIVA,EMPRESA,GIMNASIO,GOBIERNO,OTRO'],
            'nit' => ['nullable', 'string', 'max:30'],
            'direccion' => ['nullable', 'string', 'max:200'],
            'ciudad' => ['nullable', 'string', 'max:80'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'correo' => ['nullable', 'email', 'max:150'],
            'contacto_nombre' => ['nullable', 'string', 'max:120'],
            'contacto_cargo' => ['nullable', 'string', 'max:120'],
            'logo_url' => ['nullable', 'url', 'max:500'],
        ]);

        $institucion->update(array_merge($data, ['actualizado_en' => now()]));

        return back()->with('institucional_ok', 'Perfil institucional actualizado.');
    }
}
