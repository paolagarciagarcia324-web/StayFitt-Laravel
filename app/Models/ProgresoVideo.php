<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgresoVideo extends Model
{
    protected $table = 'progreso_videos';

    protected $primaryKey = 'id_progreso_video';

    public $timestamps = false;

    protected $casts = [
        'llama_racha' => 'boolean',
        'porcentaje' => 'decimal:2',
        'iniciado_en' => 'datetime',
        'completado_en' => 'datetime',
    ];

    protected $fillable = [
        'id_cliente',
        'id_video',
        'id_plan_cliente',
        'estado',
        'iniciado_en',
        'completado_en',
        'porcentaje',
        'llama_racha'
    ];
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }
    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class, 'id_video', 'id_video');
    }
}
