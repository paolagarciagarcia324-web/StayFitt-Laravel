<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistroPresencialRequest;
use App\Models\HorarioPresencial;
use App\Models\Pago;
use App\Models\Plan;
use App\Models\SolicitudCompra;
use App\Models\TransaccionPasarela;
use App\Services\PagoPasarelaSimulacionService;
use App\Services\SolicitudAprobacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;

class RegistroPresencialController extends Controller
{
    public function __construct(
        private SolicitudAprobacionService $activacion,
        private PagoPasarelaSimulacionService $pasarela
    ) {}

    public function create(): View|RedirectResponse
    {
        $plan = $this->planPresencial();

        if (! $plan) {
            return redirect()
                ->route('welcome')
                ->with('error', 'No hay servicio presencial disponible en este momento.');
        }

        $horarios = HorarioPresencial::activos()->get()->filter(fn (HorarioPresencial $h) => $h->tieneCupo());
        if ($horarios->isEmpty()) {
            return redirect()
                ->route('welcome')
                ->with('error', 'No hay horarios disponibles para inscripción presencial.');
        }

        return view('registro-presencial.create', compact('plan', 'horarios'));
    }

    public function store(RegistroPresencialRequest $request): RedirectResponse
    {
        $plan = $this->planPresencial();

        if (! $plan) {
            return back()->withErrors(['id_horario' => 'El servicio presencial no está disponible.'])->withInput();
        }

        $horario = HorarioPresencial::activos()
            ->where('id_horario', $request->integer('id_horario'))
            ->firstOrFail();

        if (! $horario->tieneCupo()) {
            return back()->withErrors(['id_horario' => 'Ese horario ya no tiene cupo.'])->withInput();
        }

        $codigo = 'SF-P-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
        $referencia = 'REF-'.$codigo;

        $datosExtra = [
            'tipo' => 'REGISTRO_PRESENCIAL',
            'sede' => 'Campoalegre, Huila',
            'tipo_documento' => $request->tipo_documento,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'genero' => $request->genero,
            'objetivo_principal' => $request->objetivo_principal,
            'id_horario' => $horario->id_horario,
            'horario' => [
                'id_horario' => $horario->id_horario,
                'codigo' => $horario->codigo,
                'nombre' => $horario->nombre,
                'dias' => $horario->dias,
                'hora_inicio' => substr((string) $horario->hora_inicio, 0, 5),
                'hora_fin' => substr((string) $horario->hora_fin, 0, 5),
                'link_whatsapp' => $horario->link_whatsapp,
            ],
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
        ];

        try {
            DB::transaction(function () use ($request, $plan, $codigo, $referencia, $datosExtra) {
                $solicitud = SolicitudCompra::create([
                    'codigo_solicitud' => $codigo,
                    'id_plan' => $plan->id_plan,
                    'origen' => 'PASARELA',
                    'modalidad' => 'PRESENCIAL',
                    'tipo_cliente' => 'INDIVIDUAL',
                    'nombres' => $request->nombres,
                    'apellidos' => $request->apellidos,
                    'tipo_documento' => $request->tipo_documento,
                    'documento' => $request->documento_identidad,
                    'documento_identidad' => $request->documento_identidad,
                    'correo' => $request->correo,
                    'telefono' => $request->telefono,
                    'ciudad' => $request->ciudad,
                    'precio_lista' => $plan->precio,
                    'descuento' => 0,
                    'total' => $plan->precio,
                    'acepto_terminos' => true,
                    'fecha_aceptacion_terminos' => now(),
                    'observacion_usuario' => $request->observacion_usuario,
                    'datos_extra_json' => $datosExtra,
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
        } catch (RuntimeException $e) {
            return back()->withErrors(['metodo_pago' => 'No pudimos procesar el pago. Intenta de nuevo.'])->withInput();
        }

        return redirect()
            ->route('registro-presencial.success', ['codigo' => $codigo])
            ->with('success', 'Pago aprobado. Tu acceso de cliente ya está activo.')
            ->with('mostrar_whatsapp', true);
    }

    public function checkout(string $codigo): View|RedirectResponse
    {
        $solicitud = $this->solicitudPorCodigo($codigo);

        if ($solicitud->estado_solicitud === 'VALIDADA') {
            return redirect()->route('registro-presencial.success', ['codigo' => $codigo]);
        }

        return redirect()->route('registro-presencial.create');
    }

    public function pagar(string $codigo): RedirectResponse
    {
        return DB::transaction(function () use ($codigo) {
            $solicitud = SolicitudCompra::with(['plan', 'pago'])
                ->where('codigo_solicitud', $codigo)
                ->lockForUpdate()
                ->firstOrFail();

            if ($solicitud->estado_solicitud === 'VALIDADA') {
                return redirect()->route('registro-presencial.success', ['codigo' => $codigo]);
            }

            $transaccion = TransaccionPasarela::query()
                ->where('id_solicitud', $solicitud->id_solicitud)
                ->lockForUpdate()
                ->firstOrFail();

            if ($transaccion->activacion_ok) {
                return redirect()->route('registro-presencial.success', ['codigo' => $codigo]);
            }

            $transaccion->update([
                'estado_pasarela' => 'APPROVED',
                'referencia_externa' => 'GW-'.strtoupper(Str::random(10)),
                'firma_valida' => true,
                'webhook_data' => [
                    'evento' => 'payment.approved',
                    'origen' => 'checkout_sandbox',
                    'aprobado_en' => now()->toIso8601String(),
                ],
                'intentos_activacion' => $transaccion->intentos_activacion + 1,
            ]);

            $this->activacion->activarPorPasarela($solicitud->fresh(['plan.modulos', 'pago']), $transaccion->pasarela);

            $transaccion->update(['activacion_ok' => true]);

            return redirect()
                ->route('registro-presencial.success', ['codigo' => $codigo])
                ->with('success', 'Pago aprobado. Tu acceso de cliente ya está activo.')
                ->with('mostrar_whatsapp', true);
        });
    }

    public function success(string $codigo): View
    {
        $solicitud = $this->solicitudPorCodigo($codigo);
        $horario = $solicitud->datos_extra_json['horario'] ?? null;
        $linkWhatsapp = $horario['link_whatsapp'] ?? null;

        return view('registro-presencial.success', [
            'solicitud' => $solicitud,
            'horario' => $horario,
            'linkWhatsapp' => $linkWhatsapp,
            'mostrarWhatsapp' => session('mostrar_whatsapp', true) && ! empty($linkWhatsapp),
        ]);
    }

    private function solicitudPorCodigo(string $codigo): SolicitudCompra
    {
        return SolicitudCompra::with(['plan', 'pago'])
            ->where('codigo_solicitud', $codigo)
            ->firstOrFail();
    }

    private function planPresencial(): ?Plan
    {
        return Plan::activos()
            ->where('modalidad', 'PRESENCIAL')
            ->where(function ($query) {
                $query->where('codigo', 'PRESENCIAL-PLUS')
                    ->orWhere('slug', 'plan-presencial-sala');
            })
            ->first()
            ?? Plan::activos()->where('modalidad', 'PRESENCIAL')->first();
    }
}
