<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuloServicio extends Model
{
    protected $table = 'modulos_servicio';

    protected $primaryKey = 'id_modulo';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'codigo',
        'codigo_modulo',
        'nombre',
        'nombre_modulo',
        'descripcion',
        'icono',
        'orden',
        'exclusivo_modalidad',
        'estado',
    ];

    public function codigoCanonico(): string
    {
        return strtoupper(trim((string) ($this->codigo ?: $this->codigo_modulo)));
    }

    public function nombreCanonico(): string
    {
        return trim((string) ($this->nombre ?: $this->nombre_modulo ?: $this->codigoCanonico()));
    }
}
