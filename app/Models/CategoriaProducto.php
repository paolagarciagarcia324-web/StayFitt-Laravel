<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaProducto extends Model
{
    protected $table = 'categorias_producto';

    protected $primaryKey = 'id_categoria_producto';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'orden',
        'estado',
    ];

    public function productos(): HasMany
    {
        return $this->hasMany(ProductoTienda::class, 'id_categoria_producto', 'id_categoria_producto');
    }
}
