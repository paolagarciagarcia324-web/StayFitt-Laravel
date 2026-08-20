<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionPasarela extends Model
{
    protected $table = 'configuracion_pasarelas';

    protected $primaryKey = 'id_config_pasarela';

    public $timestamps = false;

    protected $casts = [
        'activa' => 'boolean',
    ];

    protected $fillable = [
        'codigo_pasarela',
        'nombre',
        'modo',
        'activa',
        'actualizado_en',
    ];

    public function scopeActivas($query)
    {
        return $query->where('activa', true)->orderBy('nombre');
    }
}
