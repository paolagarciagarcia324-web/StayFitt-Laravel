<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use App\Services\Comunicacion\NotificacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificacionController extends Controller
{
    public function index(NotificacionService $notificaciones): View
    {
        $lista = $notificaciones->listarRecientes(Auth::user(), 50);

        return view('cliente.notificaciones.index', compact('lista'));
    }

    public function marcarLeida(int $id, NotificacionService $notificaciones): RedirectResponse
    {
        $notificacion = Notificacion::query()->findOrFail($id);
        $notificaciones->marcarLeida($notificacion, Auth::user());

        return back();
    }

    public function marcarTodas(NotificacionService $notificaciones): RedirectResponse
    {
        $notificaciones->marcarTodasLeidas(Auth::user());

        return back()->with('success', 'Notificaciones marcadas como leídas.');
    }
}
