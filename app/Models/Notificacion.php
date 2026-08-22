<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notificacion extends Model
{
    protected $table = 'notificaciones';

    protected $primaryKey = 'id_notificacion';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $casts = [
        'leida' => 'boolean',
    ];

    protected $fillable = [
        'id_user',
        'tipo',
        'titulo',
        'mensaje',
        'leida',
        'canal',
        'referencia_tipo',
        'referencia_id',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
