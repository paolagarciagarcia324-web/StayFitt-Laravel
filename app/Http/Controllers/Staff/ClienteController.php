<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\RegistroProgreso;
use App\Support\ResolvesStaffContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ClienteController extends Controller
{
    use ResolvesStaffContext;

    public function index(): View
    {
        $user = Auth::user();
        $coach = $this->resolveCoachForStaff($user);

        $clientes = $this->clientesApoyoQuery($coach)
            ->with(['usuario', 'planesCliente.plan'])
            ->whereIn('estado_cliente', ['ACTIVA', 'ACTIVO', 'PAUSADA', 'PAUSADO'])
            ->orderByDesc('actualizado_en')
            ->get()
            ->map(function ($cliente) {
                $cliente->ultimo_peso = RegistroProgreso::query()
                    ->where('id_cliente', $cliente->id_cliente)
                    ->orderByDesc('fecha_registro')
                    ->value('peso_kg');

                return $cliente;
            });

        return view('staff.clientes.index', compact('user', 'coach', 'clientes'));
    }

    public function show(int $id): View
    {
        $user = Auth::user();
        $coach = $this->resolveCoachForStaff($user);

        $cliente = $this->clientesApoyoQuery($coach)
            ->with(['usuario', 'planesCliente.plan', 'metas', 'registrosProgreso' => fn ($q) => $q->orderByDesc('fecha_registro')->limit(10)])
            ->where('id_cliente', $id)
            ->firstOrFail();

        $planEntrenamiento = \App\Models\PlanEntrenamiento::query()
            ->with('rutinas.ejercicios.ejercicio')
            ->where('id_coach', $coach->id_coach)
            ->where('id_cliente', $cliente->id_cliente)
            ->orderByDesc('creado_en')
            ->first();

        return view('staff.clientes.show', compact('user', 'coach', 'cliente', 'planEntrenamiento'));
    }

    public function observacion(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $coach = $this->resolveCoachForStaff($user);

        $data = $request->validate([
            'id_registro_progreso' => ['required', 'integer'],
            'observacion_coach' => ['required', 'string', 'max:1000'],
        ]);

        $registro = RegistroProgreso::query()->findOrFail($data['id_registro_progreso']);
        $this->clienteApoyoOrAbort($coach, (int) $registro->id_cliente);

        $registro->update([
            'observacion_coach' => '[Asistente '.$user->nombres.'] '.$data['observacion_coach'],
            'id_coach' => $coach->id_coach,
        ]);

        return back()->with('success', 'Observación registrada para el coach.');
    }
}
