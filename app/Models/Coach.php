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
}
