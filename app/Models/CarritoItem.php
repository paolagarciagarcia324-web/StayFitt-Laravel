<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarritoItem extends Model
{
    protected $table = 'carrito_items';

    protected $primaryKey = 'id_item';

    public $timestamps = false;

    protected $casts = [
        'precio_unitario' => 'decimal:2',
    ];

    protected $fillable = [
        'id_carrito',
        'id_producto',
        'id_variante',
        'cantidad',
        'precio_unitario'
    ];
    public function carrito(): BelongsTo
    {
        return $this->belongsTo(Carrito::class, 'id_carrito', 'id_carrito');
    }
    public function producto(): BelongsTo
    {
        return $this->belongsTo(ProductoTienda::class, 'id_producto', 'id_producto');
    }
}
