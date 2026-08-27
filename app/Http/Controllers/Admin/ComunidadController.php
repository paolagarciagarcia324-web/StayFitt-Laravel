<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminComunidadChatUpdateRequest;
use App\Models\Chat;
use App\Models\Mensaje;
use App\Services\Comunicacion\ChatService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ComunidadController extends Controller
{
    public function index(): View
    {
        $chats = Chat::query()
            ->with(['coach.usuario', 'plan'])
            ->withCount('mensajes')
            ->orderByDesc('creado_en')
            ->paginate(10)
            ->withQueryString();

        return view('admin.comunidad.index', compact('chats'));
    }

    public function show(int $id): View
    {
        $chat = Chat::query()->with(['coach.usuario', 'plan', 'participantes.usuario'])->findOrFail($id);
        $mensajes = Mensaje::query()
            ->with('emisor')
            ->where('id_chat', $chat->id_chat)
            ->orderByDesc('enviado_en')
            ->limit(100)
            ->get()
            ->reverse()
            ->values();

        return view('admin.comunidad.show', compact('chat', 'mensajes'));
    }

    public function updateChat(AdminComunidadChatUpdateRequest $request, int $id, ChatService $chats): RedirectResponse
    {
        $chat = Chat::query()->findOrFail($id);

        if ($request->validated('estado') === Chat::ESTADO_CERRADO) {
            $chats->cerrarChat($chat);
        } else {
            $chat->update(['estado' => Chat::ESTADO_ACTIVO]);
        }

        return back()->with('success', 'Estado del chat actualizado.');
    }

    public function destroyMensaje(int $chatId, int $mensajeId, ChatService $chats): RedirectResponse
    {
        $mensaje = Mensaje::query()
            ->where('id_chat', $chatId)
            ->where('id_mensaje', $mensajeId)
            ->firstOrFail();

        $chats->eliminarMensaje($mensaje);

        return back()->with('success', 'Mensaje eliminado.');
    }
}
