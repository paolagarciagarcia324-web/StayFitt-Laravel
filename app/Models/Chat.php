<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chat extends Model
{
    protected $table = 'chats';

    protected $primaryKey = 'id_chat';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    protected $fillable = [
        'tipo',
        'id_plan',
        'id_coach',
        'nombre',
        'estado',
    ];

    public function mensajes(): HasMany
    {
        return $this->hasMany(Mensaje::class, 'id_chat', 'id_chat');
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class, 'id_coach', 'id_coach');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'id_plan', 'id_plan');
    }
}
