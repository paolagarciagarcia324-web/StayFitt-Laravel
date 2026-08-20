<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdenDetalle extends Model
{
    protected $table = 'orden_detalle';

    protected $primaryKey = 'id_detalle';

    public $timestamps = false;

    protected $fillable = [
        'id_orden',
        'id_producto',
        'id_variante',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    public function orden(): BelongsTo
    {
        return $this->belongsTo(OrdenTienda::class, 'id_orden', 'id_orden');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(ProductoTienda::class, 'id_producto', 'id_producto');
    }
}
