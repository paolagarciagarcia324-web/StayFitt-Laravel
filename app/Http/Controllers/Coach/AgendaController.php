<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\Sesion;
use App\Services\Coach\CoachHubService;
use App\Support\ResolvesStaffCoach;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgendaController extends Controller
{
    use ResolvesStaffCoach;

    public function index(CoachHubService $hub): View
    {
        $coach = $this->resolveCoachOrAbort();

        $proximas = $hub->proximasSesiones($coach, 20);
        $pasadas = Sesion::query()
            ->where('id_coach', $coach->id_coach)
            ->where('fecha', '<', now()->toDateString())
            ->orderByDesc('fecha')
            ->limit(10)
            ->get();

        return view('coach.agenda.index', compact('coach', 'proximas', 'pasadas'));
    }

    public function store(Request $request): RedirectResponse
    {
        $coach = $this->resolveCoachOrAbort();

        $data = $request->validate([
            'titulo' => ['required', 'string', 'max:120'],
            'fecha' => ['required', 'date', 'after_or_equal:today'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'tipo' => ['nullable', 'string', 'max:40'],
            'modalidad' => ['nullable', 'string', 'max:20'],
            'cupo_maximo' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        Sesion::create([
            'id_coach' => $coach->id_coach,
            'tipo' => $data['tipo'] ?? 'ENTRENAMIENTO',
            'titulo' => $data['titulo'],
            'fecha' => $data['fecha'],
            'hora_inicio' => $data['hora_inicio'],
            'hora_fin' => $data['hora_fin'],
            'modalidad' => strtoupper($data['modalidad'] ?? 'PRESENCIAL'),
            'cupo_maximo' => $data['cupo_maximo'] ?? 8,
            'cupo_reservado' => 0,
            'estado' => 'PROGRAMADA',
        ]);

        return back()->with('success', 'Sesión programada.');
    }
}
