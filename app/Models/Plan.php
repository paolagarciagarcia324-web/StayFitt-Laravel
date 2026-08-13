<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Plan extends Model
{
    protected $table = 'planes';

    protected $primaryKey = 'id_plan';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $casts = [
        'caracteristicas_json' => 'array',
        'precio' => 'decimal:2',
        'requiere_coach' => 'boolean',
        'popular' => 'boolean',
        'destacado' => 'boolean',
        'renovacion_automatica' => 'boolean',
    ];

    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'caracteristicas_json',
        'precio',
        'moneda',
        'duracion_dias',
        'modalidad',
        'tipo_cliente',
        'cupo_maximo',
        'orden',
        'popular',
        'etiqueta',
        'estado_plan',
    ];

    public function modulos(): BelongsToMany
    {
        return $this->belongsToMany(
            ModuloServicio::class,
            'plan_modulos',
            'id_plan',
            'id_modulo',
            'id_plan',
            'id_modulo'
        )->withPivot('orden')->orderByPivot('orden');
    }

    public function scopeActivos($query)
    {
        return $query->where('estado_plan', 'ACTIVO')->orderBy('orden');
    }

    public function precioFormateado(): string
    {
        return '$'.number_format((float) $this->precio, 0, ',', '.').' '.$this->moneda;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
