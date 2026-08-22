<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistroProgreso extends Model
{
    protected $table = 'registros_progreso';

    protected $primaryKey = 'id_registro_progreso';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $casts = [
        'es_linea_base' => 'boolean',
        'peso_kg' => 'decimal:2',
        'estatura_cm' => 'decimal:2',
        'porcentaje_grasa' => 'decimal:2',
        'masa_muscular_kg' => 'decimal:2',
        'imc' => 'decimal:2',
        'cuello_cm' => 'decimal:2',
        'hombro_cm' => 'decimal:2',
        'pecho_cm' => 'decimal:2',
        'brazo_cm' => 'decimal:2',
        'antebrazo_cm' => 'decimal:2',
        'cintura_cm' => 'decimal:2',
        'abdomen_cm' => 'decimal:2',
        'cadera_cm' => 'decimal:2',
        'gluteo_cm' => 'decimal:2',
        'pierna_cm' => 'decimal:2',
        'gemelo_cm' => 'decimal:2',
        'agua_litros' => 'decimal:2',
        'fecha_registro' => 'date',
    ];

    protected $fillable = [
        'id_cliente',
        'id_coach',
        'id_plan_cliente',
        'fecha_registro',
        'peso_kg',
        'estatura_cm',
        'porcentaje_grasa',
        'masa_muscular_kg',
        'imc',
        'cuello_cm',
        'hombro_cm',
        'pecho_cm',
        'brazo_cm',
        'antebrazo_cm',
        'cintura_cm',
        'abdomen_cm',
        'cadera_cm',
        'gluteo_cm',
        'pierna_cm',
        'gemelo_cm',
        'fc_reposo',
        'pa_sistolica',
        'pa_diastolica',
        'nivel_energia',
        'calidad_sueno',
        'nivel_estres',
        'agua_litros',
        'observacion_coach',
        'es_linea_base'
    ];
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }
}
