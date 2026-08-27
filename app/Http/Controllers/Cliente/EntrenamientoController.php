<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\PlanEntrenamiento;
use App\Models\RegistroRutina;
use App\Models\Rutina;
use App\Support\ResolvesCliente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EntrenamientoController extends Controller
{
    use ResolvesCliente;

    public function index(): View
    {
        $cliente = $this->resolveClienteOrAbort();
        $planes = PlanEntrenamiento::query()
            ->with(['rutinas.ejercicios.ejercicio', 'coach.usuario'])
            ->where('id_cliente', $cliente->id_cliente)
            ->whereIn('estado', ['ACTIVO', 'PUBLICADO', 'APROBADO', 'VALIDADO'])
            ->orderByDesc('id_plan_entrenamiento')
            ->get();

        if ($planes->isEmpty()) {
            $planes = PlanEntrenamiento::query()
                ->with(['rutinas.ejercicios.ejercicio', 'coach.usuario'])
                ->where('id_cliente', $cliente->id_cliente)
                ->orderByDesc('id_plan_entrenamiento')
                ->limit(3)
                ->get();
        }

        $registrosRecientes = RegistroRutina::query()
            ->with('rutina')
            ->where('id_cliente', $cliente->id_cliente)
            ->orderByDesc('fecha_ejecucion')
            ->limit(10)
            ->get();

        $completadasSemana = RegistroRutina::query()
            ->where('id_cliente', $cliente->id_cliente)
            ->where('completada', true)
            ->where('fecha_ejecucion', '>=', now()->subDays(7)->toDateString())
            ->count();

        return view('cliente.entrenamiento.index', compact('cliente', 'planes', 'registrosRecientes', 'completadasSemana'));
    }

    public function showRutina(int $id): View
    {
        $cliente = $this->resolveClienteOrAbort();
        $rutina = Rutina::query()
            ->with(['ejercicios.ejercicio', 'planEntrenamiento'])
            ->findOrFail($id);

        if ((int) $rutina->planEntrenamiento?->id_cliente !== (int) $cliente->id_cliente) {
            abort(403);
        }

        $historial = RegistroRutina::query()
            ->where('id_cliente', $cliente->id_cliente)
            ->where('id_rutina', $rutina->id_rutina)
            ->orderByDesc('fecha_ejecucion')
            ->limit(8)
            ->get();

        return view('cliente.entrenamiento.rutina', compact('cliente', 'rutina', 'historial'));
    }

    public function completarRutina(Request $request, int $id): RedirectResponse
    {
        $cliente = $this->resolveClienteOrAbort();
        $rutina = Rutina::query()->with('planEntrenamiento')->findOrFail($id);

        if ((int) $rutina->planEntrenamiento?->id_cliente !== (int) $cliente->id_cliente) {
            abort(403);
        }

        $data = $request->validate([
            'dificultad' => ['nullable', 'integer', 'min:1', 'max:10'],
            'observacion_cliente' => ['nullable', 'string', 'max:500'],
        ]);

        RegistroRutina::query()->create([
            'id_cliente' => $cliente->id_cliente,
            'id_rutina' => $rutina->id_rutina,
            'fecha_ejecucion' => now()->toDateString(),
            'completada' => true,
            'dificultad' => $data['dificultad'] ?? null,
            'observacion_cliente' => $data['observacion_cliente'] ?? null,
        ]);

        return back()->with('success', 'Rutina marcada como completada. ¡Buen trabajo!');
    }
}
