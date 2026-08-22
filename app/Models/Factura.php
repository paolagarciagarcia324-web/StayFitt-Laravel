<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Factura extends Model
{
    protected $table = 'facturas';

    protected $primaryKey = 'id_factura';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $casts = [
        'subtotal' => 'decimal:2',
        'impuestos' => 'decimal:2',
        'total' => 'decimal:2',
        'emitida_en' => 'datetime',
    ];

    protected $fillable = [
        'numero_factura',
        'id_pago',
        'tipo',
        'razon_social',
        'documento_cliente',
        'correo_cliente',
        'subtotal',
        'impuestos',
        'total',
        'pdf_url',
        'xml_url',
        'estado',
        'emitida_en'
    ];
    public function pago(): BelongsTo
    {
        return $this->belongsTo(Pago::class, 'id_pago', 'id_pago');
    }
}
