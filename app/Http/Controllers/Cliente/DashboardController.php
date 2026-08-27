<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\NovedadWelcome;
use App\Models\Pago;
use App\Models\RegistroProgreso;
use App\Models\RegistroRutina;
use App\Services\Comunicacion\InvitacionWhatsappService;
use App\Services\Comunicacion\NotificacionService;
use App\Services\ModuloAccesoService;
use App\Support\ResolvesCliente;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use ResolvesCliente;

    public function index(
        NotificacionService $notificaciones,
        InvitacionWhatsappService $whatsapp,
        ModuloAccesoService $modulos,
    ): View {
        $user = Auth::user();
        $cliente = $this->resolveClienteOrAbort();
        $planActivo = $cliente->planActivo();
        $planActivo?->loadMissing('plan');
        $modulosActivos = $modulos->modulosActivosCliente($cliente);
        $noLeidas = $notificaciones->contarNoLeidas($user);
        $invitaciones = $whatsapp->pendientesParaCliente($cliente);
        $novedades = NovedadWelcome::query()->visibles()->limit(4)->get();

        $rutinasCompletadas = RegistroRutina::query()
            ->where('id_cliente', $cliente->id_cliente)
            ->where('completada', true)
            ->where('fecha_ejecucion', '>=', now()->subDays(7)->toDateString())
            ->count();

        $ultimoProgreso = RegistroProgreso::query()
            ->where('id_cliente', $cliente->id_cliente)
            ->orderByDesc('fecha_registro')
            ->first();

        $pagosPendientes = Pago::query()
            ->where('id_cliente', $cliente->id_cliente)
            ->where('estado_pago', 'PENDIENTE')
            ->count();

        return view('cliente.dashboard', compact(
            'cliente',
            'user',
            'planActivo',
            'modulosActivos',
            'noLeidas',
            'invitaciones',
            'novedades',
            'rutinasCompletadas',
            'ultimoProgreso',
            'pagosPendientes',
            'modulos',
        ));
    }
}
