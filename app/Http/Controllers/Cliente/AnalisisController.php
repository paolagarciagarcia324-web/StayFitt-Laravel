<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Mensaje;
use App\Models\Pago;
use App\Models\ProgresoVideo;
use App\Models\RegistroProgreso;
use App\Models\RegistroRutina;
use App\Support\ResolvesCliente;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AnalisisController extends Controller
{
    use ResolvesCliente;

    public function index(): View
    {
        $cliente = $this->resolveClienteOrAbort();
        $desde = now()->subDays(30)->toDateString();

        $kpis = [
            'rutinas_30d' => RegistroRutina::query()
                ->where('id_cliente', $cliente->id_cliente)
                ->where('completada', true)
                ->where('fecha_ejecucion', '>=', $desde)
                ->count(),
            'videos_completados' => ProgresoVideo::query()
                ->where('id_cliente', $cliente->id_cliente)
                ->where('estado', 'COMPLETADO')
                ->count(),
            'registros_progreso' => RegistroProgreso::query()
                ->where('id_cliente', $cliente->id_cliente)
                ->count(),
            'pagos_validados' => Pago::query()
                ->where('id_cliente', $cliente->id_cliente)
                ->whereIn('estado_pago', ['VALIDADO', 'APROBADO'])
                ->count(),
            'racha' => $cliente->diasRacha(),
            'dias_plan' => $cliente->diasRestantesPlan(),
        ];

        $serieRutinas = RegistroRutina::query()
            ->select(DB::raw('fecha_ejecucion as fecha'), DB::raw('COUNT(*) as total'))
            ->where('id_cliente', $cliente->id_cliente)
            ->where('completada', true)
            ->where('fecha_ejecucion', '>=', $desde)
            ->groupBy('fecha_ejecucion')
            ->orderBy('fecha_ejecucion')
            ->get();

        $seriePeso = RegistroProgreso::query()
            ->where('id_cliente', $cliente->id_cliente)
            ->whereNotNull('peso_kg')
            ->orderBy('fecha_registro')
            ->limit(24)
            ->get(['fecha_registro', 'peso_kg', 'imc']);

        $mensajesEnviados = Mensaje::query()
            ->where('id_user_emisor', $cliente->id_user)
            ->where('enviado_en', '>=', now()->subDays(30))
            ->count();

        return view('cliente.analisis.index', compact('cliente', 'kpis', 'serieRutinas', 'seriePeso', 'mensajesEnviados'));
    }
}
