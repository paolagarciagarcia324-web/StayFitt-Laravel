<?php

namespace App\Services\Comunicacion;

use App\Models\Cliente;
use App\Models\ConfiguracionWhatsapp;
use App\Models\GrupoWhatsapp;
use App\Models\InvitacionWhatsapp;
use App\Models\ParticipanteGrupoWhatsapp;
use Illuminate\Support\Collection;

class InvitacionWhatsappService
{
    public function __construct(private NotificacionService $notificaciones)
    {
    }

    /**
     * Genera invitaciones al activar un cliente presencial.
     *
     * @return Collection<int, InvitacionWhatsapp>
     */
    public function generarParaActivacion(Cliente $cliente): Collection
    {
        $cliente->load(['horario', 'usuario']);

        if (strtoupper((string) $cliente->modalidad) !== 'PRESENCIAL') {
            return collect();
        }

        $config = ConfiguracionWhatsapp::query()->where('activa', true)->first();
        if (! $config) {
            return collect();
        }

        $invitaciones = collect();
        $grupo = $this->resolverGrupoParaCliente($cliente, $config);

        if ($grupo) {
            $invitaciones->push($this->crearInvitacion($cliente, $grupo->link_invitacion, $grupo));
        } elseif ($config->link_grupo_principal) {
            $invitaciones->push($this->crearInvitacion($cliente, $config->link_grupo_principal));
        } elseif ($cliente->horario) {
            $invitaciones->push($this->crearInvitacion($cliente, $cliente->horario->urlGrupoWhatsapp()));
        }

        if ($cliente->usuario && $invitaciones->isNotEmpty()) {
            $this->notificaciones->crear(
                $cliente->usuario,
                'INVITACION_WHATSAPP',
                'Únete al grupo de WhatsApp',
                'Tienes una invitación pendiente al grupo de tu horario presencial.',
                'WHATSAPP',
                $invitaciones->first()->id_invitacion_wa
            );
        }

        return $invitaciones;
    }

    public function pendientesParaCliente(Cliente $cliente): Collection
    {
        return InvitacionWhatsapp::query()
            ->with('grupoWa')
            ->where('id_cliente', $cliente->id_cliente)
            ->whereNull('aceptada_en')
            ->where('cerrada_sin_aceptar', false)
            ->orderByDesc('mostrada_en')
            ->get();
    }

    public function marcarMostrada(InvitacionWhatsapp $invitacion): void
    {
        $invitacion->update(['mostrada_en' => now()]);
    }

    public function marcarAceptada(InvitacionWhatsapp $invitacion): void
    {
        $invitacion->update([
            'aceptada_en' => now(),
            'cerrada_sin_aceptar' => false,
        ]);

        if ($invitacion->id_grupo_wa) {
            ParticipanteGrupoWhatsapp::query()->updateOrCreate(
                [
                    'id_grupo_wa' => $invitacion->id_grupo_wa,
                    'id_cliente' => $invitacion->id_cliente,
                ],
                [
                    'estado' => 'UNIDO',
                    'unido_en' => now(),
                ]
            );
        }
    }

    public function marcarCerradaSinAceptar(InvitacionWhatsapp $invitacion): void
    {
        $invitacion->update([
            'cerrada_sin_aceptar' => true,
            'persistencia_local' => true,
        ]);
    }

    public function historial(int $limit = 50): Collection
    {
        return InvitacionWhatsapp::query()
            ->with(['cliente.usuario', 'grupoWa'])
            ->orderByDesc('mostrada_en')
            ->limit($limit)
            ->get();
    }

    private function resolverGrupoParaCliente(Cliente $cliente, ConfiguracionWhatsapp $config): ?GrupoWhatsapp
    {
        $horario = $cliente->horario;
        if (! $horario) {
            return $config->grupos()
                ->where('estado', 'ACTIVO')
                ->where('tipo', 'GENERAL')
                ->first();
        }

        $porCodigo = $config->grupos()
            ->where('estado', 'ACTIVO')
            ->where('tipo', 'HORARIO')
            ->where(function ($query) use ($horario) {
                $query->where('criterio', $horario->codigo)
                    ->orWhere('criterio', (string) $horario->id_horario);
            })
            ->first();

        if ($porCodigo) {
            return $porCodigo;
        }

        return $config->grupos()
            ->where('estado', 'ACTIVO')
            ->where('tipo', 'HORARIO')
            ->where('nombre', 'like', '%'.$horario->hora_inicio.'%')
            ->first();
    }

    private function crearInvitacion(Cliente $cliente, string $link, ?GrupoWhatsapp $grupo = null): InvitacionWhatsapp
    {
        $invitacion = InvitacionWhatsapp::create([
            'id_cliente' => $cliente->id_cliente,
            'id_grupo_wa' => $grupo?->id_grupo_wa,
            'link_mostrado' => $link,
            'mostrada_en' => now(),
        ]);

        if ($grupo) {
            ParticipanteGrupoWhatsapp::query()->firstOrCreate(
                [
                    'id_grupo_wa' => $grupo->id_grupo_wa,
                    'id_cliente' => $cliente->id_cliente,
                ],
                [
                    'estado' => 'INVITADO',
                ]
            );
        }

        return $invitacion;
    }
}
