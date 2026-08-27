<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracionWhatsapp;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WhatsappWebhookController extends Controller
{
    /**
     * Verificación inicial del webhook (Meta Cloud API).
     */
    public function verify(Request $request): Response
    {
        $mode = $request->query('hub_mode') ?? $request->query('hub.mode');
        $token = $request->query('hub_verify_token') ?? $request->query('hub.verify_token');
        $challenge = $request->query('hub_challenge') ?? $request->query('hub.challenge');

        $config = ConfiguracionWhatsapp::query()
            ->where('activa', true)
            ->whereNotNull('api_token_cifrado')
            ->orderByDesc('id_config_wa')
            ->first()
            ?? ConfiguracionWhatsapp::query()->where('activa', true)->orderByDesc('id_config_wa')->first();

        $expected = $config?->apiTokenPlano();

        if ($mode === 'subscribe' && $expected && hash_equals($expected, (string) $token)) {
            return response((string) $challenge, 200);
        }

        abort(403, 'Token de verificación inválido.');
    }

    /**
     * Recepción de eventos (stub: registra payload para integración futura).
     */
    public function handle(Request $request): Response
    {
        $config = ConfiguracionWhatsapp::query()->where('activa', true)->first();
        $secret = $config?->apiTokenPlano();

        if ($secret) {
            $signature = $request->header('X-Hub-Signature-256');
            $payload = $request->getContent();
            $expected = 'sha256='.hash_hmac('sha256', $payload, $secret);

            if (! $signature || ! hash_equals($expected, $signature)) {
                Log::warning('WhatsApp webhook: firma inválida');

                return response('Invalid signature', 403);
            }
        }

        Log::info('WhatsApp webhook recibido', [
            'payload' => $request->all(),
        ]);

        return response('EVENT_RECEIVED', 200);
    }
}
