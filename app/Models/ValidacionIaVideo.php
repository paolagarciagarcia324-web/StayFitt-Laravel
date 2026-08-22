<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValidacionIaVideo extends Model
{
    protected $table = 'validaciones_ia_video';

    protected $primaryKey = 'id_validacion_ia';

    public $timestamps = false;

    protected $casts = [
        'detalle_json' => 'array',
        'registrada_en' => 'datetime',
    ];

    protected $fillable = [
        'id_progreso_video',
        'id_cliente',
        'repeticiones_detectadas',
        'repeticiones_correctas',
        'alertas_postura',
        'resultado',
        'detalle_json',
        'registrada_en'
    ];
    public function progresoVideo(): BelongsTo
    {
        return $this->belongsTo(ProgresoVideo::class, 'id_progreso_video', 'id_progreso_video');
    }
}
