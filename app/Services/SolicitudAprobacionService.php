<?php

namespace App\Services;

use App\Models\AccesoClienteModulo;
use App\Models\Cliente;
use App\Models\Pago;
use App\Models\PlanCliente;
use App\Models\Rol;
use App\Models\SolicitudCompra;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SolicitudAprobacionService
{
    public function aprobar(SolicitudCompra $solicitud, ?string $observacion = null): void
    {
        DB::transaction(function () use ($solicitud, $observacion) {
            $solicitud->load(['plan.modulos', 'pago']);
            $plan = $solicitud->plan;
            $pago = $solicitud->pago;

            $usuario = User::firstOrCreate(
                ['correo' => $solicitud->correo],
                [
                    'nombres' => $solicitud->nombres,
                    'apellidos' => $solicitud->apellidos,
                    'documento_identidad' => $solicitud->documento_identidad,
                    'telefono' => $solicitud->telefono,
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

            $cliente = Cliente::firstOrCreate(
                ['id_user' => $usuario->id_user],
                [
                    'modalidad' => $solicitud->modalidad,
                    'tipo_cliente' => $solicitud->tipo_cliente,
                    'id_institucion' => $solicitud->id_institucion,
                    'estado_cliente' => 'ACTIVA',
                    'fecha_alta' => now()->toDateString(),
                ]
            );

            if ($cliente->estado_cliente !== 'ACTIVA') {
                $cliente->update(['estado_cliente' => 'ACTIVA', 'modalidad' => $solicitud->modalidad]);
            }

            if ($pago) {
                $pago->update([
                    'id_cliente' => $cliente->id_cliente,
                    'estado_pago' => 'VALIDADO',
                    'validado_por' => Auth::id(),
                    'fecha_validacion' => now(),
                    'observacion_admin' => $observacion,
                ]);
            }

            $inicio = now()->toDateString();
            $fin = now()->addDays((int) $plan->duracion_dias)->toDateString();

            $planCliente = PlanCliente::create([
                'id_cliente' => $cliente->id_cliente,
                'id_plan' => $plan->id_plan,
                'id_solicitud' => $solicitud->id_solicitud,
                'id_pago' => $pago?->id_pago,
                'fecha_inicio' => $inicio,
                'fecha_fin' => $fin,
                'estado_plan_cliente' => 'ACTIVO',
                'dias_gracia' => $plan->dias_gracia ?? 3,
            ]);

            foreach ($plan->modulos as $modulo) {
                AccesoClienteModulo::create([
                    'id_plan_cliente' => $planCliente->id_plan_cliente,
                    'id_cliente' => $cliente->id_cliente,
                    'id_modulo' => $modulo->id_modulo,
                    'fecha_inicio' => $inicio,
                    'fecha_fin' => $fin,
                    'estado_acceso' => 'ACTIVO',
                ]);
            }

            $solicitud->update([
                'estado_solicitud' => 'VALIDADA',
                'revisado_por' => Auth::id(),
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
        });
    }
}
