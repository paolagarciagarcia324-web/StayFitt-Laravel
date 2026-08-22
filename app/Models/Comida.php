<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comida extends Model
{
    protected $table = 'comidas';

    protected $primaryKey = 'id_comida';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $casts = [
        'calorias' => 'decimal:2',
        'proteinas_g' => 'decimal:2',
        'carbohidratos_g' => 'decimal:2',
        'grasas_g' => 'decimal:2',
        'fibra_g' => 'decimal:2',
        'azucar_g' => 'decimal:2',
        'sodio_mg' => 'decimal:2',
        'colesterol_mg' => 'decimal:2',
    ];

    protected $fillable = [
        'id_plan_nutricional',
        'tipo_comida',
        'nombre',
        'receta',
        'porcion',
        'calorias',
        'proteinas_g',
        'carbohidratos_g',
        'grasas_g',
        'fibra_g',
        'azucar_g',
        'sodio_mg',
        'colesterol_mg',
        'alergenos',
        'vitaminas_minerales',
        'orden'
    ];
    public function planNutricional(): BelongsTo
    {
        return $this->belongsTo(PlanNutricional::class, 'id_plan_nutricional', 'id_plan_nutricional');
    }
}
