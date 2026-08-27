<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\RegistroProgreso;
use App\Support\ResolvesStaffCoach;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * RF-046 — Reportes de progreso del coach (MVP lectura + export CSV).
 */
class ReporteProgresoController extends Controller
{
    use ResolvesStaffCoach;

    public function index(Request $request): View
    {
        $coach = $this->resolveCoachOrAbort();

        $clientes = Cliente::query()
            ->with('usuario')
            ->where(function ($q) use ($coach) {
                $q->where('id_coach', $coach->id_coach)
                    ->orWhere('id_coach_asignado', $coach->id_coach);
            })
            ->whereIn('estado_cliente', ['ACTIVA', 'ACTIVO'])
            ->orderBy('id_cliente')
            ->get();

        $idCliente = $request->integer('id_cliente') ?: null;
        $registros = collect();

        if ($idCliente) {
            $cliente = $clientes->firstWhere('id_cliente', $idCliente);
            abort_unless($cliente, 403);

            $registros = RegistroProgreso::query()
                ->where('id_cliente', $idCliente)
                ->orderByDesc('fecha_registro')
                ->limit(100)
                ->get();
        }

        return view('coach.reportes.progreso', compact('clientes', 'registros', 'idCliente'));
    }

    public function export(Request $request)
    {
        $coach = $this->resolveCoachOrAbort();
        $idCliente = $request->validate([
            'id_cliente' => ['required', 'integer'],
        ])['id_cliente'];

        $cliente = Cliente::query()
            ->with('usuario')
            ->where('id_cliente', $idCliente)
            ->where(function ($q) use ($coach) {
                $q->where('id_coach', $coach->id_coach)
                    ->orWhere('id_coach_asignado', $coach->id_coach);
            })
            ->firstOrFail();

        $registros = RegistroProgreso::query()
            ->where('id_cliente', $idCliente)
            ->orderBy('fecha_registro')
            ->get();

        $filename = 'progreso_cliente_'.$idCliente.'_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($cliente, $registros) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['Cliente', 'Fecha', 'Peso kg', 'IMC', 'Cintura', 'Observación']);
            foreach ($registros as $r) {
                fputcsv($out, [
                    trim(($cliente->usuario?->nombres ?? '').' '.($cliente->usuario?->apellidos ?? '')),
                    $r->fecha_registro,
                    $r->peso_kg,
                    $r->imc,
                    $r->cintura_cm,
                    $r->observacion_coach,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function observacion(Request $request)
    {
        $coach = $this->resolveCoachOrAbort();

        $data = $request->validate([
            'id_registro_progreso' => ['required', 'integer'],
            'observacion_coach' => ['required', 'string', 'max:1000'],
        ]);

        $registro = RegistroProgreso::query()->findOrFail($data['id_registro_progreso']);

        $cliente = Cliente::query()
            ->where('id_cliente', $registro->id_cliente)
            ->where(function ($q) use ($coach) {
                $q->where('id_coach', $coach->id_coach)
                    ->orWhere('id_coach_asignado', $coach->id_coach);
            })
            ->firstOrFail();

        $registro->update([
            'observacion_coach' => $data['observacion_coach'],
            'id_coach' => $coach->id_coach,
        ]);

        return back()->with('success', 'Observación registrada para '.$cliente->usuario?->nombres);
    }
}
