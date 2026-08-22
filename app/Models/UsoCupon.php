<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsoCupon extends Model
{
    protected $table = 'usos_cupones';

    protected $primaryKey = 'id_uso_cupon';

    public $timestamps = false;

    protected $casts = [
        'usado_en' => 'datetime',
    ];

    protected $fillable = [
        'id_descuento',
        'id_solicitud',
        'correo',
        'usado_en'
    ];
    public function descuento(): BelongsTo
    {
        return $this->belongsTo(DescuentoCupon::class, 'id_descuento', 'id_descuento');
    }
    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(SolicitudCompra::class, 'id_solicitud', 'id_solicitud');
    }
}
