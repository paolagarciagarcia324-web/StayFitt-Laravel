<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TokenRecuperacion extends Model
{
    protected $table = 'tokens_recuperacion';

    protected $primaryKey = 'id_token';

    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'token_hash',
        'expira_en',
        'usado_en',
        'ip_solicitud',
        'creado_en',
    ];

    protected function casts(): array
    {
        return [
            'expira_en' => 'datetime',
            'usado_en' => 'datetime',
            'creado_en' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
