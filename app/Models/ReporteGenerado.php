<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReporteGenerado extends Model
{
    protected $table = 'reportes_generados';

    protected $primaryKey = 'id_reporte';

    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'tipo',
        'parametros_json',
        'formato',
        'url_archivo',
        'generado_en',
    ];

    protected function casts(): array
    {
        return [
            'parametros_json' => 'array',
            'generado_en' => 'datetime',
        ];
    }
}
