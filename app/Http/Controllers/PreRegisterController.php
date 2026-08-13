<?php

namespace App\Http\Controllers;

use App\Http\Requests\PreRegisterRequest;
use App\Models\Pago;
use App\Models\Plan;
use App\Models\SolicitudCompra;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PreRegisterController extends Controller
{
    public function create(Plan $plan): View|RedirectResponse
    {
        if ($plan->estado_plan !== 'ACTIVO') {
            return redirect()->route('welcome')->with('error', 'Este plan no está disponible.');
        }

        $plan->load('modulos');

        return view('pre-registro.create', compact('plan'));
    }

    public function store(PreRegisterRequest $request, Plan $plan): RedirectResponse
    {
        if ($plan->estado_plan !== 'ACTIVO') {
            return redirect()->route('welcome')->with('error', 'Este plan no está disponible.');
        }

        $codigo = 'SF-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));

        $comprobantePath = $request->file('comprobante')->store('comprobantes', 'public');

        DB::transaction(function () use ($request, $plan, $codigo, $comprobantePath) {
            $solicitud = SolicitudCompra::create([
                'codigo_solicitud' => $codigo,
                'id_plan' => $plan->id_plan,
                'modalidad' => $request->modalidad,
                'tipo_cliente' => 'INDIVIDUAL',
                'nombres' => $request->nombres,
                'apellidos' => $request->apellidos,
                'documento_identidad' => $request->documento_identidad,
                'correo' => $request->correo,
                'telefono' => $request->telefono,
                'ciudad' => $request->ciudad,
                'acepto_terminos' => true,
                'fecha_aceptacion_terminos' => now(),
                'observacion_usuario' => $request->observacion_usuario,
                'estado_solicitud' => 'PENDIENTE',
            ]);

            Pago::create([
                'id_solicitud' => $solicitud->id_solicitud,
                'id_plan' => $plan->id_plan,
                'origen_pago' => 'SOLICITUD_PLAN',
                'monto' => $plan->precio,
                'moneda' => $plan->moneda,
                'tipo_pago' => 'UNICO',
                'metodo_pago' => $request->metodo_pago,
                'referencia_pago' => $request->referencia_pago,
                'comprobante_url' => Storage::url($comprobantePath),
                'fecha_pago' => now(),
                'estado_pago' => 'PENDIENTE',
            ]);
        });

        return redirect()
            ->route('pre-registro.success', ['codigo' => $codigo])
            ->with('success', 'Tu solicitud fue enviada correctamente.');
    }

    public function success(string $codigo): View
    {
        $solicitud = SolicitudCompra::with(['plan', 'pago'])
            ->where('codigo_solicitud', $codigo)
            ->firstOrFail();

        return view('pre-registro.success', compact('solicitud'));
    }
}
