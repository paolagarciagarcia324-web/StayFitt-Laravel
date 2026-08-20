<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Pago;
use App\Models\Plan;
use App\Models\ReporteGenerado;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteController extends Controller
{
    public function index(): View
    {
        $historial = ReporteGenerado::query()->orderByDesc('generado_en')->limit(20)->get();

        return view('admin.reportes.index', compact('historial'));
    }

    public function export(Request $request): StreamedResponse
    {
        $tipo = $request->validate([
            'tipo' => ['required', 'in:pagos,clientes,cupos'],
        ])['tipo'];

        $filename = "stayfit_{$tipo}_".now()->format('Ymd_His').'.csv';

        ReporteGenerado::create([
            'id_user' => Auth::id(),
            'tipo' => strtoupper($tipo),
            'parametros_json' => ['export' => 'csv'],
            'formato' => 'CSV',
            'url_archivo' => $filename,
            'generado_en' => now(),
        ]);

        return response()->streamDownload(function () use ($tipo) {
            $out = fopen('php://output', 'w');

            if ($tipo === 'pagos') {
                fputcsv($out, ['id_pago', 'referencia', 'monto', 'moneda', 'estado', 'metodo', 'fecha']);
                Pago::query()->orderByDesc('id_pago')->limit(2000)->get()->each(function ($p) use ($out) {
                    fputcsv($out, [
                        $p->id_pago,
                        $p->referencia ?: $p->referencia_pago,
                        $p->monto,
                        $p->moneda,
                        $p->estado_pago,
                        $p->metodo_pago ?: $p->metodo,
                        optional($p->creado_en)->format('Y-m-d H:i'),
                    ]);
                });
            } elseif ($tipo === 'clientes') {
                fputcsv($out, ['id_cliente', 'nombres', 'apellidos', 'correo', 'modalidad', 'estado']);
                Cliente::query()->with('usuario')->orderByDesc('id_cliente')->limit(2000)->get()->each(function ($c) use ($out) {
                    fputcsv($out, [
                        $c->id_cliente,
                        $c->usuario?->nombres,
                        $c->usuario?->apellidos,
                        $c->usuario?->correo,
                        $c->modalidad,
                        $c->estado_cliente,
                    ]);
                });
            } else {
                fputcsv($out, ['plan', 'cupos_totales', 'ocupados', 'reservados', 'estado']);
                Plan::query()->orderBy('orden')->get()->each(function ($p) use ($out) {
                    fputcsv($out, [
                        $p->nombre,
                        $p->cupos_totales,
                        $p->cupos_ocupados,
                        $p->cupos_reservados,
                        $p->estado_plan,
                    ]);
                });
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
