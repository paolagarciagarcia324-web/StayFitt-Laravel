<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SesionAuth extends Model
{
    protected $table = 'sesiones_auth';

    protected $primaryKey = 'id_sesion_auth';

    public $timestamps = false;

    protected $casts = [
        'iniciada_en' => 'datetime',
        'cerrada_en' => 'datetime',
    ];

    protected $fillable = [
        'id_user',
        'id_sesion_app',
        'ip',
        'user_agent',
        'iniciada_en',
        'cerrada_en'
    ];
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
