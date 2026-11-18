<?php

namespace App\Http\Controllers;

use App\Http\Requests\PreRegisterRequest;
use App\Models\Pago;
use App\Models\Plan;
use App\Models\SolicitudCompra;
use App\Services\PagoPasarelaSimulacionService;
use App\Services\SolicitudAprobacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;

class PreRegisterController extends Controller
{
    public function __construct(
        private SolicitudAprobacionService $activacion,
        private PagoPasarelaSimulacionService $pasarela
    ) {}

    public function create(Plan $plan): View|RedirectResponse
    {
        if (! $this->planVirtualActivo($plan)) {
            return redirect()->route('welcome')->with('error', 'Este plan virtual no está disponible.');
        }

        $plan->load('modulos');

        return view('pre-registro.create', compact('plan'));
    }

    public function store(PreRegisterRequest $request, Plan $plan): RedirectResponse
    {
        if (! $this->planVirtualActivo($plan)) {
            return redirect()->route('welcome')->with('error', 'Este plan virtual no está disponible.');
        }

        $codigo = SolicitudCompra::generarCodigo('VIRTUAL', $request->nombres, $request->apellidos);
        $referencia = 'REF-'.$codigo;

        try {
            DB::transaction(function () use ($request, $plan, $codigo, $referencia) {
                $solicitud = SolicitudCompra::create([
                    'codigo_solicitud' => $codigo,
                    'id_plan' => $plan->id_plan,
                    'origen' => 'PASARELA',
                    'modalidad' => 'VIRTUAL',
                    'tipo_cliente' => $plan->tipo_cliente === 'INSTITUCIONAL' ? 'INSTITUCIONAL' : 'INDIVIDUAL',
                    'nombres' => $request->nombres,
                    'apellidos' => $request->apellidos,
                    'tipo_documento' => $request->tipo_documento,
                    'documento_identidad' => $request->documento_identidad,
                    'documento' => $request->documento_identidad,
                    'correo' => $request->correo,
                    'telefono' => $request->telefono,
                    'ciudad' => $request->ciudad,
                    'precio_lista' => $plan->precio,
                    'descuento' => 0,
                    'total' => $plan->precio,
                    'acepto_terminos' => true,
                    'fecha_aceptacion_terminos' => now(),
                    'observacion_usuario' => $request->observacion_usuario,
                    'datos_extra_json' => [
                        'objetivo_principal' => $request->objetivo_principal,
                        'genero' => $request->genero,
                        'acepto_recurrente' => $request->boolean('acepto_recurrente'),
                        'medidas' => [
                            'peso_kg' => (float) $request->peso_kg,
                            'estatura_cm' => (float) $request->estatura_cm,
                            'cintura_cm' => (float) $request->cintura_cm,
                            'cadera_cm' => (float) $request->cadera_cm,
                            'brazo_cm' => (float) $request->brazo_cm,
                            'pierna_cm' => (float) $request->pierna_cm,
                        ],
                        'salud' => [
                            'alergias' => $request->alergias,
                            'condiciones_medicas' => $request->condiciones_medicas,
                            'lesiones' => $request->lesiones,
                            'medicamentos' => $request->medicamentos,
                            'contacto_emergencia_nombre' => $request->contacto_emergencia_nombre,
                            'contacto_emergencia_telefono' => $request->contacto_emergencia_telefono,
                        ],
                        'pagador' => [
                            'titular' => $request->input('titular'),
                            'tipo_documento' => $request->input('pago_tipo_documento'),
                            'documento' => $request->input('pago_documento'),
                            'correo' => $request->input('pago_correo'),
                            'telefono' => $request->input('pago_telefono'),
                            'direccion' => $request->input('direccion_pago'),
                            'ciudad' => $request->input('ciudad_pago'),
                            'mismos_datos' => $request->boolean('pago_mismos_datos'),
                        ],
                    ],
                    'estado_solicitud' => 'PENDIENTE',
                    'reserva_cupo' => true,
                ]);

                $pago = Pago::create([
                    'id_solicitud' => $solicitud->id_solicitud,
                    'id_plan' => $plan->id_plan,
                    'origen_pago' => 'SOLICITUD_PLAN',
                    'monto' => $plan->precio,
                    'moneda' => $plan->moneda,
                    'tipo_pago' => 'UNICO',
                    'metodo' => $request->metodo_pago,
                    'metodo_pago' => $request->metodo_pago,
                    'pasarela' => 'PENDIENTE',
                    'referencia' => $referencia,
                    'referencia_pago' => $referencia,
                    'fecha_pago' => now(),
                    'estado_pago' => 'PENDIENTE',
                ]);

                $pasarelaOk = $this->pasarela->cobrar($pago, $solicitud, $request->all());
                $this->activacion->activarPorPasarela($solicitud->fresh(['plan.modulos', 'pago']), $pasarelaOk);
                $this->pasarela->marcarActivada($solicitud, $pasarelaOk);
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error procesando pre-registro virtual: '.$e->getMessage(), ['exception' => $e]);
            return back()->withErrors(['metodo_pago' => 'No pudimos procesar el pago. Intenta de nuevo.'])->withInput();
        }

        return redirect()
            ->route('pre-registro.success', ['codigo' => $codigo])
            ->with('success', 'Pago aprobado. Tu acceso de cliente ya está activo.');
    }

    public function success(string $codigo): View
    {
        $solicitud = SolicitudCompra::with(['plan', 'pago'])
            ->where('codigo_solicitud', $codigo)
            ->firstOrFail();

        return view('pre-registro.success', compact('solicitud'));
    }

    private function planVirtualActivo(Plan $plan): bool
    {
        return $plan->estado_plan === 'ACTIVO' && $plan->modalidad === 'VIRTUAL';
    }
}
