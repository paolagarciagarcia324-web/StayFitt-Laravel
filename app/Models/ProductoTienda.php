<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductoTienda extends Model
{
    protected $table = 'productos_tienda';

    protected $primaryKey = 'id_producto';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'id_categoria_producto',
        'sku',
        'nombre',
        'descripcion',
        'precio',
        'imagen_url',
        'stock_total',
        'stock_minimo',
        'exclusivo_presencial',
        'estado',
        'creado_en',
        'actualizado_en',
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'exclusivo_presencial' => 'boolean',
        ];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaProducto::class, 'id_categoria_producto', 'id_categoria_producto');
    }
}
