<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrupoWhatsapp extends Model
{
    protected $table = 'grupos_whatsapp';

    protected $primaryKey = 'id_grupo_wa';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    protected $fillable = [
        'id_config_wa',
        'nombre',
        'tipo',
        'criterio',
        'link_invitacion',
        'estado',
    ];

    public function configuracion(): BelongsTo
    {
        return $this->belongsTo(ConfiguracionWhatsapp::class, 'id_config_wa', 'id_config_wa');
    }
}
