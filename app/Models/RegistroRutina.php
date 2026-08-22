<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistroRutina extends Model
{
    protected $table = 'registro_rutinas';

    protected $primaryKey = 'id_registro_rutina';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $casts = [
        'completada' => 'boolean',
        'fecha_ejecucion' => 'date',
    ];

    protected $fillable = [
        'id_cliente',
        'id_rutina',
        'fecha_ejecucion',
        'completada',
        'dificultad',
        'observacion_cliente'
    ];
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }
    public function rutina(): BelongsTo
    {
        return $this->belongsTo(Rutina::class, 'id_rutina', 'id_rutina');
    }
}
