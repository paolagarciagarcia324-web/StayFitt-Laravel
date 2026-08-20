<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    protected $table = 'pagos';

    protected $primaryKey = 'id_pago';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $casts = [
        'monto' => 'decimal:2',
        'monto_descuento' => 'decimal:2',
        'fecha_pago' => 'datetime',
        'fecha_validacion' => 'datetime',
    ];

    protected $fillable = [
        'id_solicitud',
        'id_cliente',
        'id_plan',
        'origen_pago',
        'monto',
        'moneda',
        'tipo_pago',
        'metodo',
        'metodo_pago',
        'pasarela',
        'referencia',
        'referencia_pago',
        'comprobante_url',
        'fecha_pago',
        'estado_pago',
        'validado_por',
        'fecha_validacion',
        'observacion_admin',
    ];

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(SolicitudCompra::class, 'id_solicitud', 'id_solicitud');
    }
}
