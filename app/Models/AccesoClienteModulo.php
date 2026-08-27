<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccesoClienteModulo extends Model
{
    protected $table = 'acceso_cliente_modulo';

    protected $primaryKey = 'id_acceso';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'id_plan_cliente',
        'id_cliente',
        'id_modulo',
        'fecha_inicio',
        'fecha_fin',
        'estado_acceso',
    ];

    public function planCliente(): BelongsTo
    {
        return $this->belongsTo(PlanCliente::class, 'id_plan_cliente', 'id_plan_cliente');
    }

    public function modulo(): BelongsTo
    {
        return $this->belongsTo(ModuloServicio::class, 'id_modulo', 'id_modulo');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }
}
