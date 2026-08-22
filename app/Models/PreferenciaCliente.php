<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreferenciaCliente extends Model
{
    protected $table = 'preferencias_cliente';

    protected $primaryKey = 'id_preferencia';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $casts = [
        'notif_email' => 'boolean',
        'notif_push' => 'boolean',
        'notif_whatsapp' => 'boolean',
    ];

    protected $fillable = [
        'id_cliente',
        'notif_email',
        'notif_push',
        'notif_whatsapp',
        'horario_preferido',
        'recordatorio_minutos',
        'idioma',
        'privacidad_fotos'
    ];
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }
}
