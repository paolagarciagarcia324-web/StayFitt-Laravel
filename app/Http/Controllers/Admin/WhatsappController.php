<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminWhatsappConfigRequest;
use App\Http\Requests\Admin\AdminWhatsappGrupoRequest;
use App\Models\ConfiguracionWhatsapp;
use App\Models\GrupoWhatsapp;
use App\Services\Comunicacion\InvitacionWhatsappService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WhatsappController extends Controller
{
    public function index(InvitacionWhatsappService $invitaciones): View
    {
        $config = ConfiguracionWhatsapp::query()->with('grupos')->first();
        $grupos = $config?->grupos ?? collect();
        $historial = $invitaciones->historial(30);

        return view('admin.whatsapp.index', compact('config', 'grupos', 'historial'));
    }

    public function update(AdminWhatsappConfigRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $config = ConfiguracionWhatsapp::query()->first();

        $respuestas = null;
        if (! empty($data['respuestas_automaticas'])) {
            $decoded = json_decode($data['respuestas_automaticas'], true);
            $respuestas = is_array($decoded) ? $decoded : null;
        }

        $payload = [
            'numero_business' => $data['numero_business'],
            'link_grupo_principal' => $data['link_grupo_principal'] ?? null,
            'mensaje_bienvenida' => $data['mensaje_bienvenida'] ?? null,
            'horario_atencion' => $data['horario_atencion'] ?? null,
            'respuestas_automaticas' => $respuestas,
            'activa' => $request->boolean('activa'),
            'actualizado_en' => now(),
        ];

        if ($config) {
            $config->fill($payload);
            if (! empty($data['api_token'])) {
                $config->api_token = $data['api_token'];
            }
            $config->save();
        } else {
            $config = new ConfiguracionWhatsapp($payload);
            if (! empty($data['api_token'])) {
                $config->api_token = $data['api_token'];
            }
            $config->save();
        }

        return back()->with('success', 'Configuración WhatsApp guardada.');
    }

    public function storeGrupo(AdminWhatsappGrupoRequest $request): RedirectResponse
    {
        $config = ConfiguracionWhatsapp::query()->firstOrFail();
        $data = $request->validated();

        GrupoWhatsapp::create([
            'id_config_wa' => $config->id_config_wa,
            ...$data,
        ]);

        return back()->with('success', 'Grupo WhatsApp creado.');
    }

    public function updateGrupo(AdminWhatsappGrupoRequest $request, int $id): RedirectResponse
    {
        $grupo = GrupoWhatsapp::query()->findOrFail($id);
        $data = $request->validated();
        $grupo->update($data);

        return back()->with('success', 'Grupo actualizado.');
    }

    public function destroyGrupo(int $id): RedirectResponse
    {
        $grupo = GrupoWhatsapp::query()->findOrFail($id);
        $grupo->update(['estado' => 'INACTIVO']);

        return back()->with('success', 'Grupo desactivado.');
    }
}
