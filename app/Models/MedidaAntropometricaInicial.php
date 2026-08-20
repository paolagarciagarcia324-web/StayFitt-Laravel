<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedidaAntropometricaInicial extends Model
{
    protected $table = 'medidas_antropometricas_iniciales';

    protected $primaryKey = 'id_medida_inicial';

    public $timestamps = false;

    protected $casts = [
        'peso_kg' => 'decimal:2',
        'estatura_cm' => 'decimal:2',
        'cintura_cm' => 'decimal:2',
        'cadera_cm' => 'decimal:2',
        'brazo_cm' => 'decimal:2',
        'pierna_cm' => 'decimal:2',
        'registrada_en' => 'datetime',
    ];

    protected $fillable = [
        'id_cliente',
        'peso_kg',
        'estatura_cm',
        'cintura_cm',
        'cadera_cm',
        'brazo_cm',
        'pierna_cm',
        'origen',
        'registrada_en',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }
}
