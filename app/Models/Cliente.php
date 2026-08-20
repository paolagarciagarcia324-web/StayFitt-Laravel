<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

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
        'id_coach_asignado',
        'id_institucion',
        'id_horario',
        'modalidad',
        'tipo_cliente',
        'tipo_documento',
        'documento',
        'fecha_nacimiento',
        'genero',
        'ciudad',
        'peso_inicial_kg',
        'estatura_cm',
        'objetivo_principal',
        'alergias',
        'condiciones_medicas',
        'lesiones',
        'medicamentos',
        'contacto_emergencia_nombre',
        'contacto_emergencia_telefono',
        'estado_cliente',
        'fecha_alta',
        'fecha_activacion',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function planesCliente(): HasMany
    {
        return $this->hasMany(PlanCliente::class, 'id_cliente', 'id_cliente');
    }

    public function rachas(): HasMany
    {
        return $this->hasMany(RachaCliente::class, 'id_cliente', 'id_cliente');
    }

    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class, 'id_institucion', 'id_institucion');
    }

    public function horario(): BelongsTo
    {
        return $this->belongsTo(HorarioPresencial::class, 'id_horario', 'id_horario');
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class, 'id_coach', 'id_coach');
    }

    public function accesos(): HasMany
    {
        return $this->hasMany(AccesoClienteModulo::class, 'id_cliente', 'id_cliente');
    }

    public function planActivo(): ?PlanCliente
    {
        if ($this->relationLoaded('planesCliente')) {
            return $this->planesCliente
                ->where('estado_plan_cliente', 'ACTIVO')
                ->sortByDesc('fecha_fin')
                ->first();
        }

        return $this->planesCliente()
            ->where('estado_plan_cliente', 'ACTIVO')
            ->orderByDesc('fecha_fin')
            ->first();
    }

    public function diasRacha(): int
    {
        if ($this->relationLoaded('rachas')) {
            return (int) $this->rachas->max('dias_consecutivos');
        }

        return (int) ($this->rachas()->max('dias_consecutivos') ?? 0);
    }

    public function diasRestantesPlan(): ?int
    {
        $plan = $this->planActivo();
        if (! $plan?->fecha_fin) {
            return null;
        }

        return (int) Carbon::now()->startOfDay()->diffInDays(Carbon::parse($plan->fecha_fin), false);
    }
}
