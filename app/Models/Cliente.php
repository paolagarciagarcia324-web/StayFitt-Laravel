<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    protected $table = 'clientes';

    protected $primaryKey = 'id_cliente';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'id_user',
        'id_coach',
        'id_institucion',
        'id_horario',
        'modalidad',
        'tipo_cliente',
        'estado_cliente',
        'fecha_alta',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function planesCliente(): HasMany
    {
        return $this->hasMany(PlanCliente::class, 'id_cliente', 'id_cliente');
    }
}
