<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FotoProgreso extends Model
{
    protected $table = 'fotos_progreso';

    protected $primaryKey = 'id_foto';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'id_registro_progreso',
        'angulo',
        'url_foto'
    ];
    public function registroProgreso(): BelongsTo
    {
        return $this->belongsTo(RegistroProgreso::class, 'id_registro_progreso', 'id_registro_progreso');
    }
}
