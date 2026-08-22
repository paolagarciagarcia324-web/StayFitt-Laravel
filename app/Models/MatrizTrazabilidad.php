<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatrizTrazabilidad extends Model
{
    protected $table = 'matriz_trazabilidad';

    protected $primaryKey = 'id_trazabilidad';

    public $timestamps = false;

    protected $fillable = [
        'id_rf',
        'id_hu',
        'id_cu',
        'modulo',
        'actor_principal',
        'tablas_relacionadas'
    ];
}
