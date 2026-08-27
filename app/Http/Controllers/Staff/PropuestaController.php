<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\PropuestaPlan;
use App\Support\ResolvesStaffContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PropuestaController extends Controller
{
    use ResolvesStaffContext;

    public function index(Request $request): View
    {
        $user = Auth::user();
        $coach = $this->resolveCoachForStaff($user);
        $estado = trim((string) $request->get('estado', ''));

        $propuestas = PropuestaPlan::query()
            ->with(['coach.usuario', 'programa', 'creador'])
            ->where('id_coach', $coach->id_coach)
            ->when($estado !== '', fn ($q) => $q->where('estado', $estado))
            ->when($user->tieneRol('ASISTENTE_TECNICO') && ! $user->tieneRol('ADMIN'), function ($q) use ($user) {
                $q->where(function ($inner) use ($user) {
                    $inner->where('creado_por', $user->id_user)
                        ->orWhereIn('estado', [
                            PropuestaPlan::ESTADO_PENDIENTE_REVISION,
                            PropuestaPlan::ESTADO_RECHAZADO,
                        ]);
                });
            })
            ->orderByDesc('id_propuesta')
            ->get();

        return view('staff.propuestas.index', compact('user', 'coach', 'propuestas', 'estado'));
    }
}
