<?php

namespace App\Services;

use App\Models\ConfiguracionPasarela;
use App\Models\Pago;
use App\Models\SolicitudCompra;
use App\Models\TransaccionPasarela;
use Illuminate\Support\Str;
use RuntimeException;

class PagoPasarelaSimulacionService
{
    public const CADENA = ['BOLD', 'EPAYCO', 'MERCADOPAGO'];

    public function cobrar(Pago $pago, SolicitudCompra $solicitud, array $input): string
    {
        $metodo = strtoupper((string) ($input['metodo_pago'] ?? ''));
        $payload = $this->payloadSeguro($metodo, $input);
        $intentos = [];

        foreach (self::CADENA as $codigo) {
            $config = ConfiguracionPasarela::query()
                ->where('codigo_pasarela', $codigo)
                ->first();

            if (! $config || ! $config->activa) {
                $intentos[] = ['pasarela' => $codigo, 'estado' => 'DECLINED', 'motivo' => 'inactiva'];
                TransaccionPasarela::query()->create([
                    'id_pago' => $pago->id_pago,
                    'id_solicitud' => $solicitud->id_solicitud,
                    'pasarela' => $codigo,
                    'referencia_interna' => $pago->referencia_pago.'-'.$codigo,
                    'monto' => $pago->monto,
                    'moneda' => $pago->moneda,
                    'estado_pasarela' => 'DECLINED',
                    'firma_valida' => false,
                    'idempotency_key' => $solicitud->codigo_solicitud.'-'.$codigo,
                    'payload_envio' => ['modo' => 'SIMULACION', 'motivo' => 'pasarela_inactiva'],
                    'intentos_activacion' => 0,
                    'activacion_ok' => false,
                ]);
                continue;
            }

            TransaccionPasarela::query()->create([
                'id_pago' => $pago->id_pago,
                'id_solicitud' => $solicitud->id_solicitud,
                'pasarela' => $codigo,
                'referencia_interna' => $pago->referencia_pago.'-'.$codigo,
                'referencia_externa' => 'SIM-'.strtoupper(Str::random(10)),
                'monto' => $pago->monto,
                'moneda' => $pago->moneda,
                'estado_pasarela' => 'APPROVED',
                'firma_valida' => true,
                'idempotency_key' => $solicitud->codigo_solicitud.'-'.$codigo,
                'payload_envio' => [
                    'modo' => 'SIMULACION',
                    'metodo' => $metodo,
                    'pasarela' => $codigo,
                    'datos' => $payload,
                    'cadena' => $intentos,
                ],
                'webhook_data' => [
                    'evento' => 'payment.approved',
                    'origen' => 'simulacion_checkout',
                    'aprobado_en' => now()->toIso8601String(),
                ],
                'intentos_activacion' => 1,
                'activacion_ok' => false,
            ]);

            $pago->update([
                'metodo' => $metodo,
                'metodo_pago' => $metodo,
                'pasarela' => $codigo,
            ]);

            $extra = is_array($solicitud->datos_extra_json) ? $solicitud->datos_extra_json : [];
            $extra['pago_simulado'] = [
                'metodo' => $metodo,
                'pasarela_ok' => $codigo,
                'intentos' => array_merge($intentos, [['pasarela' => $codigo, 'estado' => 'APPROVED']]),
                'datos' => $payload,
            ];
            $solicitud->update(['datos_extra_json' => $extra, 'origen' => 'PASARELA']);

            return $codigo;
        }

        throw new RuntimeException('No hay pasarela de simulación disponible.');
    }

    public function marcarActivada(SolicitudCompra $solicitud, string $pasarela): void
    {
        TransaccionPasarela::query()
            ->where('id_solicitud', $solicitud->id_solicitud)
            ->where('pasarela', $pasarela)
            ->update(['activacion_ok' => true]);
    }

    private function payloadSeguro(string $metodo, array $input): array
    {
        $base = [
            'tipo_documento' => $input['pago_tipo_documento'] ?? null,
            'documento' => $input['pago_documento'] ?? null,
            'correo' => $input['pago_correo'] ?? null,
            'telefono' => $input['pago_telefono'] ?? null,
            'acepto_recurrente' => true,
        ];

        if ($metodo === 'PSE') {
            return $base + ['banco' => $input['banco_pse'] ?? null];
        }

        $numero = preg_replace('/\D+/', '', (string) ($input['numero_tarjeta'] ?? '')) ?? '';

        return $base + [
            'token' => 'tok_'.Str::random(24),
            'last4' => substr($numero, -4),
            'marca' => $this->marca($numero),
            'vencimiento' => $input['vencimiento'] ?? null,
            'titular' => $input['titular'] ?? null,
            'direccion' => $input['direccion_pago'] ?? null,
            'ciudad' => $input['ciudad_pago'] ?? null,
        ];
    }

    private function marca(string $numero): string
    {
        return match (true) {
            str_starts_with($numero, '4') => 'VISA',
            str_starts_with($numero, '5') => 'MASTERCARD',
            str_starts_with($numero, '3') => 'AMEX',
            default => 'OTRA',
        };
    }
}
