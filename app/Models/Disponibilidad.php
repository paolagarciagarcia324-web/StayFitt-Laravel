<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Disponibilidad extends Model
{
    protected $table = 'disponibilidades';

    protected $primaryKey = 'id_disponibilidad';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $casts = [
        'activo' => 'boolean',
    ];

    protected $fillable = [
        'id_coach',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'modalidad',
        'activo',
    ];

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class, 'id_coach', 'id_coach');
    }
}
