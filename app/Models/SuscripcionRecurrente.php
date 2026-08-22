<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuscripcionRecurrente extends Model
{
    protected $table = 'suscripciones_recurrentes';

    protected $primaryKey = 'id_suscripcion';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $casts = [
        'proximo_cobro' => 'date',
    ];

    protected $fillable = [
        'id_cliente',
        'id_plan',
        'id_plan_cliente',
        'periodicidad',
        'token_pasarela',
        'pasarela',
        'estado',
        'proximo_cobro'
    ];
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'id_plan', 'id_plan');
    }
}
