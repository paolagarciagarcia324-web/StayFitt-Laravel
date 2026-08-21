<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionPasarela extends Model
{
    protected $table = 'configuracion_pasarelas';

    protected $primaryKey = 'id_config_pasarela';

    public $timestamps = false;

    protected $casts = [
        'activa' => 'boolean',
    ];

    protected $fillable = [
        'codigo_pasarela',
        'nombre',
        'logo_url',
        'modo',
        'activa',
        'actualizado_en',
    ];

    public function scopeActivas($query)
    {
        return $query->where('activa', true)->orderBy('nombre');
    }

    public function logoSrc(): string
    {
        $path = trim((string) $this->logo_url);
        if ($path === '') {
            return '';
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return asset('storage/'.$path);
    }
}
