<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventoParticipante extends Model
{
    protected $table = 'evento_participantes';

    protected $primaryKey = 'id_evento_participante';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $casts = [
        'inscrito_en' => 'datetime',
    ];

    protected $fillable = [
        'id_evento',
        'id_cliente',
        'estado',
        'inscrito_en'
    ];
    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class, 'id_evento', 'id_evento');
    }
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }
}
