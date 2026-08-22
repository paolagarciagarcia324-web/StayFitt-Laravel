<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParticipanteGrupoWhatsapp extends Model
{
    protected $table = 'participantes_grupo_whatsapp';

    protected $primaryKey = 'id_part_wa';

    public $timestamps = false;

    protected $casts = [
        'unido_en' => 'datetime',
    ];

    protected $fillable = [
        'id_grupo_wa',
        'id_cliente',
        'estado',
        'unido_en'
    ];
    public function grupoWa(): BelongsTo
    {
        return $this->belongsTo(GrupoWhatsapp::class, 'id_grupo_wa', 'id_grupo_wa');
    }
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }
}
