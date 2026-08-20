<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConfiguracionWhatsapp extends Model
{
    protected $table = 'configuracion_whatsapp';

    protected $primaryKey = 'id_config_wa';

    public $timestamps = false;

    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'numero_business',
        'api_token_cifrado',
        'link_grupo_principal',
        'mensaje_bienvenida',
        'horario_atencion',
        'respuestas_automaticas',
        'activa',
    ];

    protected function casts(): array
    {
        return [
            'activa' => 'boolean',
            'respuestas_automaticas' => 'array',
        ];
    }

    public function grupos(): HasMany
    {
        return $this->hasMany(GrupoWhatsapp::class, 'id_config_wa', 'id_config_wa');
    }
}
