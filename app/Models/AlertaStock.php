<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertaStock extends Model
{
    protected $table = 'alertas_stock';

    protected $primaryKey = 'id_alerta_stock';

    public $timestamps = false;

    protected $casts = [
        'notificada_en' => 'datetime',
    ];

    protected $fillable = [
        'id_producto',
        'id_variante',
        'stock_actual',
        'umbral',
        'notificada_en'
    ];
    public function producto(): BelongsTo
    {
        return $this->belongsTo(ProductoTienda::class, 'id_producto', 'id_producto');
    }
}
