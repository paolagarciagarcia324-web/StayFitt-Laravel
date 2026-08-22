<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogEjercicioDetalle extends Model
{
    protected $table = 'logs_ejercicio_detalle';

    protected $primaryKey = 'id_log';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $casts = [
        'peso_kg' => 'decimal:2',
    ];

    protected $fillable = [
        'id_registro_rutina',
        'id_rutina_ejercicio',
        'series_realizadas',
        'repeticiones_realizadas',
        'peso_kg',
        'rir_real',
        'observacion'
    ];
    public function registroRutina(): BelongsTo
    {
        return $this->belongsTo(RegistroRutina::class, 'id_registro_rutina', 'id_registro_rutina');
    }
}
