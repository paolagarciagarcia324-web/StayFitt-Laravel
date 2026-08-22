<?php

namespace App\Services;

use App\Models\AccesoClienteModulo;
use App\Models\Cliente;
use App\Models\HorarioPresencial;
use App\Models\MedidaAntropometricaInicial;
use App\Models\Pago;
use App\Models\PlanCliente;
use App\Models\Rol;
use App\Models\SolicitudCompra;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SolicitudAprobacionService
{
    public function __construct(private ComprobantePasarelaService $comprobantes)
    {
    }

    public function aprobar(SolicitudCompra $solicitud, ?string $observacion = null): void
    {
        $this->activar($solicitud, $observacion, false);
    }

    public function activarPorPasarela(SolicitudCompra $solicitud, string $pasarela): void
    {
        $this->activar(
            $solicitud,
            'Activación automática: pago aprobado por '.$pasarela.'.',
            true,
            $pasarela
        );
    }

    private function activar(SolicitudCompra $solicitud, ?string $observacion, bool $porPasarela, ?string $pasarela = null): void
    {
        DB::transaction(function () use ($solicitud, $observacion, $porPasarela, $pasarela) {
            $solicitud->load(['plan.modulos', 'pago']);

            if ($solicitud->estado_solicitud === 'VALIDADA') {
                return;
            }

            $plan = $solicitud->plan;
            $pago = $solicitud->pago;

            $usuario = User::firstOrCreate(
                ['correo' => $solicitud->correo],
                [
                    'nombres' => $solicitud->nombres,
                    'apellidos' => $solicitud->apellidos,
                    'documento_identidad' => $solicitud->documento_identidad,
                    'telefono' => $solicitud->telefono,
                    'email' => $solicitud->correo,
                    'password_hash' => $solicitud->documento_identidad,
                    'acepto_terminos' => true,
                    'fecha_aceptacion_terminos' => $solicitud->fecha_aceptacion_terminos ?? now(),
                    'estado' => 'ACTIVO',
                    'correo_verificado_en' => now(),
                ]
            );

            if ($usuario->estado !== 'ACTIVO') {
                $usuario->update(['estado' => 'ACTIVO']);
            }

            $rolCliente = Rol::where('codigo', 'CLIENTE')->value('id_rol');
            if ($rolCliente) {
                $usuario->roles()->syncWithoutDetaching([$rolCliente]);
            }

            $extra = is_array($solicitud->datos_extra_json) ? $solicitud->datos_extra_json : [];
            $medidas = $extra['medidas'] ?? [];
            $salud = $extra['salud'] ?? [];
            $idHorario = $extra['id_horario'] ?? ($extra['horario']['id_horario'] ?? null);

            $clientePayload = [
                'modalidad' => $solicitud->modalidad,
                'tipo_cliente' => $solicitud->tipo_cliente,
                'id_institucion' => $solicitud->id_institucion,
                'id_horario' => $idHorario,
                'tipo_documento' => $solicitud->tipo_documento ?? ($extra['tipo_documento'] ?? 'CC'),
                'documento' => $solicitud->documento ?? $solicitud->documento_identidad,
                'fecha_nacimiento' => $extra['fecha_nacimiento'] ?? null,
                'genero' => $extra['genero'] ?? null,
                'ciudad' => $solicitud->ciudad,
                'peso_inicial_kg' => $medidas['peso_kg'] ?? null,
                'estatura_cm' => $medidas['estatura_cm'] ?? null,
                'objetivo_principal' => $extra['objetivo_principal'] ?? null,
                'alergias' => $salud['alergias'] ?? null,
                'condiciones_medicas' => $salud['condiciones_medicas'] ?? null,
                'lesiones' => $salud['lesiones'] ?? null,
                'medicamentos' => $salud['medicamentos'] ?? null,
                'contacto_emergencia_nombre' => $salud['contacto_emergencia_nombre'] ?? null,
                'contacto_emergencia_telefono' => $salud['contacto_emergencia_telefono'] ?? null,
                'estado_cliente' => 'ACTIVA',
                'fecha_alta' => now()->toDateString(),
                'fecha_activacion' => now(),
            ];

            $cliente = Cliente::firstOrCreate(
                ['id_user' => $usuario->id_user],
                $clientePayload
            );

            $cliente->update(array_filter(
                array_merge($clientePayload, [
                    'estado_cliente' => 'ACTIVA',
                    'modalidad' => $solicitud->modalidad,
                ]),
                fn ($value) => $value !== null
            ));

            if ($idHorario) {
                HorarioPresencial::query()
                    ->where('id_horario', $idHorario)
                    ->increment('cupo_ocupado');
            }

            if (! empty($medidas['peso_kg']) && ! empty($medidas['estatura_cm'])) {
                MedidaAntropometricaInicial::updateOrCreate(
                    ['id_cliente' => $cliente->id_cliente],
                    [
                        'peso_kg' => $medidas['peso_kg'],
                        'estatura_cm' => $medidas['estatura_cm'],
                        'cintura_cm' => $medidas['cintura_cm'] ?? null,
                        'cadera_cm' => $medidas['cadera_cm'] ?? null,
                        'brazo_cm' => $medidas['brazo_cm'] ?? null,
                        'pierna_cm' => $medidas['pierna_cm'] ?? null,
                        'origen' => 'REGISTRO_PRESENCIAL',
                        'registrada_en' => now(),
                    ]
                );
            }

            if ($pago) {
                $pago->update([
                    'id_cliente' => $cliente->id_cliente,
                    'estado_pago' => 'VALIDADO',
                    'validado_por' => $porPasarela ? null : Auth::id(),
                    'fecha_validacion' => now(),
                    'observacion_admin' => $observacion,
                ]);

                if ($porPasarela || (filled($pago->pasarela) && ! in_array(strtoupper((string) $pago->pasarela), ['', 'NINGUNA', 'PENDIENTE'], true))) {
                    $this->comprobantes->asegurar($pago->fresh(), $solicitud, $pasarela);
                }
            }

            $inicio = now()->toDateString();
            $fin = now()->addDays((int) $plan->duracion_dias)->toDateString();

            $planCliente = PlanCliente::firstOrCreate(
                ['id_solicitud' => $solicitud->id_solicitud],
                [
                    'id_cliente' => $cliente->id_cliente,
                    'id_plan' => $plan->id_plan,
                    'id_pago' => $pago?->id_pago,
                    'fecha_inicio' => $inicio,
                    'fecha_fin' => $fin,
                    'estado_plan_cliente' => 'ACTIVO',
                    'dias_gracia' => $plan->dias_gracia ?? 3,
                ]
            );

            foreach ($plan->modulos as $modulo) {
                AccesoClienteModulo::firstOrCreate(
                    [
                        'id_plan_cliente' => $planCliente->id_plan_cliente,
                        'id_cliente' => $cliente->id_cliente,
                        'id_modulo' => $modulo->id_modulo,
                    ],
                    [
                        'fecha_inicio' => $inicio,
                        'fecha_fin' => $fin,
                        'estado_acceso' => 'ACTIVO',
                    ]
                );
            }

            $solicitud->update([
                'estado_solicitud' => 'VALIDADA',
                'revisado_por' => $porPasarela ? null : Auth::id(),
                'fecha_revision' => now(),
                'observacion_admin' => $observacion,
            ]);
        });
    }

    public function rechazar(SolicitudCompra $solicitud, ?string $observacion = null): void
    {
        DB::transaction(function () use ($solicitud, $observacion) {
            $solicitud->load('pago');

            $solicitud->update([
                'estado_solicitud' => 'RECHAZADA',
                'revisado_por' => Auth::id(),
                'fecha_revision' => now(),
                'observacion_admin' => $observacion,
            ]);

            if ($solicitud->pago) {
                $solicitud->pago->update([
                    'estado_pago' => 'RECHAZADO',
                    'validado_por' => Auth::id(),
                    'fecha_validacion' => now(),
                    'observacion_admin' => $observacion,
                ]);
            }

            $this->suspenderClienteDeSolicitud(
                $solicitud,
                $observacion ?: 'Solicitud rechazada por novedad en el pago.'
            );
        });
    }

    /**
     * Suspende el acceso del cliente cuando hay fraude o pago no reflejado.
     * La activación normal ocurre por pasarela; el admin solo actúa ante incidencias.
     */
    public function suspenderPorFraude(SolicitudCompra $solicitud, ?string $motivo = null): void
    {
        DB::transaction(function () use ($solicitud, $motivo) {
            $motivo = $motivo ?: 'Acceso suspendido: incidencia o comprobante de pago no válido.';

            $solicitud->update([
                'revisado_por' => Auth::id(),
                'fecha_revision' => now(),
                'observacion_admin' => $motivo,
            ]);

            if ($solicitud->pago) {
                $solicitud->pago->update([
                    'estado_pago' => 'RECHAZADO',
                    'validado_por' => Auth::id(),
                    'fecha_validacion' => now(),
                    'observacion_admin' => $motivo,
                ]);
            }

            if ($solicitud->estado_solicitud === 'PENDIENTE') {
                $solicitud->update(['estado_solicitud' => 'RECHAZADA']);
            }

            $this->suspenderClienteDeSolicitud($solicitud, $motivo);
        });
    }

    private function suspenderClienteDeSolicitud(SolicitudCompra $solicitud, string $motivo): void
    {
        $usuario = User::query()->where('correo', $solicitud->correo)->first();
        if (! $usuario) {
            return;
        }

        $cliente = Cliente::query()->where('id_user', $usuario->id_user)->first();
        if (! $cliente) {
            return;
        }

        $cliente->update(['estado_cliente' => 'SUSPENDIDO']);

        PlanCliente::query()
            ->where('id_cliente', $cliente->id_cliente)
            ->where('estado_plan_cliente', 'ACTIVO')
            ->update(['estado_plan_cliente' => 'SUSPENDIDO']);

        AccesoClienteModulo::query()
            ->where('id_cliente', $cliente->id_cliente)
            ->where('estado_acceso', 'ACTIVO')
            ->update(['estado_acceso' => 'INACTIVO']);

        $usuario->update([
            'estado' => 'SUSPENDIDO',
            'estado_cuenta' => 'SUSPENDIDO',
        ]);
    }
}
