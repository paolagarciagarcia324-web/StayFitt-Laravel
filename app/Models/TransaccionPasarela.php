<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaccionPasarela extends Model
{
    protected $table = 'transacciones_pasarela';

    protected $primaryKey = 'id_transaccion';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizada_en';

    protected $casts = [
        'monto' => 'decimal:2',
        'firma_valida' => 'boolean',
        'activacion_ok' => 'boolean',
        'payload_envio' => 'array',
        'webhook_data' => 'array',
    ];

    protected $fillable = [
        'id_pago',
        'id_solicitud',
        'pasarela',
        'referencia_interna',
        'referencia_externa',
        'monto',
        'moneda',
        'estado_pasarela',
        'firma_valida',
        'idempotency_key',
        'payload_envio',
        'webhook_data',
        'intentos_activacion',
        'activacion_ok',
    ];

    public function pago(): BelongsTo
    {
        return $this->belongsTo(Pago::class, 'id_pago', 'id_pago');
    }

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(SolicitudCompra::class, 'id_solicitud', 'id_solicitud');
    }
}
