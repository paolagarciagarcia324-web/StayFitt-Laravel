<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrdenTienda extends Model
{
    protected $table = 'ordenes_tienda';

    protected $primaryKey = 'id_orden';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'numero_orden',
        'id_cliente',
        'id_pago',
        'subtotal',
        'costo_envio',
        'total',
        'tipo_entrega',
        'direccion_entrega',
        'ciudad_entrega',
        'estado_orden',
        'observacion_admin',
        'comprobante_url',
        'actualizado_en',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(OrdenDetalle::class, 'id_orden', 'id_orden');
    }

    public function comprobanteSrc(): string
    {
        $path = trim((string) $this->comprobante_url);
        if ($path === '') {
            return '';
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }
        if (str_starts_with($path, 'images/')) {
            return asset($path);
        }

        return asset('storage/'.$path);
    }
}
