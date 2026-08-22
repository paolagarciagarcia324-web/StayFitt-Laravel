<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialEstadoCliente extends Model
{
    protected $table = 'historial_estados_cliente';

    protected $primaryKey = 'id_historial';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $casts = [
        'fecha_cambio' => 'datetime',
    ];

    protected $fillable = [
        'id_cliente',
        'estado_anterior',
        'estado_nuevo',
        'motivo',
        'id_user_responsable',
        'cambiado_por',
        'fecha_cambio'
    ];
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }
    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user_responsable', 'id_user');
    }
}
