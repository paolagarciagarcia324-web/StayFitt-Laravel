<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rutina extends Model
{
    protected $table = 'rutinas';

    protected $primaryKey = 'id_rutina';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'id_plan_entrenamiento',
        'nombre',
        'descripcion',
        'dia_semana',
        'orden',
        'duracion_min',
        'estado'
    ];
    public function planEntrenamiento(): BelongsTo
    {
        return $this->belongsTo(PlanEntrenamiento::class, 'id_plan_entrenamiento', 'id_plan_entrenamiento');
    }
}
