<?php

namespace App\Services;

use App\Models\ConfiguracionPasarela;
use App\Models\Pago;
use App\Models\SolicitudCompra;
use App\Models\TransaccionPasarela;
use Illuminate\Support\Str;

class PagoPasarelaSimulacionService
{
    public function __construct(private ComprobantePasarelaService $comprobantes) {}

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

            $suffix = Str::random(6);
            $refInterna = Str::limit($pago->referencia_pago.'-'.$codigo.'-'.$suffix, 80, '');
            $idempotency = Str::limit($solicitud->codigo_solicitud.'-'.$codigo.'-'.$suffix, 80, '');

            if (! $config || ! $config->activa) {
                $intentos[] = ['pasarela' => $codigo, 'estado' => 'DECLINED', 'motivo' => 'inactiva'];
                TransaccionPasarela::query()->create([
                    'id_pago' => $pago->id_pago,
                    'id_solicitud' => $solicitud->id_solicitud,
                    'pasarela' => $codigo,
                    'referencia_interna' => $refInterna,
                    'monto' => $pago->monto,
                    'moneda' => $pago->moneda,
                    'estado_pasarela' => 'DECLINED',
                    'firma_valida' => false,
                    'idempotency_key' => $idempotency,
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
                'referencia_interna' => $refInterna,
                'referencia_externa' => 'SIM-'.strtoupper(Str::random(10)),
                'monto' => $pago->monto,
                'moneda' => $pago->moneda,
                'estado_pasarela' => 'APPROVED',
                'firma_valida' => true,
                'idempotency_key' => $idempotency,
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
                'estado_pago' => 'VALIDADO',
                'fecha_validacion' => now(),
            ]);

            $this->comprobantes->asegurar($pago->fresh(), $solicitud, $codigo);

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

        // Si ninguna pasarela de la cadena principal está activa, buscar cualquier pasarela activa en BD
        // o utilizar la primera de respaldo para garantizar que la simulación de registro nunca se bloquee.
        $fallbackPasarela = ConfiguracionPasarela::query()->where('activa', true)->first();
        $fallbackCodigo = $fallbackPasarela?->codigo_pasarela ?? self::CADENA[0] ?? 'BOLD';

        $suffix = Str::random(6);
        $refInterna = Str::limit($pago->referencia_pago.'-'.$fallbackCodigo.'-SIM-'.$suffix, 80, '');
        $idempotency = Str::limit($solicitud->codigo_solicitud.'-'.$fallbackCodigo.'-SIM-'.$suffix, 80, '');

        TransaccionPasarela::query()->create([
            'id_pago' => $pago->id_pago,
            'id_solicitud' => $solicitud->id_solicitud,
            'pasarela' => $fallbackCodigo,
            'referencia_interna' => $refInterna,
            'referencia_externa' => 'SIM-'.strtoupper(Str::random(10)),
            'monto' => $pago->monto,
            'moneda' => $pago->moneda,
            'estado_pasarela' => 'APPROVED',
            'firma_valida' => true,
            'idempotency_key' => $idempotency,
            'payload_envio' => [
                'modo' => 'SIMULACION',
                'metodo' => $metodo,
                'pasarela' => $fallbackCodigo,
                'datos' => $payload,
                'cadena' => $intentos,
                'fallback' => true,
            ],
            'webhook_data' => [
                'evento' => 'payment.approved',
                'origen' => 'simulacion_checkout_fallback',
                'aprobado_en' => now()->toIso8601String(),
            ],
            'intentos_activacion' => 1,
            'activacion_ok' => false,
        ]);

        $pago->update([
            'metodo' => $metodo,
            'metodo_pago' => $metodo,
            'pasarela' => $fallbackCodigo,
            'estado_pago' => 'VALIDADO',
            'fecha_validacion' => now(),
        ]);

        $this->comprobantes->asegurar($pago->fresh(), $solicitud, $fallbackCodigo);

        $extra = is_array($solicitud->datos_extra_json) ? $solicitud->datos_extra_json : [];
        $extra['pago_simulado'] = [
            'metodo' => $metodo,
            'pasarela_ok' => $fallbackCodigo,
            'intentos' => array_merge($intentos, [['pasarela' => $fallbackCodigo, 'estado' => 'APPROVED', 'modo' => 'FALLBACK_SIMULACION']]),
            'datos' => $payload,
        ];
        $solicitud->update(['datos_extra_json' => $extra, 'origen' => 'PASARELA']);

        return $fallbackCodigo;
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
            'titular' => $input['titular'] ?? null,
            'ciudad' => $input['ciudad_pago'] ?? null,
            'acepto_recurrente' => filter_var($input['acepto_recurrente'] ?? false, FILTER_VALIDATE_BOOLEAN),
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
            'direccion' => $input['direccion_pago'] ?? null,
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
