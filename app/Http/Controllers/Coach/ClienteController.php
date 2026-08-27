<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\RegistroProgreso;
use App\Services\Comunicacion\ChatService;
use App\Support\ResolvesCoachClientes;
use App\Support\ResolvesStaffCoach;
use Illuminate\View\View;

class ClienteController extends Controller
{
    use ResolvesCoachClientes;
    use ResolvesStaffCoach;

    public function index(): View
    {
        $coach = $this->resolveCoachOrAbort();

        $clientes = $this->clientesDelCoachQuery($coach)
            ->with(['usuario', 'planesCliente.plan', 'metas'])
            ->whereIn('estado_cliente', ['ACTIVA', 'ACTIVO', 'PAUSADA', 'PAUSADO'])
            ->orderByDesc('actualizado_en')
            ->get()
            ->map(function ($cliente) {
                $cliente->plan_nombre = $cliente->planActivo()?->plan?->nombre ?? 'Sin plan activo';
                $cliente->ultimo_peso = RegistroProgreso::query()
                    ->where('id_cliente', $cliente->id_cliente)
                    ->orderByDesc('fecha_registro')
                    ->value('peso_kg');

                return $cliente;
            });

        return view('coach.clientes.index', compact('coach', 'clientes'));
    }

    public function show(int $id, ChatService $chats): View
    {
        $coach = $this->resolveCoachOrAbort();

        $cliente = $this->clientesDelCoachQuery($coach)
            ->with(['usuario', 'planesCliente.plan', 'metas', 'registrosProgreso' => fn ($q) => $q->orderByDesc('fecha_registro')->limit(12)])
            ->where('id_cliente', $id)
            ->firstOrFail();

        $planEntrenamiento = \App\Models\PlanEntrenamiento::query()
            ->with('rutinas.ejercicios.ejercicio')
            ->where('id_coach', $coach->id_coach)
            ->where('id_cliente', $cliente->id_cliente)
            ->orderByDesc('creado_en')
            ->first();

        $chat = $chats->obtenerOcrearChatCoachCliente($cliente);

        return view('coach.clientes.show', compact('coach', 'cliente', 'planEntrenamiento', 'chat'));
    }
}
