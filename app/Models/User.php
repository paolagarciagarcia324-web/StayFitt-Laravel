<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

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
        'email',
        'codigo_pais',
        'telefono',
        'password_hash',
        'password',
        'estado',
        'estado_cuenta',
        'acepto_terminos',
        'fecha_aceptacion_terminos',
        'correo_verificado_en',
        'email_verificado_en',
        'ultimo_acceso_en',
        'remember_token',
        'name',
        'foto_perfil_url',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'acepto_terminos' => 'boolean',
            'fecha_aceptacion_terminos' => 'datetime',
            'correo_verificado_en' => 'datetime',
            'email_verificado_en' => 'datetime',
            'ultimo_acceso_en' => 'datetime',
        ];
    }

    public function getAuthPassword(): string
    {
        return (string) ($this->attributes['password_hash'] ?? '');
    }

    public function getEmailForPasswordReset(): string
    {
        return (string) ($this->correo ?: $this->email);
    }

    public function routeNotificationForMail(): string
    {
        return $this->getEmailForPasswordReset();
    }

    public function avatarUrl(): ?string
    {
        if (empty($this->foto_perfil_url)) {
            return null;
        }

        $path = trim((string) $this->foto_perfil_url);
        if ($path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        if (str_starts_with($path, 'images/') || str_starts_with($path, 'css/') || str_starts_with($path, 'js/')) {
            return asset($path);
        }

        return asset('storage/'.$path);
    }

    public function getNameAttribute(): string
    {
        $full = trim(($this->attributes['nombres'] ?? '').' '.($this->attributes['apellidos'] ?? ''));

        return $full !== '' ? $full : (string) ($this->attributes['nombres'] ?? '');
    }

    public function setNameAttribute(string $value): void
    {
        $parts = preg_split('/\s+/', trim($value), 2) ?: [$value];
        $this->attributes['nombres'] = $parts[0] ?? $value;
        $this->attributes['apellidos'] = $parts[1] ?? $parts[0] ?? 'StayFit';
    }

    public function getEmailAttribute(): ?string
    {
        return $this->attributes['email'] ?? $this->attributes['correo'] ?? null;
    }

    public function setEmailAttribute(?string $value): void
    {
        $this->attributes['email'] = $value;
        $this->attributes['correo'] = $value;
    }

    public function setCorreoAttribute(?string $value): void
    {
        $this->attributes['correo'] = $value;
        $this->attributes['email'] = $value;
    }

    public function getPasswordAttribute(): string
    {
        return $this->getAuthPassword();
    }

    public function setPasswordAttribute(?string $value): void
    {
        $this->setPasswordHashAttribute($value);
    }

    public function getEmailVerifiedAtAttribute()
    {
        return $this->correo_verificado_en ?? $this->email_verificado_en;
    }

    public function setEmailVerifiedAtAttribute($value): void
    {
        $this->attributes['correo_verificado_en'] = $value;
        $this->attributes['email_verificado_en'] = $value;
    }

    public function setPasswordHashAttribute(?string $value): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $this->attributes['password_hash'] = $this->isHashedPassword($value)
            ? $value
            : Hash::make($value);
    }

    public function setEstadoAttribute(?string $value): void
    {
        $this->attributes['estado'] = $value;
        $this->attributes['estado_cuenta'] = $value;
    }

    public function validatePassword(string $plain): bool
    {
        $stored = $this->getAuthPassword();

        if ($stored === '') {
            return false;
        }

        if ($this->isHashedPassword($stored)) {
            return Hash::check($plain, $stored);
        }

        if (! hash_equals($stored, $plain)) {
            return false;
        }

        $this->password_hash = $plain;
        $this->save();

        return true;
    }

    public function isHashedPassword(?string $value = null): bool
    {
        $value ??= $this->getAuthPassword();

        return is_string($value) && (
            str_starts_with($value, '$2y$')
            || str_starts_with($value, '$2a$')
            || str_starts_with($value, '$2b$')
            || str_starts_with($value, '$argon2i$')
            || str_starts_with($value, '$argon2id$')
        );
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
        return $this->roles()->where(function ($query) use ($codigo) {
            $query->where('codigo', $codigo)->orWhere('codigo_rol', $codigo);
        })->exists();
    }

    public function cliente(): HasOne
    {
        return $this->hasOne(Cliente::class, 'id_user', 'id_user');
    }

    public function coach(): HasOne
    {
        return $this->hasOne(Coach::class, 'id_user', 'id_user');
    }

    public function iniciales(): string
    {
        $n = mb_substr(trim((string) $this->nombres), 0, 1);
        $a = mb_substr(trim((string) $this->apellidos), 0, 1);
        $sigla = mb_strtoupper($n.$a);

        return $sigla !== '' ? $sigla : 'FF';
    }
}
