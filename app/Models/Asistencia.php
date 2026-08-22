<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asistencia extends Model
{
    protected $table = 'asistencias';

    protected $primaryKey = 'id_asistencia';

    public $timestamps = false;

    protected $casts = [
        'llama_racha' => 'boolean',
        'fecha' => 'date',
    ];

    protected $fillable = [
        'id_cliente',
        'id_coach',
        'id_sesion',
        'fecha',
        'estado',
        'llama_racha',
        'observacion',
        'registrada_en'
    ];
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }
    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class, 'id_coach', 'id_coach');
    }
    public function sesion(): BelongsTo
    {
        return $this->belongsTo(Sesion::class, 'id_sesion', 'id_sesion');
    }
}
