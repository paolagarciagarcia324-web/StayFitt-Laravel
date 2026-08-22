<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RutinaEjercicio extends Model
{
    protected $table = 'rutina_ejercicios';

    protected $primaryKey = 'id_rutina_ejercicio';

    public $timestamps = false;

    protected $casts = [
        'peso_sugerido_kg' => 'decimal:2',
    ];

    protected $fillable = [
        'id_rutina',
        'id_ejercicio',
        'series',
        'repeticiones',
        'descanso_seg',
        'peso_sugerido_kg',
        'rir',
        'ritmo_ejecucion',
        'orden',
        'notas'
    ];
    public function rutina(): BelongsTo
    {
        return $this->belongsTo(Rutina::class, 'id_rutina', 'id_rutina');
    }
    public function ejercicio(): BelongsTo
    {
        return $this->belongsTo(Ejercicio::class, 'id_ejercicio', 'id_ejercicio');
    }
}
