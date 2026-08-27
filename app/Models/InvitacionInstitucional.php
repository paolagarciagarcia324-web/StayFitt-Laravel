<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class InvitacionInstitucional extends Model
{
    protected $table = 'invitaciones_institucionales';

    protected $primaryKey = 'id_invitacion';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    protected $fillable = [
        'id_institucion',
        'id_plan',
        'correo',
        'nombre_sugerido',
        'token',
        'estado',
        'fecha_expiracion',
        'id_user_creador',
        'id_cliente_vinculado',
        'aceptada_en',
    ];

    protected function casts(): array
    {
        return [
            'fecha_expiracion' => 'datetime',
            'aceptada_en' => 'datetime',
        ];
    }

    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class, 'id_institucion', 'id_institucion');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'id_plan', 'id_plan');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user_creador', 'id_user');
    }

    public function clienteVinculado(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente_vinculado', 'id_cliente');
    }

    public function estadoEfectivo(): string
    {
        if ($this->estado === 'ACEPTADA') {
            return 'ACEPTADA';
        }

        if ($this->estado === 'EXPIRADA' || $this->fecha_expiracion->isPast()) {
            return 'EXPIRADA';
        }

        return 'PENDIENTE';
    }

    public function etiquetaEstado(): string
    {
        return match ($this->estadoEfectivo()) {
            'ACEPTADA' => 'Aceptada',
            'EXPIRADA' => 'Expirada',
            default => 'Pendiente',
        };
    }

    public function marcarExpiradaSiCorresponde(): void
    {
        if ($this->estado === 'PENDIENTE' && $this->fecha_expiracion->isPast()) {
            $this->update(['estado' => 'EXPIRADA']);
        }
    }
}
