<?php

namespace App\Services\Comunicacion;

use App\Models\Chat;
use App\Models\ChatParticipante;
use App\Models\Cliente;
use App\Models\Coach;
use App\Models\Mensaje;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ChatService
{
    public function __construct(private NotificacionService $notificaciones)
    {
    }

    public function listarChatsUsuario(User $user): Collection
    {
        return Chat::query()
            ->whereHas('participantes', fn ($q) => $q->where('id_user', $user->id_user))
            ->with(['coach.usuario', 'plan', 'participantes.usuario'])
            ->withCount('mensajes')
            ->orderByDesc('creado_en')
            ->get();
    }

    public function usuarioPuedeAcceder(User $user, Chat $chat): bool
    {
        if ($user->tieneRol('ADMIN')) {
            return true;
        }

        return ChatParticipante::query()
            ->where('id_chat', $chat->id_chat)
            ->where('id_user', $user->id_user)
            ->exists();
    }

    public function obtenerOcrearChatCoachCliente(Cliente $cliente): ?Chat
    {
        $coachId = $cliente->id_coach_asignado ?: $cliente->id_coach;
        if (! $coachId) {
            return null;
        }

        $coach = Coach::query()->with('usuario')->find($coachId);
        if (! $coach?->usuario) {
            return null;
        }

        $planActivo = $cliente->planActivo();

        $existente = Chat::query()
            ->where('tipo', 'COACH_CLIENTE')
            ->where('id_coach', $coach->id_coach)
            ->whereHas('participantes', fn ($q) => $q->where('id_user', $cliente->usuario->id_user))
            ->first();

        if ($existente) {
            return $existente;
        }

        return DB::transaction(function () use ($cliente, $coach, $planActivo) {
            $chat = Chat::create([
                'tipo' => 'COACH_CLIENTE',
                'id_plan' => $planActivo?->id_plan,
                'id_coach' => $coach->id_coach,
                'nombre' => trim($coach->usuario->nombres.' · '.$cliente->usuario->nombres),
                'estado' => 'ACTIVO',
            ]);

            $this->agregarParticipante($chat, $cliente->usuario, 'CLIENTE');
            $this->agregarParticipante($chat, $coach->usuario, 'COACH');

            return $chat;
        });
    }

    public function listarChatsCoach(Coach $coach): Collection
    {
        return Chat::query()
            ->where('id_coach', $coach->id_coach)
            ->where('estado', 'ACTIVO')
            ->with(['participantes.usuario', 'plan'])
            ->withCount('mensajes')
            ->orderByDesc('creado_en')
            ->get();
    }

    public function enviarMensaje(Chat $chat, User $emisor, string $contenido, string $tipo = 'TEXTO'): Mensaje
    {
        if ($chat->estado !== 'ACTIVO') {
            abort(422, 'Este chat está cerrado.');
        }

        if (! $this->usuarioPuedeAcceder($emisor, $chat)) {
            abort(403);
        }

        $mensaje = Mensaje::create([
            'id_chat' => $chat->id_chat,
            'id_user_emisor' => $emisor->id_user,
            'contenido' => trim($contenido),
            'tipo_mensaje' => $tipo,
            'leido' => false,
            'enviado_en' => now(),
        ]);

        $destinatarios = ChatParticipante::query()
            ->with('usuario')
            ->where('id_chat', $chat->id_chat)
            ->where('id_user', '!=', $emisor->id_user)
            ->get();

        foreach ($destinatarios as $participante) {
            if (! $participante->usuario) {
                continue;
            }

            $this->notificaciones->crear(
                $participante->usuario,
                'MENSAJE_CHAT',
                'Nuevo mensaje',
                mb_substr(trim($contenido), 0, 120),
                'CHAT',
                $chat->id_chat
            );
        }

        return $mensaje;
    }

    public function mensajesRecientes(Chat $chat, int $limit = 100): Collection
    {
        return Mensaje::query()
            ->with('emisor')
            ->where('id_chat', $chat->id_chat)
            ->orderByDesc('enviado_en')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();
    }

    public function marcarLeidos(Chat $chat, User $user): void
    {
        if (! $this->usuarioPuedeAcceder($user, $chat)) {
            return;
        }

        Mensaje::query()
            ->where('id_chat', $chat->id_chat)
            ->where('id_user_emisor', '!=', $user->id_user)
            ->where('leido', false)
            ->update(['leido' => true]);
    }

    public function cerrarChat(Chat $chat): void
    {
        $chat->update(['estado' => 'CERRADO']);
    }

    public function eliminarMensaje(Mensaje $mensaje): void
    {
        $mensaje->delete();
    }

    private function agregarParticipante(Chat $chat, User $user, string $rol): ChatParticipante
    {
        return ChatParticipante::firstOrCreate(
            [
                'id_chat' => $chat->id_chat,
                'id_user' => $user->id_user,
            ],
            [
                'rol_en_chat' => $rol,
                'unido_en' => now(),
            ]
        );
    }
}
