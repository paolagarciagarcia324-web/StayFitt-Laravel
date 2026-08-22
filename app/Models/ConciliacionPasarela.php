<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConciliacionPasarela extends Model
{
    protected $table = 'conciliaciones_pasarela';

    protected $primaryKey = 'id_conciliacion';

    public $timestamps = false;

    protected $casts = [
        'coincidencia' => 'boolean',
        'respuesta_json' => 'array',
        'ejecutada_en' => 'datetime',
    ];

    protected $fillable = [
        'id_transaccion',
        'estado_consultado',
        'coincidencia',
        'respuesta_json',
        'ejecutada_en'
    ];
    public function transaccion(): BelongsTo
    {
        return $this->belongsTo(TransaccionPasarela::class, 'id_transaccion', 'id_transaccion');
    }
}
