<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanCliente extends Model
{
    protected $table = 'planes_cliente';

    protected $primaryKey = 'id_plan_cliente';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'id_cliente',
        'id_plan',
        'id_solicitud',
        'id_pago',
        'fecha_inicio',
        'fecha_fin',
        'estado_plan_cliente',
        'dias_gracia',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'id_plan', 'id_plan');
    }

    public function accesos(): HasMany
    {
        return $this->hasMany(AccesoClienteModulo::class, 'id_plan_cliente', 'id_plan_cliente');
    }
}
