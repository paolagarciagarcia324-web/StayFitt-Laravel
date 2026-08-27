<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\PropuestaPlan;
use App\Services\Staff\StaffHubService;
use App\Support\ResolvesStaffContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ComunicacionController extends Controller
{
    use ResolvesStaffContext;

    public function index(StaffHubService $hub): View
    {
        $user = Auth::user();
        $coach = $this->resolveCoachForStaff($user);

        $propuestasConNotas = PropuestaPlan::query()
            ->with(['coach.usuario', 'creador'])
            ->where('id_coach', $coach->id_coach)
            ->where(function ($q) {
                $q->whereNotNull('notas_coach')
                    ->orWhereNotNull('notas_admin')
                    ->orWhereNotNull('motivo_rechazo');
            })
            ->orderByDesc('actualizado_en')
            ->limit(15)
            ->get();

        $propuestasSelect = PropuestaPlan::query()
            ->where('id_coach', $coach->id_coach)
            ->orderByDesc('id_propuesta')
            ->limit(30)
            ->get();

        return view('staff.comunicacion.index', [
            'user' => $user,
            'coach' => $coach,
            'coaches' => $hub->coachesActivos(),
            'propuestasConNotas' => $propuestasConNotas,
            'propuestasSelect' => $propuestasSelect,
        ]);
    }

    public function nota(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $coach = $this->resolveCoachForStaff($user);

        $data = $request->validate([
            'id_propuesta' => ['required', 'integer'],
            'nota' => ['required', 'string', 'max:500'],
        ]);

        $propuesta = PropuestaPlan::query()
            ->where('id_coach', $coach->id_coach)
            ->findOrFail($data['id_propuesta']);

        $prefijo = '[Asistente '.trim($user->nombres).' · '.now()->format('d/m H:i').'] ';
        $propuesta->update([
            'notas_coach' => trim(($propuesta->notas_coach ? $propuesta->notas_coach."\n" : '').$prefijo.$data['nota']),
            'actualizado_en' => now(),
        ]);

        return back()->with('success', 'Nota operativa enviada al coach.');
    }
}
