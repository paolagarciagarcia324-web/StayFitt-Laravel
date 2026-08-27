<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Rol extends Model
{
    protected $table = 'roles';

    protected $primaryKey = 'id_rol';

    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'codigo_rol',
        'nombre',
        'nombre_rol',
        'descripcion',
        'estado',
    ];

    public function codigoDisplay(): string
    {
        return strtoupper((string) ($this->codigo ?: $this->codigo_rol ?: ''));
    }

    public function nombreDisplay(): string
    {
        return (string) ($this->nombre ?: $this->nombre_rol ?: $this->codigoDisplay());
    }

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_roles',
            'id_rol',
            'id_user',
            'id_rol',
            'id_user'
        );
    }
}
