<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialAsignacionCoach extends Model
{
    protected $table = 'historial_asignacion_coach';

    protected $primaryKey = 'id_asignacion';

    public $timestamps = false;

    protected $fillable = [
        'id_cliente',
        'id_coach_anterior',
        'id_coach_nuevo',
        'id_admin',
        'motivo',
        'asignado_en',
    ];

    protected function casts(): array
    {
        return [
            'asignado_en' => 'datetime',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }
}
