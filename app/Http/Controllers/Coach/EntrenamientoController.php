<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\Ejercicio;
use App\Models\PlanEntrenamiento;
use App\Models\Rutina;
use App\Services\Coach\CoachHubService;
use App\Services\Coach\EntrenamientoCoachService;
use App\Support\ResolvesCoachClientes;
use App\Support\ResolvesStaffCoach;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EntrenamientoController extends Controller
{
    use ResolvesCoachClientes;
    use ResolvesStaffCoach;

    public function index(CoachHubService $hub): View
    {
        $coach = $this->resolveCoachOrAbort();
        $planes = $hub->planesDelCoach($coach);
        $clientes = $this->clientesDelCoachQuery($coach)
            ->with('usuario')
            ->whereIn('estado_cliente', ['ACTIVA', 'ACTIVO'])
            ->orderBy('id_cliente')
            ->get();

        return view('coach.entrenamiento.index', compact('coach', 'planes', 'clientes'));
    }

    public function show(int $id): View
    {
        $coach = $this->resolveCoachOrAbort();

        $plan = PlanEntrenamiento::query()
            ->with(['cliente.usuario', 'rutinas.ejercicios.ejercicio'])
            ->where('id_coach', $coach->id_coach)
            ->findOrFail($id);

        $ejerciciosCatalogo = Ejercicio::query()->orderBy('nombre')->limit(200)->get();

        return view('coach.entrenamiento.show', compact('coach', 'plan', 'ejerciciosCatalogo'));
    }

    public function storePlan(Request $request, EntrenamientoCoachService $service): RedirectResponse
    {
        $coach = $this->resolveCoachOrAbort();

        $data = $request->validate([
            'id_cliente' => ['required', 'integer'],
            'nombre' => ['required', 'string', 'max:120'],
            'objetivo' => ['nullable', 'string', 'max:120'],
        ]);

        $cliente = $this->clienteDelCoachOrAbort($coach, (int) $data['id_cliente']);
        $plan = $service->crearPlan($coach, $cliente, $data);

        return redirect()
            ->route('coach.entrenamiento.show', $plan->id_plan_entrenamiento)
            ->with('success', 'Plan de entrenamiento creado.');
    }

    public function storeRutina(Request $request, int $id, EntrenamientoCoachService $service): RedirectResponse
    {
        $coach = $this->resolveCoachOrAbort();

        $plan = PlanEntrenamiento::query()
            ->where('id_coach', $coach->id_coach)
            ->findOrFail($id);

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'nivel' => ['nullable', 'string', 'max:40'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'dia_semana' => ['nullable', 'integer', 'min:1', 'max:7'],
            'duracion_min' => ['nullable', 'integer', 'min:5', 'max:240'],
            'ejercicios' => ['nullable', 'array'],
            'ejercicios.*.id_ejercicio' => ['nullable', 'integer'],
            'ejercicios.*.nombre_ejercicio' => ['nullable', 'string', 'max:120'],
            'ejercicios.*.series' => ['nullable', 'integer', 'min:1', 'max:20'],
            'ejercicios.*.repeticiones' => ['nullable', 'string', 'max:40'],
            'ejercicios.*.descanso_seg' => ['nullable', 'integer', 'min:0', 'max:600'],
        ]);

        $service->crearRutina($plan, $data, $data['ejercicios'] ?? []);

        return back()->with('success', 'Rutina agregada al plan.');
    }

    public function updateRutina(Request $request, int $planId, int $rutinaId): RedirectResponse
    {
        $coach = $this->resolveCoachOrAbort();

        $plan = PlanEntrenamiento::query()
            ->where('id_coach', $coach->id_coach)
            ->findOrFail($planId);

        $rutina = Rutina::query()
            ->where('id_plan_entrenamiento', $plan->id_plan_entrenamiento)
            ->findOrFail($rutinaId);

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'duracion_min' => ['nullable', 'integer', 'min:5', 'max:240'],
            'estado' => ['nullable', 'string', 'in:ACTIVA,PAUSADA,ARCHIVADA'],
        ]);

        $rutina->update($data);

        return back()->with('success', 'Rutina actualizada.');
    }
}
