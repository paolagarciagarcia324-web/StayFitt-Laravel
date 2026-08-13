<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuarios';

    protected $primaryKey = 'id_user';

    public $timestamps = true;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'nombres',
        'apellidos',
        'Tipo_documento',
        'documento_identidad',
        'correo',
        'telefono',
        'password_hash',
        'estado',
        'acepto_terminos',
        'fecha_aceptacion_terminos',
        'correo_verificado_en',
        'ultimo_acceso_en',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected function casts(): array
    {
        return [
            'acepto_terminos' => 'boolean',
            'fecha_aceptacion_terminos' => 'datetime',
            'correo_verificado_en' => 'datetime',
            'ultimo_acceso_en' => 'datetime',
            'password_hash' => 'hashed',
        ];
    }

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function getEmailForPasswordReset(): string
    {
        return $this->correo;
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Rol::class,
            'user_roles',
            'id_user',
            'id_rol',
            'id_user',
            'id_rol'
        );
    }

    public function tieneRol(string $codigo): bool
    {
        return $this->roles()->where('codigo', $codigo)->exists();
    }
}
