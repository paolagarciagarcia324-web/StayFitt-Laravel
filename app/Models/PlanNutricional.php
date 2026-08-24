<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanNutricional extends Model
{
    protected $table = 'planes_nutricionales';

    protected $primaryKey = 'id_plan_nutricional';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $casts = [
        'proteinas_g' => 'decimal:2',
        'carbohidratos_g' => 'decimal:2',
        'grasas_g' => 'decimal:2',
    ];

    protected $fillable = [
        'id_cliente',
        'id_coach',
        'id_plan_cliente',
        'id_propuesta',
        'nombre',
        'objetivo',
        'kcal_objetivo',
        'proteinas_g',
        'carbohidratos_g',
        'grasas_g',
        'recomendaciones',
        'estado',
    ];

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class, 'id_coach', 'id_coach');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function comidas(): HasMany
    {
        return $this->hasMany(Comida::class, 'id_plan_nutricional', 'id_plan_nutricional')->orderBy('orden');
    }
}
