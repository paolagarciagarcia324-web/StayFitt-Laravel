<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetaCliente extends Model
{
    protected $table = 'metas_cliente';

    protected $primaryKey = 'id_meta';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $casts = [
        'valor_inicial' => 'decimal:2',
        'valor_actual' => 'decimal:2',
        'valor_objetivo' => 'decimal:2',
        'fecha_inicio' => 'date',
        'fecha_objetivo' => 'date',
    ];

    protected $fillable = [
        'id_cliente',
        'nombre',
        'metrica',
        'valor_inicial',
        'valor_actual',
        'valor_objetivo',
        'unidad',
        'fecha_inicio',
        'fecha_objetivo',
        'estado'
    ];
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }
}
