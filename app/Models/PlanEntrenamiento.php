<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanEntrenamiento extends Model
{
    protected $table = 'planes_entrenamiento';

    protected $primaryKey = 'id_plan_entrenamiento';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $casts = [
        'validado_en' => 'datetime',
    ];

    protected $fillable = [
        'id_cliente',
        'id_coach',
        'id_plan_cliente',
        'nombre',
        'objetivo',
        'modalidad',
        'estado',
        'id_admin_validador',
        'validado_en'
    ];
    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class, 'id_coach', 'id_coach');
    }
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function rutinas(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Rutina::class, 'id_plan_entrenamiento', 'id_plan_entrenamiento')
            ->orderBy('orden');
    }
}
