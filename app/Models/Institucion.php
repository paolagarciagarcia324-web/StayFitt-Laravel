<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Institucion extends Model
{
    protected $table = 'instituciones';

    protected $primaryKey = 'id_institucion';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'nombre',
        'nit',
        'contacto_nombre',
        'correo',
        'telefono',
        'ciudad',
        'direccion',
        'convenio',
        'cupos_totales',
        'cupos_usados',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
        ];
    }

    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class, 'id_institucion', 'id_institucion');
    }

    public function enlaces(): HasMany
    {
        return $this->hasMany(EnlaceRegistroInstitucional::class, 'id_institucion', 'id_institucion');
    }
}
