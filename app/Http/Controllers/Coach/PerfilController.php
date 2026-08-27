<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Support\ResolvesStaffCoach;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PerfilController extends Controller
{
    use ResolvesStaffCoach;

    public function show(): View
    {
        $coach = $this->resolveCoachOrAbort()->load('usuario');
        $hub = app(\App\Services\Coach\CoachHubService::class);

        return view('coach.perfil.show', [
            'coach' => $coach,
            'totalClientes' => $hub->clientesActivosCount($coach),
            'proximasSesiones' => $hub->proximasSesiones($coach, 3),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $coach = $this->resolveCoachOrAbort()->load('usuario');

        $data = $request->validate([
            'especialidad' => ['nullable', 'string', 'max:120'],
            'biografia' => ['nullable', 'string', 'max:2000'],
            'certificaciones' => ['nullable', 'string', 'max:500'],
            'foto_url' => ['nullable', 'url', 'max:500'],
            'telefono' => ['nullable', 'string', 'max:30'],
        ]);

        $coach->update([
            'especialidad' => $data['especialidad'] ?? $coach->especialidad,
            'biografia' => $data['biografia'] ?? $coach->biografia,
            'certificaciones' => $data['certificaciones'] ?? $coach->certificaciones,
            'foto_url' => $data['foto_url'] ?? $coach->foto_url,
        ]);

        if ($coach->usuario && array_key_exists('telefono', $data)) {
            $coach->usuario->update(['telefono' => $data['telefono']]);
        }

        return back()->with('success', 'Perfil profesional actualizado.');
    }
}
