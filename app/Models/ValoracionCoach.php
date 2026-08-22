<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValoracionCoach extends Model
{
    protected $table = 'valoraciones_coach';

    protected $primaryKey = 'id_valoracion';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'id_coach',
        'id_cliente',
        'calificacion',
        'comentario'
    ];
    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class, 'id_coach', 'id_coach');
    }
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }
}
