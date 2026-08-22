<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntentoActivacion extends Model
{
    protected $table = 'intentos_activacion';

    protected $primaryKey = 'id_intento';

    public $timestamps = false;

    protected $casts = [
        'ejecutado_en' => 'datetime',
    ];

    protected $fillable = [
        'id_transaccion',
        'nro_intento',
        'resultado',
        'detalle',
        'ejecutado_en'
    ];
    public function transaccion(): BelongsTo
    {
        return $this->belongsTo(TransaccionPasarela::class, 'id_transaccion', 'id_transaccion');
    }
}
