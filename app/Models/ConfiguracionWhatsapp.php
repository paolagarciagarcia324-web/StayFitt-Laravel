<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

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

    public function setApiTokenAttribute(?string $value): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $this->attributes['api_token_cifrado'] = Crypt::encryptString($value);
    }

    public function apiTokenPlano(): ?string
    {
        $cifrado = $this->attributes['api_token_cifrado'] ?? null;
        if (! $cifrado) {
            return null;
        }

        try {
            return Crypt::decryptString($cifrado);
        } catch (\Throwable) {
            return null;
        }
    }

    public function tieneApiToken(): bool
    {
        return filled($this->attributes['api_token_cifrado'] ?? null);
    }
}
