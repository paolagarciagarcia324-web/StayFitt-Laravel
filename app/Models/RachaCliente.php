<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RachaCliente extends Model
{
    protected $table = 'rachas_cliente';

    protected $primaryKey = 'id_racha';

    public $timestamps = false;

    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'id_cliente',
        'tipo',
        'dias_consecutivos',
        'mejor_racha',
        'ultima_fecha',
    ];

    protected function casts(): array
    {
        return [
            'ultima_fecha' => 'date',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }
}
