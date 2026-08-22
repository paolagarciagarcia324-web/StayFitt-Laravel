<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialEntrenamiento extends Model
{
    protected $table = 'material_entrenamiento';

    protected $primaryKey = 'id_material';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'id_ejercicio',
        'id_rutina',
        'tipo',
        'titulo',
        'url_archivo',
        'contenido_texto'
    ];
    public function ejercicio(): BelongsTo
    {
        return $this->belongsTo(Ejercicio::class, 'id_ejercicio', 'id_ejercicio');
    }
    public function rutina(): BelongsTo
    {
        return $this->belongsTo(Rutina::class, 'id_rutina', 'id_rutina');
    }
}
