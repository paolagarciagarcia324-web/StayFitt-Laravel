<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SesionParticipante extends Model
{
    protected $table = 'sesion_participantes';

    protected $primaryKey = 'id_sesion_participante';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $casts = [
        'inscrito_en' => 'datetime',
    ];

    protected $fillable = [
        'id_sesion',
        'id_cliente',
        'estado',
        'inscrito_en'
    ];
    public function sesion(): BelongsTo
    {
        return $this->belongsTo(Sesion::class, 'id_sesion', 'id_sesion');
    }
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }
}
