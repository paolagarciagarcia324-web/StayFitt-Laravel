<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recordatorio extends Model
{
    protected $table = 'recordatorios';

    protected $primaryKey = 'id_recordatorio';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $casts = [
        'activo' => 'boolean',
        'fecha_hora' => 'datetime',
    ];

    protected $fillable = [
        'id_cliente',
        'id_coach',
        'tipo',
        'titulo',
        'mensaje',
        'recurrencia',
        'dias_semana',
        'fecha_hora',
        'minutos_antes',
        'activo'
    ];
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }
}
