<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductoVariante extends Model
{
    protected $table = 'producto_variantes';

    protected $primaryKey = 'id_variante';

    public $timestamps = false;

    protected $fillable = [
        'id_producto',
        'talla',
        'color',
        'sku_variante',
        'stock',
        'estado'
    ];
    public function producto(): BelongsTo
    {
        return $this->belongsTo(ProductoTienda::class, 'id_producto', 'id_producto');
    }
}
