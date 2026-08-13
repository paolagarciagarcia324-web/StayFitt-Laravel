<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuloServicio extends Model
{
    protected $table = 'modulos_servicio';

    protected $primaryKey = 'id_modulo';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';
}
