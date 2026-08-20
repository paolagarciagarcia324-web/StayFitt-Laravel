<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnlaceRegistroInstitucional extends Model
{
    protected $table = 'enlaces_registro_institucional';

    protected $primaryKey = 'id_enlace';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    protected $fillable = [
        'id_institucion',
        'id_plan',
        'token',
        'cupos_maximos',
        'usos_actuales',
        'fecha_expiracion',
        'estado',
        'creado_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha_expiracion' => 'datetime',
        ];
    }

    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class, 'id_institucion', 'id_institucion');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'id_plan', 'id_plan');
    }
}
