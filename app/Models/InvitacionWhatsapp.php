<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvitacionWhatsapp extends Model
{
    protected $table = 'invitaciones_whatsapp';

    protected $primaryKey = 'id_invitacion_wa';

    public $timestamps = false;

    protected $casts = [
        'cerrada_sin_aceptar' => 'boolean',
        'persistencia_local' => 'boolean',
        'mostrada_en' => 'datetime',
        'aceptada_en' => 'datetime',
    ];

    protected $fillable = [
        'id_cliente',
        'id_grupo_wa',
        'link_mostrado',
        'mostrada_en',
        'aceptada_en',
        'cerrada_sin_aceptar',
        'persistencia_local'
    ];
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }
    public function grupoWa(): BelongsTo
    {
        return $this->belongsTo(GrupoWhatsapp::class, 'id_grupo_wa', 'id_grupo_wa');
    }
}
