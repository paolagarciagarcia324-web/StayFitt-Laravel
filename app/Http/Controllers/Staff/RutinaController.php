<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\PlanEntrenamiento;
use App\Models\PropuestaPlan;
use App\Services\Coach\EntrenamientoCoachService;
use App\Support\ResolvesStaffContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RutinaController extends Controller
{
    use ResolvesStaffContext;

    public function index(): View
    {
        $user = Auth::user();
        $coach = $this->resolveCoachForStaff($user);

        $planes = PlanEntrenamiento::query()
            ->with(['cliente.usuario', 'rutinas'])
            ->where('id_coach', $coach->id_coach)
            ->orderByDesc('creado_en')
            ->get();

        $propuestas = PropuestaPlan::query()
            ->where('id_coach', $coach->id_coach)
            ->whereIn('estado', [
                PropuestaPlan::ESTADO_BORRADOR_ENTRENAMIENTO,
                PropuestaPlan::ESTADO_BORRADOR_NUTRICION,
                PropuestaPlan::ESTADO_PENDIENTE_REVISION,
                PropuestaPlan::ESTADO_RECHAZADO,
            ])
            ->when($user->tieneRol('ASISTENTE_TECNICO') && ! $user->tieneRol('ADMIN'), fn ($q) => $q->where('creado_por', $user->id_user))
            ->orderByDesc('id_propuesta')
            ->limit(12)
            ->get();

        $clientes = $this->clientesApoyoQuery($coach)
            ->with('usuario')
            ->whereIn('estado_cliente', ['ACTIVA', 'ACTIVO'])
            ->orderBy('id_cliente')
            ->get();

        return view('staff.rutinas.index', compact('user', 'coach', 'planes', 'propuestas', 'clientes'));
    }

    public function storeBorrador(Request $request, EntrenamientoCoachService $service): RedirectResponse
    {
        $user = Auth::user();
        $coach = $this->resolveCoachForStaff($user);

        $data = $request->validate([
            'id_cliente' => ['required', 'integer'],
            'nombre' => ['required', 'string', 'max:120'],
            'objetivo' => ['nullable', 'string', 'max:120'],
        ]);

        $cliente = $this->clienteApoyoOrAbort($coach, (int) $data['id_cliente']);
        $plan = $service->crearPlan($coach, $cliente, [
            ...$data,
            'estado' => 'BORRADOR',
        ]);

        return redirect()
            ->route('staff.rutinas.index')
            ->with('success', 'Borrador de rutina creado (#'.$plan->id_plan_entrenamiento.'). El coach debe aprobar.');
    }
}
