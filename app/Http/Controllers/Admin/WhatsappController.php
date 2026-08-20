<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracionWhatsapp;
use App\Models\GrupoWhatsapp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WhatsappController extends Controller
{
    public function index(): View
    {
        $config = ConfiguracionWhatsapp::query()->with('grupos')->first();
        $grupos = $config?->grupos ?? collect();

        return view('admin.whatsapp.index', compact('config', 'grupos'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'numero_business' => ['required', 'string', 'max:30'],
            'link_grupo_principal' => ['nullable', 'string', 'max:300'],
            'mensaje_bienvenida' => ['nullable', 'string', 'max:2000'],
            'horario_atencion' => ['nullable', 'string', 'max:120'],
            'activa' => ['nullable', 'boolean'],
        ]);

        $config = ConfiguracionWhatsapp::query()->first();

        $payload = [
            'numero_business' => $data['numero_business'],
            'link_grupo_principal' => $data['link_grupo_principal'] ?? null,
            'mensaje_bienvenida' => $data['mensaje_bienvenida'] ?? null,
            'horario_atencion' => $data['horario_atencion'] ?? null,
            'activa' => $request->boolean('activa'),
            'actualizado_en' => now(),
        ];

        if ($config) {
            $config->update($payload);
        } else {
            ConfiguracionWhatsapp::create($payload);
        }

        return back()->with('success', 'Configuración WhatsApp guardada.');
    }

    public function storeGrupo(Request $request): RedirectResponse
    {
        $config = ConfiguracionWhatsapp::query()->firstOrFail();

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'tipo' => ['required', 'string', 'max:20'],
            'criterio' => ['nullable', 'string', 'max:80'],
            'link_invitacion' => ['required', 'string', 'max:300'],
            'estado' => ['required', 'in:ACTIVO,INACTIVO'],
        ]);

        GrupoWhatsapp::create([
            'id_config_wa' => $config->id_config_wa,
            ...$data,
        ]);

        return back()->with('success', 'Grupo WhatsApp creado.');
    }
}
