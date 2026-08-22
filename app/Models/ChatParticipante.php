<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatParticipante extends Model
{
    protected $table = 'chat_participantes';

    protected $primaryKey = 'id_chat_participante';

    public $timestamps = false;

    protected $casts = [
        'unido_en' => 'datetime',
    ];

    protected $fillable = [
        'id_chat',
        'id_user',
        'rol_en_chat',
        'unido_en'
    ];
    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class, 'id_chat', 'id_chat');
    }
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
