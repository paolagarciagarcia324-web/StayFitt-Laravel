<?php

namespace App\Http\Controllers\Institucional;

use App\Http\Controllers\Controller;
use App\Support\ResolvesInstitucionContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InstitucionContextController extends Controller
{
    use ResolvesInstitucionContext;

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if (! $user->tieneRol('ADMIN')) {
            abort(403);
        }

        $data = $request->validate([
            'id_institucion' => ['required', 'integer', 'exists:instituciones,id_institucion'],
        ]);

        session(['institucional_id' => (int) $data['id_institucion']]);

        return back()->with('institucional_ok', 'Institución de trabajo actualizada.');
    }
}
