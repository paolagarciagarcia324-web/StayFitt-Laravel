<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DescuentoCupon extends Model
{
    protected $table = 'descuentos_cupones';

    protected $primaryKey = 'id_descuento';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'codigo',
        'tipo',
        'tipo_descuento',
        'valor',
        'id_plan',
        'fecha_inicio',
        'fecha_fin',
        'usos_maximos',
        'uso_maximo',
        'usos_actuales',
        'estado',
        'estado_cupon',
        'creado_por',
    ];

    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
            'fecha_inicio' => 'datetime',
            'fecha_fin' => 'datetime',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'id_plan', 'id_plan');
    }
}
