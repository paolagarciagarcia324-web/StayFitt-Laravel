<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
