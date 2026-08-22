<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class Pago extends Model
{
    protected $table = 'pagos';

    protected $primaryKey = 'id_pago';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $casts = [
        'monto' => 'decimal:2',
        'monto_descuento' => 'decimal:2',
        'fecha_pago' => 'datetime',
        'fecha_validacion' => 'datetime',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    protected $fillable = [
        'id_solicitud',
        'id_cliente',
        'id_plan',
        'id_plan_cliente',
        'origen_pago',
        'monto',
        'moneda',
        'tipo_pago',
        'metodo',
        'metodo_pago',
        'pasarela',
        'referencia',
        'referencia_pago',
        'comprobante_url',
        'fecha_pago',
        'estado_pago',
        'validado_por',
        'fecha_validacion',
        'observacion_admin',
    ];

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(SolicitudCompra::class, 'id_solicitud', 'id_solicitud');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'id_plan', 'id_plan');
    }

    public function planCliente(): HasOne
    {
        return $this->hasOne(PlanCliente::class, 'id_pago', 'id_pago');
    }

    public function fechaPagoEfectiva(): ?CarbonInterface
    {
        return $this->fecha_pago ?? $this->fecha_validacion ?? $this->creado_en;
    }

    public function fechaVencimiento(): ?CarbonInterface
    {
        if ($this->planCliente?->fecha_fin) {
            return Carbon::parse($this->planCliente->fecha_fin)->startOfDay();
        }

        $inicio = $this->fechaPagoEfectiva();
        if (! $inicio) {
            return null;
        }

        $dias = (int) ($this->plan?->duracion_dias ?: 30);

        return $inicio->copy()->startOfDay()->addDays($dias);
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
