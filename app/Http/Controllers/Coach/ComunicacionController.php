<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cliente\EnviarMensajeRequest;
use App\Models\Chat;
use App\Services\Comunicacion\ChatService;
use App\Support\ResolvesStaffCoach;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ComunicacionController extends Controller
{
    use ResolvesStaffCoach;

    public function index(ChatService $chats): View
    {
        $coach = $this->resolveCoachOrAbort();

        $listaChats = $chats->listarChatsCoach($coach);

        return view('coach.comunicacion.index', compact('listaChats'));
    }

    public function show(int $id, ChatService $chats): View
    {
        $user = Auth::user();
        $coach = $this->resolveCoachOrAbort();
        $chat = Chat::query()
            ->where('id_coach', $coach->id_coach)
            ->with(['participantes.usuario', 'plan'])
            ->findOrFail($id);

        if (! $chats->usuarioPuedeAcceder($user, $chat)) {
            abort(403);
        }

        $chats->marcarLeidos($chat, $user);
        $mensajes = $chats->mensajesRecientes($chat);

        return view('coach.comunicacion.show', compact('chat', 'mensajes'));
    }

    public function store(int $id, EnviarMensajeRequest $request, ChatService $chats): RedirectResponse
    {
        $user = Auth::user();
        $coach = $this->resolveCoachOrAbort();
        $chat = Chat::query()
            ->where('id_coach', $coach->id_coach)
            ->findOrFail($id);

        if (! $chats->usuarioPuedeAcceder($user, $chat)) {
            abort(403);
        }

        $chats->enviarMensaje($chat, $user, $request->validated('contenido'));

        return back()->with('success', 'Mensaje enviado.');
    }
}
