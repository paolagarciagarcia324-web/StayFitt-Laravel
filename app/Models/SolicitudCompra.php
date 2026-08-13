<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SolicitudCompra extends Model
{
    protected $table = 'solicitudes_compra';

    protected $primaryKey = 'id_solicitud';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $casts = [
        'acepto_terminos' => 'boolean',
        'fecha_aceptacion_terminos' => 'datetime',
        'fecha_revision' => 'datetime',
    ];

    protected $fillable = [
        'codigo_solicitud',
        'id_plan',
        'id_institucion',
        'modalidad',
        'tipo_cliente',
        'nombres',
        'apellidos',
        'documento_identidad',
        'correo',
        'telefono',
        'ciudad',
        'acepto_terminos',
        'fecha_aceptacion_terminos',
        'observacion_usuario',
        'estado_solicitud',
        'revisado_por',
        'fecha_revision',
        'observacion_admin',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'id_plan', 'id_plan');
    }

    public function pago(): HasOne
    {
        return $this->hasOne(Pago::class, 'id_solicitud', 'id_solicitud');
    }
}
