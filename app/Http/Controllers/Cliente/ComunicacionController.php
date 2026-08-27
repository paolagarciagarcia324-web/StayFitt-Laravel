<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cliente\EnviarMensajeRequest;
use App\Models\Chat;
use App\Models\InvitacionWhatsapp;
use App\Services\Comunicacion\ChatService;
use App\Services\Comunicacion\InvitacionWhatsappService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ComunicacionController extends Controller
{
    public function index(ChatService $chats, InvitacionWhatsappService $whatsapp): View
    {
        $user = Auth::user();
        $cliente = $user->cliente;

        if ($cliente && strtoupper((string) $cliente->modalidad) !== 'PRESENCIAL') {
            $chats->obtenerOcrearChatCoachCliente($cliente->load('usuario'));
        }

        $listaChats = $chats->listarChatsUsuario($user);
        $invitaciones = $cliente ? $whatsapp->pendientesParaCliente($cliente) : collect();

        return view('cliente.comunicacion.index', compact('listaChats', 'invitaciones', 'cliente'));
    }

    public function show(int $id, ChatService $chats): View
    {
        $user = Auth::user();
        $chat = Chat::query()->with(['coach.usuario', 'participantes.usuario'])->findOrFail($id);

        if (! $chats->usuarioPuedeAcceder($user, $chat)) {
            abort(403);
        }

        $chats->marcarLeidos($chat, $user);
        $mensajes = $chats->mensajesRecientes($chat);

        return view('cliente.comunicacion.show', compact('chat', 'mensajes'));
    }

    public function store(int $id, EnviarMensajeRequest $request, ChatService $chats): RedirectResponse
    {
        $user = Auth::user();
        $chat = Chat::query()->findOrFail($id);

        if (! $chats->usuarioPuedeAcceder($user, $chat)) {
            abort(403);
        }

        $chats->enviarMensaje($chat, $user, $request->validated('contenido'));

        return back()->with('success', 'Mensaje enviado.');
    }

    public function aceptarWhatsapp(int $id, InvitacionWhatsappService $whatsapp): RedirectResponse
    {
        $user = Auth::user();
        $cliente = $user->cliente;
        $invitacion = InvitacionWhatsapp::query()->findOrFail($id);

        if (! $cliente || $invitacion->id_cliente !== $cliente->id_cliente) {
            abort(403);
        }

        $whatsapp->marcarAceptada($invitacion);

        return redirect($invitacion->link_mostrado);
    }

    public function cerrarWhatsapp(int $id, InvitacionWhatsappService $whatsapp): RedirectResponse
    {
        $user = Auth::user();
        $cliente = $user->cliente;
        $invitacion = InvitacionWhatsapp::query()->findOrFail($id);

        if (! $cliente || $invitacion->id_cliente !== $cliente->id_cliente) {
            abort(403);
        }

        $whatsapp->marcarCerradaSinAceptar($invitacion);

        return back()->with('success', 'Invitación cerrada. Puedes unirte más tarde desde Comunicación.');
    }
}
