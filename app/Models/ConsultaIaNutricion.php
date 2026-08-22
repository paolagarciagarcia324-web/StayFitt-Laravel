<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultaIaNutricion extends Model
{
    protected $table = 'consultas_ia_nutricion';

    protected $primaryKey = 'id_consulta_ia';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $casts = [
        'cumple_estructura' => 'boolean',
        'alimentos_detectados' => 'array',
        'macros_estimados' => 'array',
        'alternativas_json' => 'array',
        'registrada_en' => 'datetime',
    ];

    protected $fillable = [
        'id_cliente',
        'id_plan_nutricional',
        'tipo',
        'foto_url',
        'alimentos_detectados',
        'cumple_estructura',
        'alerta',
        'macros_estimados',
        'alternativas_json',
        'respuesta_ia',
        'registrada_en'
    ];
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }
}
