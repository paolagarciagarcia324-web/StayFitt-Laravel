<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Institucion extends Model
{
    protected $table = 'instituciones';

    protected $primaryKey = 'id_institucion';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'codigo_institucion',
        'nombre',
        'nit',
        'correo_contacto',
        'telefono_contacto',
        'ciudad',
        'estado',
    ];
}
