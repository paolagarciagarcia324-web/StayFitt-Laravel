<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EncuestaSatisfaccion extends Model
{
    protected $table = 'encuestas_satisfaccion';

    protected $primaryKey = 'id_encuesta';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $casts = [
        'recomienda_plan' => 'boolean',
    ];

    protected $fillable = [
        'id_cliente',
        'id_plan_cliente',
        'calificacion',
        'comentario',
        'recomienda_plan'
    ];
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }
}
