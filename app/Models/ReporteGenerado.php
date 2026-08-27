<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReporteGenerado extends Model
{
    protected $table = 'reportes_generados';

    protected $primaryKey = 'id_reporte';

    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'tipo',
        'titulo',
        'parametros_json',
        'formato',
        'estado',
        'registros_count',
        'error_mensaje',
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

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
