<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventoWebhook extends Model
{
    protected $table = 'eventos_webhook';

    protected $primaryKey = 'id_evento_webhook';

    public $timestamps = false;

    protected $casts = [
        'firma_valida' => 'boolean',
        'procesado' => 'boolean',
        'headers_json' => 'array',
        'payload_json' => 'array',
        'recibido_en' => 'datetime',
    ];

    protected $fillable = [
        'id_transaccion',
        'pasarela',
        'event_id_externo',
        'headers_json',
        'payload_json',
        'firma_recibida',
        'firma_valida',
        'procesado',
        'error_proceso',
        'recibido_en'
    ];
    public function transaccion(): BelongsTo
    {
        return $this->belongsTo(TransaccionPasarela::class, 'id_transaccion', 'id_transaccion');
    }
}
