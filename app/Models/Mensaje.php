<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mensaje extends Model
{
    protected $table = 'mensajes';

    protected $primaryKey = 'id_mensaje';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    protected $fillable = [
        'id_chat',
        'id_user_emisor',
        'contenido',
        'tipo_mensaje',
        'url_adjunto',
        'leido',
        'enviado_en',
    ];

    protected function casts(): array
    {
        return [
            'leido' => 'boolean',
            'enviado_en' => 'datetime',
        ];
    }

    public function emisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user_emisor', 'id_user');
    }

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class, 'id_chat', 'id_chat');
    }
}
