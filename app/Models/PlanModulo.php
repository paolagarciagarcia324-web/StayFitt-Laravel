<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanModulo extends Model
{
    protected $table = 'plan_modulos';

    protected $primaryKey = 'id_plan_modulo';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $casts = [
        'incluido' => 'boolean',
    ];

    protected $fillable = [
        'id_plan',
        'id_modulo',
        'incluido',
        'orden'
    ];
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'id_plan', 'id_plan');
    }
    public function modulo(): BelongsTo
    {
        return $this->belongsTo(ModuloServicio::class, 'id_modulo', 'id_modulo');
    }
}
