<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComunicadoInstitucional extends Model
{
    protected $table = 'comunicados_institucionales';

    protected $primaryKey = 'id_comunicado';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    protected $fillable = [
        'id_institucion',
        'id_user_emisor',
        'titulo',
        'mensaje',
        'canal',
        'destinatarios',
    ];

    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class, 'id_institucion', 'id_institucion');
    }

    public function emisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user_emisor', 'id_user');
    }
}
