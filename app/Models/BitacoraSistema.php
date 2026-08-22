<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BitacoraSistema extends Model
{
    protected $table = 'bitacora_sistema';

    protected $primaryKey = 'id_bitacora';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $casts = [
        'datos_antes' => 'array',
        'datos_despues' => 'array',
    ];

    protected $fillable = [
        'id_user',
        'accion',
        'entidad',
        'id_entidad',
        'datos_antes',
        'datos_despues',
        'ip',
        'resultado',
        'mensaje'
    ];
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
