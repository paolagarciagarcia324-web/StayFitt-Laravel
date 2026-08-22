<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Mensaje;
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
        $chat = Chat::query()->with(['coach.usuario', 'plan'])->findOrFail($id);
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
}
