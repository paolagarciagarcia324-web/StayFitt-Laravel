<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coach extends Model
{
    protected $table = 'coaches';

    protected $primaryKey = 'id_coach';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'id_user',
        'codigo_coach',
        'especialidad',
        'biografia',
        'certificaciones',
        'foto_url',
        'estado_coach',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class, 'id_coach', 'id_coach');
    }

    public function clientesAsignados(): HasMany
    {
        return $this->hasMany(Cliente::class, 'id_coach_asignado', 'id_coach');
    }

    public function planesEntrenamiento(): HasMany
    {
        return $this->hasMany(PlanEntrenamiento::class, 'id_coach', 'id_coach');
    }

    public function sesiones(): HasMany
    {
        return $this->hasMany(Sesion::class, 'id_coach', 'id_coach');
    }

    public function chats(): HasMany
    {
        return $this->hasMany(Chat::class, 'id_coach', 'id_coach');
    }

    public function totalClientesActivos(): int
    {
        return Cliente::query()
            ->where(function ($q) {
                $q->where('id_coach', $this->id_coach)
                    ->orWhere('id_coach_asignado', $this->id_coach);
            })
            ->whereIn('estado_cliente', ['ACTIVA', 'ACTIVO'])
            ->count();
    }
}
