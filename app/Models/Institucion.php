<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Institucion extends Model
{
    protected $table = 'instituciones';

    protected $primaryKey = 'id_institucion';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'nombre',
        'logo_url',
        'tipo_institucion',
        'nit',
        'contacto_nombre',
        'contacto_cargo',
        'correo',
        'telefono',
        'ciudad',
        'direccion',
        'convenio',
        'cupos_totales',
        'cupos_usados',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'observaciones',
        'id_user_responsable',
        'id_plan_contratado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
        ];
    }

    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class, 'id_institucion', 'id_institucion');
    }

    public function enlaces(): HasMany
    {
        return $this->hasMany(EnlaceRegistroInstitucional::class, 'id_institucion', 'id_institucion');
    }

    public function invitaciones(): HasMany
    {
        return $this->hasMany(InvitacionInstitucional::class, 'id_institucion', 'id_institucion');
    }

    public function comunicados(): HasMany
    {
        return $this->hasMany(ComunicadoInstitucional::class, 'id_institucion', 'id_institucion');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user_responsable', 'id_user');
    }

    public function planContratado(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'id_plan_contratado', 'id_plan');
    }

    public function cuposDisponibles(): int
    {
        $totales = (int) $this->cupos_totales;
        $usados = (int) $this->cupos_usados;

        if ($totales <= 0) {
            return PHP_INT_MAX;
        }

        return max(0, $totales - $usados);
    }

    public function consumoPorcentaje(): float
    {
        $totales = (int) $this->cupos_totales;
        if ($totales <= 0) {
            return 0.0;
        }

        return min(100, round(((int) $this->cupos_usados / $totales) * 100, 1));
    }

    public function convenioVigente(): bool
    {
        if ($this->estado !== 'ACTIVA') {
            return false;
        }

        $hoy = Carbon::today();

        if ($this->fecha_fin && $this->fecha_fin->lt($hoy)) {
            return false;
        }

        return true;
    }

    public function etiquetaTipo(): string
    {
        return match ($this->tipo_institucion) {
            'EDUCATIVA' => 'Institución educativa',
            'EMPRESA' => 'Empresa / corporativo',
            'GIMNASIO' => 'Gimnasio / wellness hub',
            'GOBIERNO' => 'Entidad pública',
            default => $this->tipo_institucion ?: 'Convenio B2B',
        };
    }
}
